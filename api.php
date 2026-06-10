<?php
require_once 'config.php';

header('Content-Type: application/json');

// Consolidate API actions
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

// Public actions
if ($action === 'load_public') {
    try {
        $db = getDBConnection();
        $stmt = $db->query("SELECT version, published_at FROM published_schedule ORDER BY id DESC LIMIT 1");
        $ver = $stmt->fetch();
        $version_id = $ver ? $ver['version'] : null;

        if (!$version_id) {
            echo json_encode(['status' => 'success', 'headers' => [], 'rows' => [], 'cells' => [], 'published' => null]);
            exit();
        }

        $headers = $db->prepare("SELECT id, header_name, position FROM published_headers WHERE version_id = ? ORDER BY position");
        $headers->execute([$version_id]);
        
        $rows = $db->prepare("SELECT id, row_order FROM published_rows WHERE version_id = ? ORDER BY row_order");
        $rows->execute([$version_id]);
        
        $cells_stmt = $db->prepare("SELECT * FROM published_cells WHERE version_id = ?");
        $cells_stmt->execute([$version_id]);
        $cells_raw = $cells_stmt->fetchAll();
        
        $cells = [];
        foreach ($cells_raw as $cell) {
            $cells[$cell['row_id']][$cell['header_id']] = $cell;
        }

        echo json_encode([
            'status' => 'success',
            'headers' => $headers->fetchAll(),
            'rows' => $rows->fetchAll(),
            'cells' => $cells,
            'published' => [
                'version' => $version_id,
                'published_at' => $ver['published_at']
            ]
        ]);
        exit();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
        exit();
    }
}

// Protected actions
if (!isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// CSRF check for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($input['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
        exit();
    }
}

try {
    $db = getDBConnection();
    
    switch ($action) {
        case 'load_admin':
            $headers = $db->query("SELECT * FROM schedule_headers ORDER BY position")->fetchAll();
            $rows = $db->query("SELECT * FROM schedule_rows ORDER BY row_order")->fetchAll();
            $cells_raw = $db->query("SELECT * FROM schedule_cells")->fetchAll();
            $last_mod = $db->query("SELECT MAX(updated_at) FROM schedule_cells")->fetchColumn();
            $cells = [];
            foreach ($cells_raw as $cell) {
                $cells[$cell['row_id']][$cell['header_id']] = $cell;
            }
            echo json_encode(['status' => 'success', 'headers' => $headers, 'rows' => $rows, 'cells' => $cells, 'last_modified' => $last_mod]);
            break;

        case 'save_cell':
            $sql = "INSERT INTO schedule_cells (row_id, header_id, content, bg_color, font_weight, text_align) 
                    VALUES (:row_id, :header_id, :content, :bg_color, :font_weight, :text_align)
                    ON DUPLICATE KEY UPDATE 
                    content = VALUES(content), bg_color = VALUES(bg_color), font_weight = VALUES(font_weight), text_align = VALUES(text_align)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':row_id' => $input['row_id'],
                ':header_id' => $input['header_id'],
                ':content' => $input['content'],
                ':bg_color' => $input['bg_color'] ?? null,
                ':font_weight' => $input['font_weight'] ?? 'normal',
                ':text_align' => $input['text_align'] ?? 'left'
            ]);
            echo json_encode(['status' => 'success']);
            break;

        case 'add_row':
            $max = $db->query("SELECT MAX(row_order) as m FROM schedule_rows")->fetch();
            $stmt = $db->prepare("INSERT INTO schedule_rows (row_order) VALUES (?)");
            $stmt->execute([($max['m'] ?? 0) + 1]);
            echo json_encode(['status' => 'success', 'id' => $db->lastInsertId()]);
            break;

        case 'add_header':
            $max = $db->query("SELECT MAX(position) as m FROM schedule_headers")->fetch();
            $stmt = $db->prepare("INSERT INTO schedule_headers (header_name, position) VALUES (?, ?)");
            $stmt->execute([$input['header_name'] ?? 'New Column', ($max['m'] ?? 0) + 1]);
            echo json_encode(['status' => 'success', 'id' => $db->lastInsertId()]);
            break;

        case 'delete_row':
            $stmt = $db->prepare("DELETE FROM schedule_rows WHERE id = ?");
            $stmt->execute([$input['id']]);
            echo json_encode(['status' => 'success']);
            break;

        case 'delete_header':
            $stmt = $db->prepare("DELETE FROM schedule_headers WHERE id = ?");
            $stmt->execute([$input['id']]);
            echo json_encode(['status' => 'success']);
            break;

        case 'rename_header':
            $stmt = $db->prepare("UPDATE schedule_headers SET header_name = ? WHERE id = ?");
            $stmt->execute([$input['header_name'], $input['id']]);
            echo json_encode(['status' => 'success']);
            break;

        case 'get_versions':
            $stmt = $db->query("SELECT * FROM published_schedule ORDER BY published_at DESC");
            echo json_encode(['status' => 'success', 'versions' => $stmt->fetchAll()]);
            break;

        case 'publish':
            $db->beginTransaction();
            $stmt = $db->query("SELECT version FROM published_schedule ORDER BY id DESC LIMIT 1");
            $current = $stmt->fetch();
            $new_ver = ($current ? $current['version'] : 0) + 1;
            
            // Snapshot headers
            $headers = $db->query("SELECT id, header_name, position FROM schedule_headers")->fetchAll();
            $stmt = $db->prepare("INSERT INTO published_headers (id, header_name, position, version_id) VALUES (?, ?, ?, ?)");
            foreach ($headers as $h) $stmt->execute([$h['id'], $h['header_name'], $h['position'], $new_ver]);
            
            // Snapshot rows
            $rows = $db->query("SELECT id, row_order FROM schedule_rows")->fetchAll();
            $stmt = $db->prepare("INSERT INTO published_rows (id, row_order, version_id) VALUES (?, ?, ?)");
            foreach ($rows as $r) $stmt->execute([$r['id'], $r['row_order'], $new_ver]);
            
            // Snapshot cells
            $cells = $db->query("SELECT * FROM schedule_cells")->fetchAll();
            $stmt = $db->prepare("INSERT INTO published_cells (row_id, header_id, content, bg_color, font_weight, text_align, row_span, col_span, version_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($cells as $c) $stmt->execute([$c['row_id'], $c['header_id'], $c['content'], $c['bg_color'], $c['font_weight'], $c['text_align'], $c['row_span'], $c['col_span'], $new_ver]);
            
            $db->prepare("INSERT INTO published_schedule (version) VALUES (?)")->execute([$new_ver]);
            $db->commit();
            echo json_encode(['status' => 'success', 'version' => $new_ver]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
    }
} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
