@extends('admin.layouts.default')

@section('title', 'Plugins - Minecraft Tools')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Plugin Management</h1>
                <button type="button" class="btn btn-primary" id="installPluginBtn">
                    <i class="fas fa-plus"></i> Install Plugin
                </button>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="pluginSearch" placeholder="Search plugins...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="pluginSource">
                                <option value="spigot">Spigot</option>
                                <option value="modrinth">Modrinth</option>
                                <option value="curseforge">CurseForge</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="pluginFilter">
                                <option value="all">All</option>
                                <option value="installed">Installed</option>
                                <option value="available">Available</option>
                                <option value="enabled">Enabled</option>
                                <option value="disabled">Disabled</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="pluginsTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Version</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Plugins loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Install Plugin Modal -->
<div class="modal fade" id="installPluginModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Install Plugin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="installPluginForm">
                    <div class="mb-3">
                        <label class="form-label">Plugin Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Source</label>
                        <select class="form-select" name="source">
                            <option value="spigot">Spigot</option>
                            <option value="modrinth">Modrinth</option>
                            <option value="curseforge">CurseForge</option>
                            <option value="local">Local File</option>
                        </select>
                    </div>
                    <div class="mb-3" id="sourceIdField" style="display: none;">
                        <label class="form-label">Source ID / File</label>
                        <input type="text" class="form-control" name="source_id" placeholder="Plugin ID or upload file">
                        <input type="file" class="form-control mt-2" name="plugin_file" accept=".jar" style="display: none;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Version (optional)</label>
                        <input type="text" class="form-control" name="version" placeholder="Latest">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmInstallPlugin">Install</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadPlugins();
    
    document.getElementById('installPluginBtn').addEventListener('click', function() {
        new bootstrap.Modal(document.getElementById('installPluginModal')).show();
    });
    
    document.getElementById('pluginSource').addEventListener('change', function() {
        const sourceIdField = document.getElementById('sourceIdField');
        const fileInput = sourceIdField.querySelector('input[type="file"]');
        const textInput = sourceIdField.querySelector('input[type="text"]');
        
        if (this.value === 'local') {
            sourceIdField.style.display = 'block';
            fileInput.style.display = 'block';
            textInput.style.display = 'none';
        } else {
            sourceIdField.style.display = 'block';
            fileInput.style.display = 'none';
            textInput.style.display = 'block';
        }
    });
    
    document.getElementById('confirmInstallPlugin').addEventListener('click', installPlugin);
    document.getElementById('pluginSearch').addEventListener('input', debounce(loadPlugins, 300));
    document.getElementById('pluginFilter').addEventListener('change', loadPlugins);
    document.getElementById('pluginSource').addEventListener('change', loadPlugins);
});

function loadPlugins() {
    const search = document.getElementById('pluginSearch').value;
    const filter = document.getElementById('pluginFilter').value;
    const source = document.getElementById('pluginSource').value;
    
    fetch(`/api/extensions/minecraft-tools/plugins?search=${encodeURIComponent(search)}&filter=${filter}&source=${source}`, {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        renderPlugins(data.plugins);
    })
    .catch(console.error);
}

function renderPlugins(plugins) {
    const tbody = document.querySelector('#pluginsTable tbody');
    tbody.innerHTML = '';
    
    plugins.forEach(plugin => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${plugin.name}</td>
            <td>${plugin.version || 'N/A'}</td>
            <td><span class="badge bg-secondary">${plugin.source}</span></td>
            <td>
                <span class="badge ${plugin.enabled ? 'bg-success' : 'bg-danger'}">
                    ${plugin.enabled ? 'Enabled' : 'Disabled'}
                </span>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    ${plugin.enabled ? 
                        `<button class="btn btn-outline-danger" onclick="togglePlugin('${plugin.name}', false)">Disable</button>` :
                        `<button class="btn btn-outline-success" onclick="togglePlugin('${plugin.name}', true)">Enable</button>`
                    }
                    <button class="btn btn-outline-primary" onclick="configurePlugin('${plugin.name}')">Config</button>
                    <button class="btn btn-outline-danger" onclick="uninstallPlugin('${plugin.name}')">Uninstall</button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function installPlugin() {
    const form = document.getElementById('installPluginForm');
    const formData = new FormData(form);
    
    fetch('/api/extensions/minecraft-tools/plugins', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
        },
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('installPluginModal')).hide();
            loadPlugins();
            showAlert('Plugin installation started', 'success');
        }
    })
    .catch(console.error);
}

function togglePlugin(name, enable) {
    const endpoint = enable ? 'enable' : 'disable';
    fetch(`/api/extensions/minecraft-tools/plugins/${name}/${endpoint}`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' || data.status === 'enabled' || data.status === 'disabled') {
            loadPlugins();
            showAlert(`Plugin ${enable ? 'enabled' : 'disabled'}`, 'success');
        }
    })
    .catch(console.error);
}

function uninstallPlugin(name) {
    if (!confirm(`Are you sure you want to uninstall ${name}?`)) return;
    
    fetch(`/api/extensions/minecraft-tools/plugins/${name}`, {
        method: 'DELETE',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            loadPlugins();
            showAlert('Plugin uninstalled', 'success');
        }
    })
    .catch(console.error);
}

function configurePlugin(name) {
    window.location.href = `/admin/extensions/minecraft-tools/plugins/${name}/config`;
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