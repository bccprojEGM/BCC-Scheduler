<?php
require_once 'config.php';

$db = getDBConnection();
$stmt = $db->query("SELECT COUNT(*) FROM admins");
$adminCount = $stmt->fetchColumn();

// Only allow registration if no admins exist OR if an admin is already logged in
if ($adminCount > 0 && !isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'All fields are required.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt->execute([$username, $hashedPassword]);
                $success = 'Administrator account created successfully. You can now <a href="login.php" class="underline">login</a>.';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = 'Username already exists.';
                } else {
                    $error = 'Error creating account: ' . $e->getMessage();
                }
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - Binalbagan Catholic College</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(-45deg, #0f766e, #115e59, #1e293b, #0f172a);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="glass max-w-md w-full p-8 rounded-2xl shadow-2xl">
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-white rounded-full mx-auto mb-4 flex items-center justify-center shadow-lg">
                 <span class="text-teal-700 font-bold text-2xl">BCC</span>
            </div>
            <h1 class="text-2xl font-bold text-white">Admin Registration</h1>
            <p class="text-teal-100">Create a new administrator account</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-100 px-4 py-3 rounded mb-6 text-sm">
                <?php echo h($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-500/20 border border-green-500 text-green-100 px-4 py-3 rounded mb-6 text-sm">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <div>
                <label for="username" class="block text-sm font-medium text-teal-500 mb-1">Username</label>
                <input type="text" id="username" name="username" required
                    class="w-full px-4 py-3 bg-slate-800/50 border border-slate-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-teal-500 mb-1">Password</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-3 bg-slate-800/50 border border-slate-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all">
            </div>
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-teal-500 mb-1">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                    class="w-full px-4 py-3 bg-slate-800/50 border border-slate-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all">
            </div>
            <button type="submit"
                class="w-full py-3 px-4 bg-teal-600 hover:bg-teal-500 text-white font-bold rounded-lg shadow-lg transform hover:-translate-y-1 transition-all duration-200">
                Register Admin
            </button>
            <div class="text-center">
                <a href="login.php" class="text-teal-400 hover:text-teal-300 text-sm">Back to Login</a>
            </div>
        </form>

        <div class="mt-8 text-center text-teal-200 text-xs">
            &copy; <?php echo date('Y'); ?> Binalbagan Catholic College<br>
            Official Scheduling Management System
        </div>
    </div>
</body>
</html>
