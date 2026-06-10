<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$username = $_SESSION['username'];
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BCC Official Schedule</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-slate-50 min-h-screen font-[Poppins]">
    <!-- Top Navigation -->
    <nav class="bg-teal-800 text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center space-x-3">
                    <div class="bg-white p-1 rounded-full">
                        <img src="https://via.placeholder.com/40" alt="Logo" class="w-8 h-8">
                    </div>
                    <div>
                        <h1 class="text-xl font-bold leading-none">Binalbagan Catholic College</h1>
                        <p class="text-xs text-teal-200">Official Scheduling Console (Admin)</p>
                    </div>
                </div>
                <div class="flex items-center space-x-6">
                    <span class="hidden md:inline text-teal-100 text-sm">
                        <i class="bi bi-person-circle mr-1"></i> Welcome, <?php echo h($username); ?>
                    </span>
                    <a href="index.php" target="_blank" class="bg-teal-700 hover:bg-teal-600 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="bi bi-eye mr-1"></i> User View
                    </a>
                    <a href="register.php" class="bg-teal-700 hover:bg-teal-600 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="bi bi-person-plus mr-1"></i> Add Admin
                    </a>
                    <a href="logout.php" class="text-teal-100 hover:text-white transition-colors">
                        <i class="bi bi-box-arrow-right text-xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="p-4 md:p-8">
        <!-- Dashboard Header & Controls -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 space-y-4 lg:space-y-0">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Academic Affairs & Scheduling</h2>
                <div class="flex flex-col">
                    <p class="text-slate-500 text-sm" id="currentDateTime"></p>
                    <p class="text-teal-600 text-xs font-semibold" id="lastModifiedDisplay"></p>
                </div>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <button onclick="addNewRow()" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center">
                    <i class="bi bi-plus-lg mr-2"></i> Add Row
                </button>
                <button onclick="promptNewColumn()" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center">
                    <i class="bi bi-layout-three-columns mr-2"></i> Add Column
                </button>
                <button onclick="manualSave()" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center">
                    <i class="bi bi-save mr-2"></i> Manual Save
                </button>
                <button onclick="togglePreview()" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center">
                    <i class="bi bi-eye mr-2"></i> Preview
                </button>
                <button onclick="toggleVersions()" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center">
                    <i class="bi bi-history mr-2"></i> History
                </button>
                <div class="h-8 w-[1px] bg-slate-300 mx-1"></div>
                <button onclick="publishSchedule()" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded-lg text-sm font-bold shadow-md transform hover:-translate-y-0.5 transition-all flex items-center">
                    <i class="bi bi-cloud-arrow-up-fill mr-2"></i> Publish Updates
                </button>
            </div>
        </div>

        <!-- Formatting Toolbar -->
        <div id="formattingToolbar" class="bg-white rounded-t-xl border border-slate-200 border-b-0 p-3 flex flex-wrap items-center gap-4 hidden shadow-sm transition-all">
            <div class="flex items-center space-x-1 border-r border-slate-200 pr-4">
                <button onclick="applyFormat('font_weight', 'bold')" class="p-2 hover:bg-slate-100 rounded" title="Bold"><i class="bi bi-type-bold"></i></button>
                <button onclick="applyFormat('font_weight', 'normal')" class="p-2 hover:bg-slate-100 rounded" title="Normal"><i class="bi bi-type"></i></button>
            </div>
            <div class="flex items-center space-x-1 border-r border-slate-200 pr-4">
                <button onclick="applyFormat('text_align', 'left')" class="p-2 hover:bg-slate-100 rounded"><i class="bi bi-text-left"></i></button>
                <button onclick="applyFormat('text_align', 'center')" class="p-2 hover:bg-slate-100 rounded"><i class="bi bi-text-center"></i></button>
                <button onclick="applyFormat('text_align', 'right')" class="p-2 hover:bg-slate-100 rounded"><i class="bi bi-text-right"></i></button>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-slate-400 uppercase">Bg:</span>
                <button onclick="applyFormat('bg_color', '#f0fdfa')" class="w-6 h-6 rounded-full bg-teal-50 border border-slate-200"></button>
                <button onclick="applyFormat('bg_color', '#fff7ed')" class="w-6 h-6 rounded-full bg-orange-50 border border-slate-200"></button>
                <button onclick="applyFormat('bg_color', '#eff6ff')" class="w-6 h-6 rounded-full bg-blue-50 border border-slate-200"></button>
                <button onclick="applyFormat('bg_color', 'transparent')" class="w-6 h-6 rounded-full bg-white border border-slate-200" title="Clear"></button>
            </div>
        </div>

        <!-- Spreadsheet Editor Card -->
        <div class="bg-white rounded-b-xl shadow-xl overflow-hidden border border-slate-200">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <span class="flex items-center text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <span class="w-3 h-3 bg-teal-500 rounded-full mr-2"></span> Spreadsheet Mode
                    </span>
                    <span id="saveIndicator" class="text-xs text-slate-400 italic hidden"></span>
                </div>
            </div>
            
            <div class="overflow-x-auto" id="tableContainer">
                <div id="skeletonLoader" class="p-8 space-y-4">
                    <div class="h-8 bg-slate-100 rounded animate-pulse w-full"></div>
                    <div class="h-64 bg-slate-50 rounded animate-pulse w-full"></div>
                </div>
                
                <table id="scheduleTable" class="w-full border-collapse hidden">
                    <thead>
                        <tr id="tableHeaderRow" class="bg-slate-50"></tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>
        </div>
        
        <footer class="mt-12 text-center text-slate-400 text-sm pb-8">
            <p>&copy; <?php echo date('Y'); ?> Binalbagan Catholic College | Official Scheduling Management System</p>
        </footer>
    </main>

    <div id="toast" class="fixed bottom-8 right-8 transform translate-y-24 opacity-0 transition-all duration-300 pointer-events-none z-[100]">
        <div class="bg-slate-800 text-white px-6 py-3 rounded-lg shadow-2xl flex items-center space-x-3">
            <span id="toastIcon"></span>
            <span id="toastMessage"></span>
        </div>
    </div>

    <script src="script.js"></script>

    <!-- Preview Modal -->
    <div id="previewModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] hidden overflow-y-auto p-4 md:p-8">
        <div class="bg-white rounded-2xl shadow-2xl max-w-7xl mx-auto min-h-screen">
            <div class="sticky top-0 bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center rounded-t-2xl z-10">
                <h3 class="text-xl font-bold text-slate-800">Draft Schedule Preview</h3>
                <button onclick="togglePreview()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="bi bi-x-lg text-2xl"></i>
                </button>
            </div>
            <div class="p-6 overflow-x-auto" id="previewContent">
                <!-- Preview content will be injected here -->
            </div>
        </div>
    </div>

    <!-- Versions Modal -->
    <div id="versionsModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] hidden overflow-y-auto p-4 md:p-8">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl mx-auto">
            <div class="sticky top-0 bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center rounded-t-2xl z-10">
                <h3 class="text-xl font-bold text-slate-800">Published Version History</h3>
                <button onclick="toggleVersions()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="bi bi-x-lg text-2xl"></i>
                </button>
            </div>
            <div class="p-6" id="versionsList">
                <!-- Version list will be injected here -->
            </div>
        </div>
    </div>

    <script>
        csrfToken = '<?php echo $csrfToken; ?>';
        document.addEventListener('DOMContentLoaded', () => {
            loadScheduleData(true);
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });
        
        function updateDateTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            document.getElementById('currentDateTime').innerText = now.toLocaleDateString('en-US', options);
        }
    </script>
</body>
</html>
