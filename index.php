<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Teacher Schedule - Binalbagan Catholic College</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .header-gradient { background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); }
        @media print {
            .no-print { display: none !important; }
            body { background: white; padding: 0; margin: 0; }
            .print-container { width: 100%; }
            #scheduleTable { border: 1px solid #ddd; width: 100%; border-collapse: collapse; }
            #scheduleTable th, #scheduleTable td { border: 1px solid #ddd; padding: 8px; font-size: 10pt; }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <!-- Public Header -->
    <header class="header-gradient text-white shadow-lg no-print">
        <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-center md:text-left">
                    <div class="flex items-center justify-center md:justify-start space-x-4 mb-2">
                         <div class="bg-white p-1.5 rounded-full shadow-lg">
                            <div class="w-10 h-10 bg-teal-800 rounded-full flex items-center justify-center font-bold text-sm">BCC</div>
                         </div>
                         <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Binalbagan Catholic College</h1>
                    </div>
                    <p class="text-teal-100 text-lg">Official Teacher Schedule Matrix</p>
                </div>
                <div class="mt-6 md:mt-0 flex flex-col items-center md:items-end">
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 px-4 py-1.5 rounded-full mb-3">
                        <span class="text-xs font-bold uppercase tracking-widest">Institutional Portal</span>
                    </div>
                    <?php if (isLoggedIn()): ?>
                        <a href="dashboard.php" class="text-teal-200 hover:text-white text-sm transition-colors flex items-center">
                            <i class="bi bi-speedometer2 mr-1"></i> Admin Dashboard
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8 print-container">
        <!-- Actions Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6 flex flex-col lg:flex-row justify-between items-center gap-4 no-print">
            <div class="relative w-full lg:w-96">
                <i class="bi bi-search absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="searchInput" placeholder="Search teacher, subject, room..." 
                    class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all">
            </div>
            
            <div class="flex items-center space-x-2 w-full lg:w-auto overflow-x-auto">
                <button onclick="downloadExcel()" class="flex-1 lg:flex-none bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all flex items-center justify-center">
                    <i class="bi bi-file-earmark-excel mr-2"></i> Excel
                </button>
                <button onclick="window.print()" class="flex-1 lg:flex-none bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all flex items-center justify-center">
                    <i class="bi bi-printer mr-2"></i> Print / PDF
                </button>
            </div>
        </div>

        <!-- Schedule Display -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-slate-200">
            <div class="overflow-x-auto">
                <table id="scheduleTable" class="w-full border-collapse">
                    <thead>
                        <tr id="tableHeaderRow" class="bg-slate-50 border-b border-slate-200"></tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>
            <div id="noDataMessage" class="hidden p-20 text-center text-slate-400">
                <i class="bi bi-calendar-x text-5xl mb-4 block"></i>
                <p class="text-xl font-medium">No published schedule available.</p>
            </div>
        </div>

        <!-- Info Footer -->
        <div class="mt-6 flex flex-col md:flex-row justify-between items-center text-slate-400 text-xs px-2 no-print">
            <div id="lastUpdated">
                <i class="bi bi-info-circle mr-1"></i> Official copy as of <span id="pubDate">...</span>
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-4">
                <span id="versionTag" class="font-bold text-slate-300"></span>
                <span>&copy; <?php echo date('Y'); ?> Binalbagan Catholic College</span>
            </div>
        </div>
    </main>

    <script src="script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadScheduleData(false);
            // Poll for updates every 60 seconds
            setInterval(() => loadScheduleData(false), 60000);
        });
    </script>
</body>
</html>
