@extends('admin.layouts.default')

@section('title', 'Versions - Minecraft Tools')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Game Version Management</h1>
                <button type="button" class="btn btn-primary" id="installVersionBtn">
                    <i class="fas fa-download"></i> Install Version
                </button>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Current Version</h5>
                        </div>
                        <div class="card-body">
                            <div id="currentVersion">Loading...</div>
                            <button type="button" class="btn btn-outline-primary mt-2" id="switchVersionBtn">
                                <i class="fas fa-exchange-alt"></i> Switch Version
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Check for Updates</h5>
                        </div>
                        <div class="card-body">
                            <button type="button" class="btn btn-primary" id="checkUpdatesBtn">
                                <i class="fas fa-sync-alt"></i> Check Updates
                            </button>
                            <div id="updatesInfo" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="versionSearch" placeholder="Search versions...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="versionType">
                                <option value="paper">Paper</option>
                                <option value="spigot">Spigot</option>
                                <option value="purpur">Purpur</option>
                                <option value="vanilla">Vanilla</option>
                                <option value="fabric">Fabric</option>
                                <option value="forge">Forge</option>
                                <option value="quilt">Quilt</option>
                                <option value="neoforge">NeoForge</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="versionFilter">
                                <option value="all">All</option>
                                <option value="installed">Installed</option>
                                <option value="available">Available</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="versionsTable">
                            <thead>
                                <tr>
                                    <th>Version</th>
                                    <th>Type</th>
                                    <th>Build</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Versions loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Install Version Modal -->
<div class="modal fade" id="installVersionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Install Version</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="installVersionForm">
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="type" id="installVersionType">
                            <option value="paper">Paper</option>
                            <option value="spigot">Spigot</option>
                            <option value="purpur">Purpur</option>
                            <option value="vanilla">Vanilla</option>
                            <option value="fabric">Fabric</option>
                            <option value="forge">Forge</option>
                            <option value="quilt">Quilt</option>
                            <option value="neoforge">NeoForge</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Version</label>
                        <select class="form-select" name="version" id="installVersionSelect" required>
                            <option value="">Select a type first</option>
                        </select>
                    </div>
                    <div class="mb-3" id="buildField" style="display: none;">
                        <label class="form-label">Build (optional)</label>
                        <select class="form-select" name="build" id="installBuildSelect">
                            <option value="">Latest</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmInstallVersion">Install</button>
            </div>
        </div>
    </div>
</div>

<!-- Switch Version Modal -->
<div class="modal fade" id="switchVersionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Switch Version</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Select the version to switch to:</p>
                <div class="mb-3">
                    <label class="form-label">Version</label>
                    <select class="form-select" id="switchVersionSelect" required>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <select class="form-select" id="switchTypeSelect" required>
                        <option value="paper">Paper</option>
                        <option value="spigot">Spigot</option>
                        <option value="purpur">Purpur</option>
                        <option value="vanilla">Vanilla</option>
                        <option value="fabric">Fabric</option>
                        <option value="forge">Forge</option>
                        <option value="quilt">Quilt</option>
                        <option value="neoforge">NeoForge</option>
                    </select>
                </div>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Switching versions will restart the server and may cause data loss if not properly backed up.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmSwitchVersion">Switch Version</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadCurrentVersion();
    loadVersions();
    loadAvailableVersions();
    
    document.getElementById('installVersionBtn').addEventListener('click', function() {
        new bootstrap.Modal(document.getElementById('installVersionModal')).show();
    });
    
    document.getElementById('switchVersionBtn').addEventListener('click', function() {
        populateSwitchModal();
        new bootstrap.Modal(document.getElementById('switchVersionModal')).show();
    });
    
    document.getElementById('checkUpdatesBtn').addEventListener('click', checkUpdates);
    document.getElementById('installVersionType').addEventListener('change', loadAvailableVersions);
    document.getElementById('installVersionSelect').addEventListener('change', loadBuilds);
    document.getElementById('confirmInstallVersion').addEventListener('click', installVersion);
    document.getElementById('confirmSwitchVersion').addEventListener('click', switchVersion);
    document.getElementById('versionSearch').addEventListener('input', debounce(loadVersions, 300));
    document.getElementById('versionFilter').addEventListener('change', loadVersions);
    document.getElementById('versionType').addEventListener('change', loadVersions);
});

function loadCurrentVersion() {
    fetch('/api/extensions/minecraft-tools/versions/current', {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('currentVersion').innerHTML = `
            <strong>${data.current?.version || 'Unknown'}</strong> (${data.current?.type || 'Unknown'})
            ${data.current?.build ? `<br><small>Build: ${data.current.build}</small>` : ''}
        `;
    })
    .catch(console.error);
}

function loadVersions() {
    const search = document.getElementById('versionSearch').value;
    const filter = document.getElementById('versionFilter').value;
    const type = document.getElementById('versionType').value;
    
    fetch(`/api/extensions/minecraft-tools/versions?search=${encodeURIComponent(search)}&filter=${filter}&type=${type}`, {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        renderVersions(data.versions);
    })
    .catch(console.error);
}

function loadAvailableVersions() {
    const type = document.getElementById('installVersionType').value;
    
    fetch(`/api/extensions/minecraft-tools/versions/available?type=${type}`, {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        const select = document.getElementById('installVersionSelect');
        select.innerHTML = '<option value="">Select version</option>';
        data.versions.forEach(v => {
            const option = document.createElement('option');
            option.value = v;
            option.textContent = v;
            select.appendChild(option);
        });
    })
    .catch(console.error);
}

function loadBuilds() {
    const version = document.getElementById('installVersionSelect').value;
    const type = document.getElementById('installVersionType').value;
    const buildField = document.getElementById('buildField');
    const buildSelect = document.getElementById('installBuildSelect');
    
    if (!version) {
        buildField.style.display = 'none';
        return;
    }
    
    fetch(`/api/extensions/minecraft-tools/versions/builds/${version}?type=${type}`, {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        buildSelect.innerHTML = '<option value="">Latest</option>';
        data.builds.forEach(b => {
            const option = document.createElement('option');
            option.value = b;
            option.textContent = b;
            buildSelect.appendChild(option);
        });
        buildField.style.display = 'block';
    })
    .catch(() => {
        buildField.style.display = 'none';
    });
}

function renderVersions(versions) {
    const tbody = document.querySelector('#versionsTable tbody');
    tbody.innerHTML = '';
    
    versions.forEach(version => {
        const row = document.createElement('tr');
        const isCurrent = version.current;
        row.innerHTML = `
            <td>${version.version}</td>
            <td><span class="badge bg-secondary">${version.type}</span></td>
            <td>${version.build || 'Latest'}</td>
            <td>
                <span class="badge ${version.installed ? 'bg-success' : 'bg-secondary'}">
                    ${version.installed ? 'Installed' : 'Available'}
                </span>
                ${isCurrent ? ' <span class="badge bg-primary">Current</span>' : ''}
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    ${version.installed && !isCurrent ? 
                        `<button class="btn btn-outline-primary" onclick="switchToVersion('${version.version}', '${version.type}')">Switch</button>` : ''
                    }
                    ${version.installed ? 
                        `<button class="btn btn-outline-warning" onclick="reinstallVersion('${version.version}')">Reinstall</button>
                         <button class="btn btn-outline-danger" onclick="removeVersion('${version.version}')">Remove</button>` :
                        `<button class="btn btn-outline-success" onclick="installSpecificVersion('${version.version}', '${version.type}')">Install</button>`
                    }
                </div>
            </td>
        `;
        if (isCurrent) row.classList.add('table-primary');
        tbody.appendChild(row);
    });
}

function installVersion() {
    const form = document.getElementById('installVersionForm');
    const formData = new FormData(form);
    
    fetch('/api/extensions/minecraft-tools/versions', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
        },
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'installing') {
            bootstrap.Modal.getInstance(document.getElementById('installVersionModal')).hide();
            loadVersions();
            showAlert('Version installation started', 'success');
        }
    })
    .catch(console.error);
}

function installSpecificVersion(version, type) {
    document.getElementById('installVersionType').value = type;
    loadAvailableVersions().then(() => {
        document.getElementById('installVersionSelect').value = version;
        loadBuilds();
        new bootstrap.Modal(document.getElementById('installVersionModal')).show();
    });
}

function reinstallVersion(version) {
    if (!confirm(`Reinstall ${version}? This will overwrite existing files.`)) return;
    
    fetch(`/api/extensions/minecraft-tools/versions/${version}/reinstall`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'reinstalling') {
            loadVersions();
            showAlert('Version reinstall started', 'success');
        }
    })
    .catch(console.error);
}

function removeVersion(version) {
    if (!confirm(`Remove ${version}?`)) return;
    
    fetch(`/api/extensions/minecraft-tools/versions/${version}`, {
        method: 'DELETE',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'removed') {
            loadVersions();
            showAlert('Version removed', 'success');
        }
    })
    .catch(console.error);
}

function populateSwitchModal() {
    fetch('/api/extensions/minecraft-tools/versions?filter=installed', {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        const select = document.getElementById('switchVersionSelect');
        select.innerHTML = '<option value="">Select version</option>';
        data.versions.forEach(v => {
            if (v.installed && !v.current) {
                const option = document.createElement('option');
                option.value = v.version;
                option.textContent = `${v.version} (${v.type}${v.build ? ' - Build ' + v.build : ''})`;
                option.dataset.type = v.type;
                select.appendChild(option);
            }
        });
    })
    .catch(console.error);
}

function switchVersion() {
    const version = document.getElementById('switchVersionSelect').value;
    const type = document.getElementById('switchTypeSelect').value;
    
    if (!version) return;
    
    if (!confirm('This will restart the server. Continue?')) return;
    
    fetch('/api/extensions/minecraft-tools/versions/switch', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ version, type })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'switching') {
            bootstrap.Modal.getInstance(document.getElementById('switchVersionModal')).hide();
            showAlert('Version switch initiated', 'success');
            setTimeout(loadCurrentVersion, 5000);
        }
    })
    .catch(console.error);
}

function checkUpdates() {
    const btn = document.getElementById('checkUpdatesBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
    
    fetch('/api/extensions/minecraft-tools/versions/check-updates', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync-alt"></i> Check Updates';
        
        const infoDiv = document.getElementById('updatesInfo');
        if (data.updates && data.updates.length > 0) {
            infoDiv.innerHTML = '<div class="alert alert-info"><strong>Updates available:</strong><ul class="mb-0">' +
                data.updates.map(u => `<li>${u.version} (${u.type})</li>`).join('') + '</ul></div>';
        } else {
            infoDiv.innerHTML = '<div class="alert alert-success">No updates available</div>';
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync-alt"></i> Check Updates';
    });
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