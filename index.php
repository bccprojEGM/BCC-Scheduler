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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --background-color: #F8F7F2;
            --surface-color: #ffffff;
            --default-color: #2D3748;
            --heading-color: #003366;
            --accent-color: #E6A519;
        }

        body { font-family: 'Inter', sans-serif; }
        .header-gradient { background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); }

        /* LDRRMO Form Styles */
        #contact-us-ldrrmo-binalbagan-registry {
            background-color: var(--background-color);
            padding: 60px 0;
            margin-bottom: 300px;
        }

        .ldrrmo-form-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .ldrrmo-form-hero {
            background: var(--surface-color);
            padding: 45px;
            border-radius: 12px;
            border: 1px solid rgba(0, 51, 102, 0.08);
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.01);
        }

        .ldrrmo-form-hero h2 {
            color: var(--heading-color);
            margin: 0 auto 12px auto;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            font-size: 2.25rem;
            font-weight: 800;
        }

        .ldrrmo-form-subtitle {
            color: var(--accent-color);
            letter-spacing: 0.1em;
            font-weight: 600;
            margin-bottom: 24px;
            text-transform: uppercase;
        }

        .ldrrmo-form-description {
            text-align: justify;
            line-height: 1.7;
            color: var(--default-color);
            font-size: 1.05rem;
            max-width: 950px;
            margin: 24px auto 0 auto;
        }

        .ldrrmo-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 35px;
            margin-top: 40px;
        }

        .ldrrmo-form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }

        .ldrrmo-form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--default-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ldrrmo-form-input, .ldrrmo-form-select, .ldrrmo-form-textarea {
            width: 100%;
            min-height: 48px;
            font-size: 16px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: #fdfdfd;
            transition: all 0.2s ease-in-out;
            color: var(--default-color);
        }

        .ldrrmo-form-input:focus, .ldrrmo-form-select:focus, .ldrrmo-form-textarea:focus {
            outline: none;
            border-left: 5px solid var(--accent-color);
            background: #fff;
            box-shadow: 0 0 8px rgba(230, 165, 25, 0.2);
        }

        .ldrrmo-form-mandatory {
            color: var(--accent-color);
        }

        .ldrrmo-form-submit {
            display: flex;
            width: 100%;
            height: 50px;
            background: var(--heading-color);
            color: #fff;
            font-size: 1.1rem;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            transition: background 0.2s ease;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .ldrrmo-form-submit:hover {
            background: var(--accent-color);
        }

        .ldrrmo-form-notice {
            background: var(--surface-color);
            border: 1px solid rgba(0, 51, 102, 0.06);
            border-left: 6px solid var(--accent-color);
            padding: 35px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.01);
        }

        .ldrrmo-form-notice-header {
            color: var(--heading-color);
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ldrrmo-form-mandate {
            color: var(--default-color);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 20px;
            text-align: justify;
        }

        .ldrrmo-form-vulnerability-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .ldrrmo-form-vulnerability-item {
            display: flex;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px dashed rgba(0,0,0,0.04);
            font-size: 0.95rem;
            color: var(--default-color);
            align-items: center;
        }

        .ldrrmo-form-vulnerability-item i {
            color: var(--accent-color);
        }

        @media (max-width: 992px) {
            .ldrrmo-form-grid {
                grid-template-columns: 1fr;
                gap: 25px;
            }
            .ldrrmo-form-hero h2 {
                font-size: 1.8rem;
            }
            .ldrrmo-form-notice {
                height: auto;
            }
        }
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

    <main class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8 print-container mb-12">
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

        <section id="contact-us-ldrrmo-binalbagan-registry" class="no-print">
            <div class="ldrrmo-form-container">
                <!-- Tier A -->
                <div class="ldrrmo-form-hero">
                    <h2><i class="fas fa-bullhorn"></i> CONTACT US: LDRRMO BINALBAGAN</h2>
                    <p class="ldrrmo-form-subtitle">Municipal Agriculture Office Frontline Services & Incident Reporting</p>
                    <div class="ldrrmo-form-description">
                        This directory outlines the processing timelines, target audiences, and primary purposes for the frontline agricultural, livestock, and fisheries transactions of the municipality. All workflows conform to national ease-of-doing-business and anti-red tape standards.
                    </div>
                </div>

                <!-- Tier B -->
                <div class="ldrrmo-form-grid">
                    <!-- Left Column -->
                    <div class="ldrrmo-form-intake">
                        <form id="ldrrmo-report-form">
                            <div class="ldrrmo-form-group">
                                <label class="ldrrmo-form-label" for="full_name">
                                    <i class="fas fa-user"></i> Full Name (Optional)
                                </label>
                                <input type="text" id="full_name" name="full_name" class="ldrrmo-form-input" placeholder="e.g., Juan Dela Cruz">
                            </div>

                            <div class="ldrrmo-form-group">
                                <label class="ldrrmo-form-label" for="contact_number">
                                    <i class="fas fa-phone"></i> Contact Number <span class="ldrrmo-form-mandatory">*</span>
                                </label>
                                <input type="tel" id="contact_number" name="contact_number" class="ldrrmo-form-input" placeholder="e.g., 09123456789" required>
                            </div>

                            <div class="ldrrmo-form-group">
                                <label class="ldrrmo-form-label" for="report_nature">
                                    <i class="fas fa-exclamation-circle"></i> Nature of Report <span class="ldrrmo-form-mandatory">*</span>
                                </label>
                                <select id="report_nature" name="report_nature" class="ldrrmo-form-select" required>
                                    <option value="" disabled selected>-- Select Incident Type --</option>
                                    <option value="Flooding / Water Level Rise">Flooding / Water Level Rise</option>
                                    <option value="Landslide / Road Obstruction">Landslide / Road Obstruction</option>
                                    <option value="Medical Emergency / Rescue Request">Medical Emergency / Rescue Request</option>
                                    <option value="Fire Incident">Fire Incident</option>
                                    <option value="Weather / Typhoon Damage">Weather / Typhoon Damage</option>
                                    <option value="General Inquiry / Non-Emergency">General Inquiry / Non-Emergency</option>
                                </select>
                            </div>

                            <div class="ldrrmo-form-group">
                                <label class="ldrrmo-form-label" for="situation_details">
                                    <i class="fas fa-align-left"></i> Situation Details / Address <span class="ldrrmo-form-mandatory">*</span>
                                </label>
                                <textarea id="situation_details" name="situation_details" class="ldrrmo-form-textarea" rows="5" placeholder="Please describe the situation. Include landmarks or exact location if possible..." required></textarea>
                            </div>

                            <button type="submit" class="ldrrmo-form-submit">
                                <i class="fas fa-paper-plane"></i> Submit Report
                            </button>
                        </form>
                    </div>

                    <!-- Right Column -->
                    <div class="ldrrmo-form-notice">
                        <div class="ldrrmo-form-notice-header">
                            <i class="fas fa-info-circle"></i> IMPORTANT NOTE
                        </div>
                        <div class="ldrrmo-form-mandate">
                            The processing times indicated in this Citizen's Charter Directory represent the standard service delivery timelines under normal operating conditions and are provided in accordance with the principles of transparency, accountability, and efficient public service.
                        </div>
                        <ul class="ldrrmo-form-vulnerability-list">
                            <li class="ldrrmo-form-vulnerability-item">
                                <i class="fas fa-exclamation-triangle"></i> Completeness of documents
                            </li>
                            <li class="ldrrmo-form-vulnerability-item">
                                <i class="fas fa-exclamation-triangle"></i> Personnel availability
                            </li>
                            <li class="ldrrmo-form-vulnerability-item">
                                <i class="fas fa-exclamation-triangle"></i> Application volume
                            </li>
                            <li class="ldrrmo-form-vulnerability-item">
                                <i class="fas fa-exclamation-triangle"></i> Weather/Field accessibility
                            </li>
                            <li class="ldrrmo-form-vulnerability-item">
                                <i class="fas fa-exclamation-triangle"></i> Input allocations
                            </li>
                            <li class="ldrrmo-form-vulnerability-item">
                                <i class="fas fa-exclamation-triangle"></i> Partner coordination
                            </li>
                            <li class="ldrrmo-form-vulnerability-item">
                                <i class="fas fa-exclamation-triangle"></i> Emergency situations
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Success Modal -->
        <div id="ldrrmo-success-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[100] p-4 no-print">
            <div class="bg-white rounded-xl max-w-md w-full p-8 shadow-2xl border-t-4 border-[#E6A519]">
                <div class="text-center">
                    <i class="fas fa-check-circle text-5xl text-emerald-500 mb-4"></i>
                    <h3 class="text-2xl font-bold text-[#003366] mb-2">Report Received</h3>
                    <div id="ldrrmo-modal-message" class="text-[#2D3748] mb-6 text-sm leading-relaxed">
                        Report received. If this is a life-threatening emergency demanding immediate rescue, please also call our direct hotline at [Hotline Number].
                    </div>
                    <button onclick="document.getElementById('ldrrmo-success-modal').classList.add('hidden')" class="w-full bg-[#003366] text-white font-bold py-3 rounded-lg hover:bg-[#E6A519] transition-all">
                        UNDERSTOOD
                    </button>
                </div>
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

            // LDRRMO Form Handling
            const reportForm = document.getElementById('ldrrmo-report-form');
            if (reportForm) {
                reportForm.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const submitBtn = reportForm.querySelector('.ldrrmo-form-submit');
                    const originalBtnContent = submitBtn.innerHTML;

                    // Loading state
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> PROCESSING...';

                    try {
                        const formData = new FormData(reportForm);
                        const response = await fetch('submit-report.php', {
                            method: 'POST',
                            body: formData
                        });

                        const result = await response.json();

                        if (result.status === 'success') {
                            document.getElementById('ldrrmo-modal-message').innerText = result.message;
                            document.getElementById('ldrrmo-success-modal').classList.remove('hidden');
                            reportForm.reset();
                        } else {
                            alert(result.message || 'An error occurred while submitting the report.');
                        }
                    } catch (error) {
                        console.error('Submission error:', error);
                        alert('Could not connect to the reporting server. Please check your internet connection or call the hotline directly.');
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnContent;
                    }
                });
            }
        });
    </script>
</body>
</html>
