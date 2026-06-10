/**
 * Binalbagan Catholic College Official Schedule System
 * Frontend Logic
 */

let scheduleData = {
    headers: [],
    rows: [],
    cells: {}
};

let csrfToken = '';

/**
 * Load Schedule Data from API
 */
async function loadScheduleData(isAdmin = false) {
    try {
        const action = isAdmin ? 'load_admin' : 'load_public';
        const response = await fetch(`api.php?action=${action}`);
        const data = await response.json();
        
        if (data.status === 'success') {
            scheduleData = data;
            
            if (!isAdmin && (!data.headers || data.headers.length === 0)) {
                document.getElementById('noDataMessage').classList.remove('hidden');
                document.getElementById('scheduleTable').classList.add('hidden');
            } else {
                if (document.getElementById('noDataMessage')) document.getElementById('noDataMessage').classList.add('hidden');
                document.getElementById('scheduleTable').classList.remove('hidden');
                renderTable(isAdmin);
            }

            if (isAdmin && data.last_modified) {
                const lmDisplay = document.getElementById('lastModifiedDisplay');
                if (lmDisplay) {
                    lmDisplay.textContent = `Last Modified: ${new Date(data.last_modified).toLocaleString()}`;
                }
            }
            
            // Update metadata
            const versionTag = document.getElementById('versionTag');
            const pubDate = document.getElementById('pubDate');
            if (versionTag && data.published) versionTag.textContent = `VER ${data.published.version}.0`;
            if (pubDate && data.published && data.published.published_at) {
                pubDate.textContent = new Date(data.published.published_at).toLocaleString();
            }
        }
    } catch (error) {
        console.error('Error loading schedule:', error);
        showToast('error', 'Failed to load schedule data.');
    } finally {
        const skeleton = document.getElementById('skeletonLoader');
        if (skeleton) skeleton.classList.add('hidden');
    }
}

/**
 * Render Table Structure
 */
function renderTable(isAdmin = false) {
    const headerRow = document.getElementById('tableHeaderRow');
    const tableBody = document.getElementById('tableBody');
    if (!headerRow || !tableBody) return;
    
    headerRow.innerHTML = '';
    tableBody.innerHTML = '';
    
    // 1. Render Headers
    scheduleData.headers.forEach(header => {
        const th = document.createElement('th');
        th.className = 'px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-200 bg-slate-50';
        
        if (isAdmin) {
            const container = document.createElement('div');
            container.className = 'flex justify-between items-center group';

            const span = document.createElement('span');
            span.textContent = header.header_name;
            span.className = 'cursor-pointer hover:text-teal-600 transition-colors';
            span.title = 'Click to rename';
            span.onclick = () => renameColumn(header.id, header.header_name);

            container.appendChild(span);

            if (header.position > 1) {
                const btn = document.createElement('button');
                btn.className = 'text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity';
                btn.innerHTML = '<i class="bi bi-trash"></i>';
                btn.onclick = (e) => { e.stopPropagation(); deleteColumn(header.id); };
                container.appendChild(btn);
            }

            th.appendChild(container);
        } else {
            th.textContent = header.header_name;
        }
        headerRow.appendChild(th);
    });
    
    // 2. Render Rows
    scheduleData.rows.forEach(row => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50 transition-colors border-b border-slate-100';
        
        scheduleData.headers.forEach((header, index) => {
            const td = document.createElement('td');
            const cellInfo = (scheduleData.cells[row.id] && scheduleData.cells[row.id][header.id]) || null;
            const content = cellInfo ? cellInfo.content : '';
            
            if (isAdmin) {
                td.className = 'admin-cell border border-slate-100';
                if (cellInfo && cellInfo.bg_color) td.style.backgroundColor = cellInfo.bg_color;
                
                const textarea = document.createElement('textarea');
                textarea.value = content || '';
                textarea.placeholder = index === 0 ? 'Enter time...' : '...';
                if (cellInfo) {
                    textarea.style.fontWeight = cellInfo.font_weight || 'normal';
                    textarea.style.textAlign = cellInfo.text_align || 'left';
                }
                
                textarea.addEventListener('change', (e) => saveCell(row.id, header.id, e.target.value));
                textarea.addEventListener('input', showSaveIndicator);
                textarea.addEventListener('click', (e) => selectCell(row.id, header.id, e.target));
                
                td.appendChild(textarea);
                
                if (index === scheduleData.headers.length - 1) {
                    const delBtn = document.createElement('button');
                    delBtn.className = 'absolute -right-10 top-1/2 -translate-y-1/2 text-red-400 hover:text-red-600 p-2 transition-colors no-print';
                    delBtn.innerHTML = '<i class="bi bi-trash-fill"></i>';
                    delBtn.onclick = () => deleteRow(row.id);
                    td.appendChild(delBtn);
                }
            } else {
                td.className = 'p-4 border border-slate-100 align-top';
                if (content) {
                    const container = document.createElement('div');
                    if (index === 0) {
                        container.className = 'font-bold text-teal-800 text-sm whitespace-nowrap';
                        container.textContent = content;
                    } else {
                        container.className = 'schedule-badge';
                        if (cellInfo.bg_color) container.style.backgroundColor = cellInfo.bg_color;
                        container.style.fontWeight = cellInfo.font_weight || 'normal';
                        container.style.textAlign = cellInfo.text_align || 'left';
                        
                        content.split('\n').forEach((line, i) => {
                            if (i > 0) container.appendChild(document.createElement('br'));
                            container.appendChild(document.createTextNode(line));
                        });
                    }
                    td.appendChild(container);
                } else {
                    if (index !== 0) {
                        const span = document.createElement('span');
                        span.className = 'empty-slot';
                        span.textContent = '---';
                        td.appendChild(span);
                    }
                }
            }
            tr.appendChild(td);
        });
        tableBody.appendChild(tr);
    });
}

let selectedCell = null;

function selectCell(rowId, headerId, element) {
    selectedCell = { rowId, headerId, element };
    document.querySelectorAll('.admin-cell textarea').forEach(el => el.classList.remove('ring-2', 'ring-teal-500'));
    element.classList.add('ring-2', 'ring-teal-500');
    const toolbar = document.getElementById('formattingToolbar');
    if (toolbar) toolbar.classList.remove('hidden');
}

async function applyFormat(property, value) {
    if (!selectedCell) return;
    const rowId = selectedCell.rowId;
    const headerId = selectedCell.headerId;
    if (!scheduleData.cells[rowId]) scheduleData.cells[rowId] = {};
    if (!scheduleData.cells[rowId][headerId]) scheduleData.cells[rowId][headerId] = { content: selectedCell.element.value };
    
    const cell = scheduleData.cells[rowId][headerId];
    cell[property] = value;
    
    if (property === 'bg_color') selectedCell.element.parentElement.style.backgroundColor = value === 'transparent' ? '' : value;
    if (property === 'font_weight') selectedCell.element.style.fontWeight = value;
    if (property === 'text_align') selectedCell.element.style.textAlign = value;
    
    await saveCell(rowId, headerId, selectedCell.element.value, cell);
}

/**
 * Save Cell via AJAX
 */
async function saveCell(rowId, headerId, content, formats = null) {
    const cell = formats || (scheduleData.cells[rowId] && scheduleData.cells[rowId][headerId]) || {};
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                action: 'save_cell',
                csrf_token: csrfToken,
                row_id: rowId, 
                header_id: headerId, 
                content: content,
                bg_color: cell.bg_color,
                font_weight: cell.font_weight,
                text_align: cell.text_align
            })
        });
        const data = await response.json();
        if (data.status === 'success') {
            hideSaveIndicator();
            if (!scheduleData.cells[rowId]) scheduleData.cells[rowId] = {};
            scheduleData.cells[rowId][headerId] = { ...cell, content: content };

            // Update last modified display
            const now = new Date();
            const lmDisplay = document.getElementById('lastModifiedDisplay');
            if (lmDisplay) {
                lmDisplay.textContent = `Last Modified: ${now.toLocaleString()}`;
            }
        }
    } catch (e) { showToast('error', 'Auto-save failed.'); }
}

async function manualSave() {
    showSaveIndicator();
    // In this implementation, change events already trigger saveCell.
    // Manual save will just ensure the currently focused cell is saved and give feedback.
    if (selectedCell) {
        await saveCell(selectedCell.rowId, selectedCell.headerId, selectedCell.element.value);
    }
    showToast('success', 'All changes synchronized to server.');
    hideSaveIndicator();
}

function togglePreview() {
    const modal = document.getElementById('previewModal');
    if (modal.classList.contains('hidden')) {
        renderPreview();
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    } else {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function renderPreview() {
    const container = document.getElementById('previewContent');
    container.innerHTML = '';

    const table = document.createElement('table');
    table.className = 'w-full border-collapse bg-white';

    // Header
    const thead = document.createElement('thead');
    const trH = document.createElement('tr');
    trH.className = 'bg-slate-50 border-b border-slate-200';

    scheduleData.headers.forEach(h => {
        const th = document.createElement('th');
        th.className = 'px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-200';
        th.textContent = h.header_name;
        trH.appendChild(th);
    });
    thead.appendChild(trH);
    table.appendChild(thead);

    // Body
    const tbody = document.createElement('tbody');
    scheduleData.rows.forEach(row => {
        const tr = document.createElement('tr');
        tr.className = 'border-b border-slate-100';

        scheduleData.headers.forEach((header, index) => {
            const td = document.createElement('td');
            td.className = 'p-4 border border-slate-100 align-top';

            const cellInfo = (scheduleData.cells[row.id] && scheduleData.cells[row.id][header.id]) || null;
            const content = cellInfo ? cellInfo.content : '';

            if (content) {
                const div = document.createElement('div');
                if (index === 0) {
                    div.className = 'font-bold text-teal-800 text-sm whitespace-nowrap';
                    div.textContent = content;
                } else {
                    div.className = 'schedule-badge';
                    if (cellInfo.bg_color) div.style.backgroundColor = cellInfo.bg_color;
                    div.style.fontWeight = cellInfo.font_weight || 'normal';
                    div.style.textAlign = cellInfo.text_align || 'left';

                    content.split('\n').forEach((line, i) => {
                        if (i > 0) div.appendChild(document.createElement('br'));
                        div.appendChild(document.createTextNode(line));
                    });
                }
                td.appendChild(div);
            }
            tr.appendChild(td);
        });
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    container.appendChild(table);
}

async function toggleVersions() {
    const modal = document.getElementById('versionsModal');
    if (modal.classList.contains('hidden')) {
        await loadVersionHistory();
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    } else {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

async function loadVersionHistory() {
    const container = document.getElementById('versionsList');
    container.innerHTML = '<div class="text-center py-4"><i class="bi bi-arrow-repeat animate-spin text-2xl text-teal-600"></i></div>';

    try {
        const response = await fetch('api.php?action=get_versions');
        const data = await response.json();

        if (data.status === 'success') {
            if (data.versions.length === 0) {
                container.innerHTML = '<p class="text-center text-slate-500">No published versions yet.</p>';
                return;
            }

            const list = document.createElement('div');
            list.className = 'space-y-4';

            data.versions.forEach(v => {
                const item = document.createElement('div');
                item.className = 'flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-200';

                const info = document.createElement('div');
                info.innerHTML = `
                    <div class="font-bold text-slate-800">Version ${v.version}.0</div>
                    <div class="text-xs text-slate-500">${new Date(v.published_at).toLocaleString()}</div>
                `;

                const badge = document.createElement('span');
                badge.className = 'px-3 py-1 bg-teal-100 text-teal-700 rounded-full text-xs font-bold';
                badge.textContent = 'Published';

                item.appendChild(info);
                item.appendChild(badge);
                list.appendChild(item);
            });

            container.innerHTML = '';
            container.appendChild(list);
        }
    } catch (e) {
        container.innerHTML = '<p class="text-center text-red-500">Failed to load history.</p>';
    }
}

/**
 * Actions
 */
async function addNewRow() {
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'add_row', csrf_token: csrfToken })
        });
        const data = await response.json();
        if (data.status === 'success') { showToast('success', 'Row added.'); loadScheduleData(true); }
    } catch (e) { showToast('error', 'Error.'); }
}

async function promptNewColumn() {
    const colName = prompt('Column Name:');
    if (!colName) return;
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'add_header', header_name: colName, csrf_token: csrfToken })
        });
        const data = await response.json();
        if (data.status === 'success') { showToast('success', 'Column added.'); loadScheduleData(true); }
    } catch (e) { showToast('error', 'Error.'); }
}

async function deleteRow(id) {
    if (!confirm('Delete row?')) return;
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete_row', id: id, csrf_token: csrfToken })
        });
        const data = await response.json();
        if (data.status === 'success') loadScheduleData(true);
    } catch (e) { showToast('error', 'Error.'); }
}

async function deleteColumn(id) {
    if (!confirm('Delete column?')) return;
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete_header', id: id, csrf_token: csrfToken })
        });
        const data = await response.json();
        if (data.status === 'success') loadScheduleData(true);
    } catch (e) { showToast('error', 'Error.'); }
}

async function renameColumn(id, currentName) {
    const newName = prompt('Enter new column name:', currentName);
    if (!newName || newName === currentName) return;
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'rename_header', id: id, header_name: newName, csrf_token: csrfToken })
        });
        const data = await response.json();
        if (data.status === 'success') {
            showToast('success', 'Column renamed.');
            loadScheduleData(true);
        }
    } catch (e) { showToast('error', 'Error.'); }
}

async function publishSchedule() {
    if (!confirm('Publish these changes to the official portal?')) return;
    const btn = event.currentTarget;
    const original = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin mr-2"></i> Publishing...';
    btn.disabled = true;
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'publish', csrf_token: csrfToken })
        });
        const data = await response.json();
        if (data.status === 'success') showToast('success', `Published! Version ${data.version} is live.`);
    } catch (e) { showToast('error', 'Publish failed.'); }
    finally { btn.innerHTML = original; btn.disabled = false; }
}

/**
 * Export Logic
 */
function downloadExcel() {
    const table = document.getElementById('scheduleTable');
    const wb = XLSX.utils.table_to_book(table, { sheet: "Teacher Schedule" });
    XLSX.writeFile(wb, "BCC_Teacher_Schedule.xlsx");
}

/**
 * UI Helpers
 */
function showToast(type, message) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    document.getElementById('toastMessage').textContent = message;
    document.getElementById('toastIcon').innerHTML = type === 'success' ? '<i class="bi bi-check-circle-fill text-green-400"></i>' : '<i class="bi bi-exclamation-triangle-fill text-red-400"></i>';
    toast.classList.add('show-toast');
    setTimeout(() => toast.classList.remove('show-toast'), 4000);
}

function showSaveIndicator() {
    const indicator = document.getElementById('saveIndicator');
    if (indicator) {
        indicator.innerHTML = '<i class="bi bi-arrow-repeat animate-spin mr-1"></i> Saving draft...';
        indicator.classList.remove('hidden');
    }
}

function hideSaveIndicator() {
    const indicator = document.getElementById('saveIndicator');
    if (indicator) {
        indicator.innerHTML = '<i class="bi bi-check2-all mr-1 text-green-500"></i> Draft Saved';
        setTimeout(() => indicator.classList.add('hidden'), 2000);
    }
}

// Search
if (document.getElementById('searchInput')) {
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#tableBody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    });
}
