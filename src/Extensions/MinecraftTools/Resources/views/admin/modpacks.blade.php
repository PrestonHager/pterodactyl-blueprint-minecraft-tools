@extends('admin.layouts.default')

@section('title', 'Modpacks - Minecraft Tools')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Modpack Management</h1>
                <button type="button" class="btn btn-primary" id="installModpackBtn">
                    <i class="fas fa-plus"></i> Install Modpack
                </button>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Current Modpack</h5>
                        </div>
                        <div class="card-body">
                            <div id="currentModpack">Loading...</div>
                            <button type="button" class="btn btn-outline-primary mt-2" id="switchModpackBtn">
                                <i class="fas fa-exchange-alt"></i> Switch Modpack
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Modpack Info</h5>
                        </div>
                        <div class="card-body">
                            <div id="modpackInfo"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-5">
                            <input type="text" class="form-control" id="modpackSearch" placeholder="Search modpacks...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="modpackSource">
                                <option value="modrinth">Modrinth</option>
                                <option value="curseforge">CurseForge</option>
                                <option value="technic">Technic</option>
                                <option value="ftb">FTB</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="modpackCategory">
                                <option value="">All Categories</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="modpackFilter">
                                <option value="all">All</option>
                                <option value="installed">Installed</option>
                                <option value="available">Available</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="modpacksTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Version</th>
                                    <th>Source</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Modpacks loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Install Modpack Modal -->
<div class="modal fade" id="installModpackModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Install Modpack</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="installModpackForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Source</label>
                                <select class="form-select" name="source" id="installModpackSource">
                                    <option value="modrinth">Modrinth</option>
                                    <option value="curseforge">CurseForge</option>
                                    <option value="technic">Technic</option>
                                    <option value="ftb">FTB</option>
                                    <option value="local">Local File</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Modpack Name / ID</label>
                                <input type="text" class="form-control" name="name" id="installModpackName" placeholder="Modpack name or slug" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="versionField" style="display: none;">
                        <label class="form-label">Version</label>
                        <select class="form-select" name="version" id="installModpackVersion">
                            <option value="">Latest</option>
                        </select>
                    </div>
                    <div class="mb-3" id="fileField" style="display: none;">
                        <label class="form-label">Modpack File</label>
                        <input type="file" class="form-control" name="modpack_file" accept=".zip,.mrpack">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmInstallModpack">Install</button>
            </div>
        </div>
    </div>
</div>

<!-- Switch Modpack Modal -->
<div class="modal fade" id="switchModpackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Switch Modpack</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Select the modpack to switch to:</p>
                <div class="mb-3">
                    <label class="form-label">Modpack</label>
                    <select class="form-select" id="switchModpackSelect" required>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Version</label>
                    <select class="form-select" id="switchModpackVersionSelect" required>
                        <option value="">Default</option>
                    </select>
                </div>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Switching modpacks will restart the server and replace server files. Ensure you have a backup.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmSwitchModpack">Switch Modpack</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadModpacks();
    loadCurrentModpack();
    loadCategories();
    
    document.getElementById('installModpackBtn').addEventListener('click', function() {
        document.getElementById('installModpackForm').reset();
        document.getElementById('versionField').style.display = 'none';
        document.getElementById('fileField').style.display = 'none';
        new bootstrap.Modal(document.getElementById('installModpackModal')).show();
    });
    
    document.getElementById('switchModpackBtn').addEventListener('click', function() {
        populateSwitchModal();
        new bootstrap.Modal(document.getElementById('switchModpackModal')).show();
    });
    
    document.getElementById('installModpackSource').addEventListener('change', function() {
        const versionField = document.getElementById('versionField');
        const fileField = document.getElementById('fileField');
        const nameField = document.getElementById('installModpackName');
        
        if (this.value === 'local') {
            versionField.style.display = 'none';
            fileField.style.display = 'block';
            nameField.placeholder = 'Modpack name';
        } else {
            versionField.style.display = 'block';
            fileField.style.display = 'none';
            nameField.placeholder = 'Modpack slug or name';
            loadModpackVersions(this.value, nameField.value);
        }
    });
    
    document.getElementById('installModpackName').addEventListener('input', debounce(function() {
        const source = document.getElementById('installModpackSource').value;
        if (source !== 'local') {
            loadModpackVersions(source, this.value);
        }
    }, 500));
    
    document.getElementById('confirmInstallModpack').addEventListener('click', installModpack);
    document.getElementById('confirmSwitchModpack').addEventListener('click', switchModpack);
    document.getElementById('modpackSearch').addEventListener('input', debounce(loadModpacks, 300));
    document.getElementById('modpackFilter').addEventListener('change', loadModpacks);
    document.getElementById('modpackSource').addEventListener('change', loadModpacks);
    document.getElementById('modpackCategory').addEventListener('change', loadModpacks);
});

function loadModpacks() {
    const search = document.getElementById('modpackSearch').value;
    const filter = document.getElementById('modpackFilter').value;
    const source = document.getElementById('modpackSource').value;
    const category = document.getElementById('modpackCategory').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (filter !== 'all') params.append('filter', filter);
    if (source) params.append('source', source);
    if (category) params.append('category', category);
    
    fetch(`/api/extensions/minecraft-tools/modpacks?${params}`, {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        renderModpacks(data.modpacks);
    })
    .catch(console.error);
}

function loadCurrentModpack() {
    fetch('/api/extensions/minecraft-tools/modpacks/current', {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        const current = data.current;
        document.getElementById('currentModpack').innerHTML = current 
            ? `<strong>${current.name}</strong> ${current.version ? `(v${current.version})` : ''}`
            : 'No modpack installed';
    })
    .catch(console.error);
}

function loadCategories() {
    fetch('/api/extensions/minecraft-tools/modpacks/categories', {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        const select = document.getElementById('modpackCategory');
        data.categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat;
            option.textContent = cat.charAt(0).toUpperCase() + cat.slice(1);
            select.appendChild(option);
        });
    })
    .catch(console.error);
}

function loadModpackVersions(source, name) {
    if (!name || name.length < 2) return;
    
    fetch(`/api/extensions/minecraft-tools/modpacks/${encodeURIComponent(name)}/versions?source=${source}`, {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        const select = document.getElementById('installModpackVersion');
        select.innerHTML = '<option value="">Latest</option>';
        data.versions.forEach(v => {
            const option = document.createElement('option');
            option.value = v;
            option.textContent = v;
            select.appendChild(option);
        });
    })
    .catch(console.error);
}

function renderModpacks(modpacks) {
    const tbody = document.querySelector('#modpacksTable tbody');
    tbody.innerHTML = '';
    
    modpacks.forEach(modpack => {
        const row = document.createElement('tr');
        const isCurrent = modpack.current;
        row.innerHTML = `
            <td>${modpack.name}</td>
            <td>${modpack.version || 'N/A'}</td>
            <td><span class="badge bg-secondary">${modpack.source}</span></td>
            <td>${modpack.category || 'N/A'}</td>
            <td>
                <span class="badge ${modpack.installed ? 'bg-success' : 'bg-secondary'}">
                    ${modpack.installed ? 'Installed' : 'Available'}
                </span>
                ${isCurrent ? ' <span class="badge bg-primary">Active</span>' : ''}
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    ${modpack.installed && !isCurrent ? 
                        `<button class="btn btn-outline-primary" onclick="switchToModpack('${modpack.name}')">Switch</button>` : ''
                    }
                    ${modpack.installed ? 
                        `<button class="btn btn-outline-warning" onclick="updateModpack('${modpack.name}')">Update</button>
                         <button class="btn btn-outline-secondary" onclick="configureModpack('${modpack.name}')">Config</button>
                         <button class="btn btn-outline-info" onclick="viewModpackFiles('${modpack.name}')">Files</button>
                         <button class="btn btn-outline-danger" onclick="uninstallModpack('${modpack.name}')">Uninstall</button>` :
                        `<button class="btn btn-outline-success" onclick="installSpecificModpack('${modpack.name}', '${modpack.source}')">Install</button>`
                    }
                </div>
            </td>
        `;
        if (isCurrent) row.classList.add('table-primary');
        tbody.appendChild(row);
    });
}

function installModpack() {
    const form = document.getElementById('installModpackForm');
    const formData = new FormData(form);
    
    fetch('/api/extensions/minecraft-tools/modpacks', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
        },
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('installModpackModal')).hide();
            loadModpacks();
            loadCurrentModpack();
            showAlert('Modpack installation started', 'success');
        }
    })
    .catch(console.error);
}

function installSpecificModpack(name, source) {
    document.getElementById('installModpackSource').value = source;
    document.getElementById('installModpackName').value = name;
    loadModpackVersions(source, name);
    new bootstrap.Modal(document.getElementById('installModpackModal')).show();
}

function updateModpack(name) {
    if (!confirm(`Update ${name} to latest version?`)) return;
    
    fetch(`/api/extensions/minecraft-tools/modpacks/${name}/update`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'updating') {
            loadModpacks();
            loadCurrentModpack();
            showAlert('Modpack update started', 'success');
        }
    })
    .catch(console.error);
}

function uninstallModpack(name) {
    if (!confirm(`Uninstall ${name}? This will remove all modpack files.`)) return;
    
    fetch(`/api/extensions/minecraft-tools/modpacks/${name}`, {
        method: 'DELETE',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'uninstalled') {
            loadModpacks();
            loadCurrentModpack();
            showAlert('Modpack uninstalled', 'success');
        }
    })
    .catch(console.error);
}

function configureModpack(name) {
    window.location.href = `/admin/extensions/minecraft-tools/modpacks/${name}/config`;
}

function viewModpackFiles(name) {
    alert('File browser not yet implemented');
}

function populateSwitchModal() {
    fetch('/api/extensions/minecraft-tools/modpacks?filter=installed', {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        const select = document.getElementById('switchModpackSelect');
        select.innerHTML = '<option value="">Select modpack</option>';
        data.modpacks.forEach(m => {
            if (m.installed && !m.current) {
                const option = document.createElement('option');
                option.value = m.name;
                option.textContent = `${m.name}${m.version ? ' (v' + m.version + ')' : ''}`;
                select.appendChild(option);
            }
        });
        
        select.addEventListener('change', function() {
            loadModpackVersionsForSwitch(this.value);
        });
    })
    .catch(console.error);
}

function loadModpackVersionsForSwitch(name) {
    fetch(`/api/extensions/minecraft-tools/modpacks/${encodeURIComponent(name)}/versions`, {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        const select = document.getElementById('switchModpackVersionSelect');
        select.innerHTML = '<option value="">Default</option>';
        data.versions.forEach(v => {
            const option = document.createElement('option');
            option.value = v;
            option.textContent = v;
            select.appendChild(option);
        });
    })
    .catch(console.error);
}

function switchModpack() {
    const name = document.getElementById('switchModpackSelect').value;
    const version = document.getElementById('switchModpackVersionSelect').value;
    
    if (!name) return;
    
    if (!confirm('This will restart the server and replace files. Continue?')) return;
    
    fetch('/api/extensions/minecraft-tools/modpacks/switch', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ name, version: version || null })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'switching') {
            bootstrap.Modal.getInstance(document.getElementById('switchModpackModal')).hide();
            showAlert('Modpack switch initiated', 'success');
            setTimeout(() => {
                loadCurrentModpack();
                loadModpacks();
            }, 5000);
        }
    })
    .catch(console.error);
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