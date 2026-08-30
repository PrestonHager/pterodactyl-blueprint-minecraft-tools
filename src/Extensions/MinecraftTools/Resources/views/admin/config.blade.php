@extends('admin.layouts.default')

@section('title', 'Config Editor - Minecraft Tools')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Configuration Editor</h1>
                <div>
                    <button type="button" class="btn btn-outline-primary me-2" id="applyTemplateBtn">
                        <i class="fas fa-magic"></i> Apply Template
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="refreshConfigsBtn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5>Config Files</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" id="configFileList">
                                <div class="list-group-item">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    Loading...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-9">
                    <div class="card" id="configEditorCard" style="display: none;">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 id="configFileName">Select a config file</h5>
                            <div>
                                <button type="button" class="btn btn-outline-secondary btn-sm me-1" id="validateConfigBtn">
                                    <i class="fas fa-check"></i> Validate
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm me-1" id="backupConfigBtn">
                                    <i class="fas fa-save"></i> Backup
                                </button>
                                <button type="button" class="btn btn-primary btn-sm" id="saveConfigBtn">
                                    <i class="fas fa-save"></i> Save
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div id="editor" style="height: 600px;"></div>
                        </div>
                    </div>
                    
                    <div class="card" id="noConfigSelectedCard">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-file-code fa-3x text-muted mb-3"></i>
                            <h5>No Config Selected</h5>
                            <p class="text-muted">Select a configuration file from the list to edit it</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Apply Template Modal -->
<div class="modal fade" id="applyTemplateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Apply Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="applyTemplateForm">
                    <div class="mb-3">
                        <label class="form-label">Template Type</label>
                        <select class="form-select" name="type" id="templateType">
                            <option value="paper">Paper</option>
                            <option value="spigot">Spigot</option>
                            <option value="purpur">Purpur</option>
                            <option value="vanilla">Vanilla</option>
                            <option value="fabric">Fabric</option>
                            <option value="forge">Forge</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Template</label>
                        <select class="form-select" name="template" id="templateSelect" required>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Target Config File</label>
                        <select class="form-select" name="file" id="templateTargetFile" required>
                        </select>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Applying a template will overwrite the target configuration file.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmApplyTemplate">Apply Template</button>
            </div>
        </div>
    </div>
</div>

<!-- Backup History Modal -->
<div class="modal fade" id="backupHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Backup History - <span id="backupFileName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="backupTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.14/ace.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.14/ext-language_tools.min.js"></script>
<script>
let editor;
let currentFile = null;

document.addEventListener('DOMContentLoaded', function() {
    initEditor();
    loadConfigFiles();
    
    document.getElementById('refreshConfigsBtn').addEventListener('click', loadConfigFiles);
    document.getElementById('saveConfigBtn').addEventListener('click', saveConfig);
    document.getElementById('validateConfigBtn').addEventListener('click', validateConfig);
    document.getElementById('backupConfigBtn').addEventListener('click', showBackupHistory);
    document.getElementById('applyTemplateBtn').addEventListener('click', showApplyTemplateModal);
    document.getElementById('confirmApplyTemplate').addEventListener('click', applyTemplate);
    document.getElementById('templateType').addEventListener('change', loadTemplates);
});

function initEditor() {
    editor = ace.edit('editor');
    editor.setTheme('ace/theme/monokai');
    editor.session.setMode('ace/mode/yaml');
    editor.setOptions({
        enableBasicAutocompletion: true,
        enableSnippets: true,
        enableLiveAutocompletion: true,
        tabSize: 2,
        useSoftTabs: true,
        showPrintMargin: false,
        wrap: true,
    });
    
    editor.commands.addCommand({
        name: 'save',
        bindKey: { win: 'Ctrl-S', mac: 'Command-S' },
        exec: function() { saveConfig(); },
        readOnly: false
    });
}

function loadConfigFiles() {
    fetch('/api/extensions/minecraft-tools/config/files', {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        renderConfigFileList(data.files);
        populateTemplateTargetFiles(data.files);
    })
    .catch(console.error);
}

function renderConfigFileList(files) {
    const list = document.getElementById('configFileList');
    list.innerHTML = '';
    
    if (files.length === 0) {
        list.innerHTML = '<div class="list-group-item text-muted">No config files found</div>';
        return;
    }
    
    files.forEach(file => {
        const item = document.createElement('a');
        item.href = '#';
        item.className = 'list-group-item list-group-item-action';
        item.dataset.file = file;
        item.innerHTML = `
            <i class="fas fa-file-code me-2"></i>
            ${file}
            <span class="badge bg-secondary float-end">${getFileType(file)}</span>
        `;
        item.addEventListener('click', function(e) {
            e.preventDefault();
            selectConfigFile(file);
        });
        list.appendChild(item);
    });
}

function getFileType(file) {
    if (file.endsWith('.properties')) return 'Properties';
    if (file.endsWith('.yml') || file.endsWith('.yaml')) return 'YAML';
    if (file.endsWith('.toml')) return 'TOML';
    if (file.endsWith('.json')) return 'JSON';
    return 'Config';
}

function selectConfigFile(file) {
    currentFile = file;
    
    document.querySelectorAll('#configFileList .list-group-item').forEach(item => {
        item.classList.toggle('active', item.dataset.file === file);
    });
    
    document.getElementById('noConfigSelectedCard').style.display = 'none';
    document.getElementById('configEditorCard').style.display = 'block';
    document.getElementById('configFileName').textContent = file;
    
    loadConfigContent(file);
}

function loadConfigContent(file) {
    fetch(`/api/extensions/minecraft-tools/config/${encodeURIComponent(file)}`, {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        const content = data.config?.content || data.config || '';
        setEditorMode(file);
        editor.setValue(content, -1);
        editor.clearSelection();
    })
    .catch(console.error);
}

function setEditorMode(file) {
    if (file.endsWith('.properties')) {
        editor.session.setMode('ace/mode/properties');
    } else if (file.endsWith('.yml') || file.endsWith('.yaml')) {
        editor.session.setMode('ace/mode/yaml');
    } else if (file.endsWith('.toml')) {
        editor.session.setMode('ace/mode/toml');
    } else if (file.endsWith('.json')) {
        editor.session.setMode('ace/mode/json');
    } else {
        editor.session.setMode('ace/mode/text');
    }
}

function saveConfig() {
    if (!currentFile) return;
    
    const content = editor.getValue();
    
    fetch(`/api/extensions/minecraft-tools/config/${encodeURIComponent(currentFile)}`, {
        method: 'PUT',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ content }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'updated') {
            showAlert('Configuration saved', 'success');
        }
    })
    .catch(console.error);
}

function validateConfig() {
    if (!currentFile) return;
    
    const content = editor.getValue();
    
    fetch('/api/extensions/minecraft-tools/config/validate', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ file: currentFile, content }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            showAlert('Configuration is valid', 'success');
        } else {
            showAlert('Validation errors: ' + data.errors.join(', '), 'danger');
        }
    })
    .catch(console.error);
}

function showBackupHistory() {
    if (!currentFile) return;
    
    document.getElementById('backupFileName').textContent = currentFile;
    loadBackupHistory(currentFile);
    new bootstrap.Modal(document.getElementById('backupHistoryModal')).show();
}

function loadBackupHistory(file) {
    fetch(`/api/extensions/minecraft-tools/config/${encodeURIComponent(file)}/backup`, {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        const tbody = document.querySelector('#backupTable tbody');
        tbody.innerHTML = '';
        
        if (data.backups && data.backups.length > 0) {
            data.backups.forEach(backup => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${new Date(backup.created_at).toLocaleString()}</td>
                    <td>${formatBytes(backup.size)}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="restoreBackup('${file}', '${backup.id}')">
                            <i class="fas fa-undo"></i> Restore
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No backups available</td></tr>';
        }
    })
    .catch(console.error);
}

function restoreBackup(file, backupId) {
    if (!confirm('Restore this backup? Current changes will be lost.')) return;
    
    fetch(`/api/extensions/minecraft-tools/config/${encodeURIComponent(file)}/restore`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ backup_id: backupId }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'restored') {
            loadConfigContent(file);
            bootstrap.Modal.getInstance(document.getElementById('backupHistoryModal')).hide();
            showAlert('Backup restored', 'success');
        }
    })
    .catch(console.error);
}

function showApplyTemplateModal() {
    loadTemplates();
    populateTemplateTargetFiles();
    new bootstrap.Modal(document.getElementById('applyTemplateModal')).show();
}

function loadTemplates() {
    const type = document.getElementById('templateType').value;
    
    fetch(`/api/extensions/minecraft-tools/config/templates?type=${type}`, {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        const select = document.getElementById('templateSelect');
        select.innerHTML = '<option value="">Select template</option>';
        data.templates.forEach(t => {
            const option = document.createElement('option');
            option.value = t.id;
            option.textContent = t.name;
            select.appendChild(option);
        });
    })
    .catch(console.error);
}

function populateTemplateTargetFiles(files = null) {
    if (!files) {
        fetch('/api/extensions/minecraft-tools/config/files', {
            headers: { 'Authorization': `Bearer {{ auth()->user()->api_token }}` }
        })
        .then(r => r.json())
        .then(data => populateTemplateTargetFiles(data.files))
        .catch(console.error);
        return;
    }
    
    const select = document.getElementById('templateTargetFile');
    const currentValue = select.value;
    select.innerHTML = '<option value="">Select target file</option>';
    
    files.forEach(file => {
        const option = document.createElement('option');
        option.value = file;
        option.textContent = file;
        select.appendChild(option);
    });
    
    if (files.includes(currentValue)) {
        select.value = currentValue;
    }
}

function applyTemplate() {
    const template = document.getElementById('templateSelect').value;
    const file = document.getElementById('templateTargetFile').value;
    
    if (!template || !file) {
        showAlert('Please select both template and target file', 'warning');
        return;
    }
    
    fetch(`/api/extensions/minecraft-tools/config/templates/${template}/apply`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ file }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'applied') {
            bootstrap.Modal.getInstance(document.getElementById('applyTemplateModal')).hide();
            if (file === currentFile) {
                loadConfigContent(file);
            }
            showAlert('Template applied', 'success');
        }
    })
    .catch(console.error);
}

function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function showAlert(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alert.style.zIndex = '9999';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
}

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}
</script>
@endsection