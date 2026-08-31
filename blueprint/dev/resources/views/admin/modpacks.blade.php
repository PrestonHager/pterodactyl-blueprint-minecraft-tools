@extends('admin.layouts.default')

@section('title')
    Minecraft Tools — Modpacks
@endsection

@section('content-header')
    <h1>Modpack Management<small>Install, configure, and manage modpacks</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="/admin/extensions/minecraft-tools">Minecraft Tools</a></li>
        <li class="active">Modpacks</li>
    </ol>
@endsection

@section('content')
<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#installed" data-toggle="tab">Installed</a></li>
        <li><a href="#search" data-toggle="tab">Search & Install</a></li>
    </ul>
    <div class="tab-content">
        {{-- INSTALLED TAB --}}
        <div class="tab-pane active" id="installed">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Installed Modpacks</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" id="refresh-modpacks"><i class="fa fa-refresh"></i></button>
                    </div>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Version</th>
                                <th>Source</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="modpacks-list">
                            <tr><td colspan="4" class="text-center text-muted">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- SEARCH TAB --}}
        <div class="tab-pane" id="search">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Search for Modpacks</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label>Source</label>
                        <select class="form-control" id="search-source" style="width:200px">
                            <option value="modrinth">Modrinth</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search-query" placeholder="Search modpacks...">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-primary" id="do-search"><i class="fa fa-search"></i> Search</button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="box" id="search-results-box" style="display:none">
                <div class="box-header with-border">
                    <h3 class="box-title">Search Results</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="search-results">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- VERSION MODAL --}}
<div class="modal fade" id="version-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Versions: <span id="version-modpack-name"></span></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Select Version</label>
                    <select class="form-control" id="version-select"></select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="switch-version">Switch Version</button>
            </div>
        </div>
    </div>
</div>

{{-- CONFIG MODAL --}}
<div class="modal fade" id="config-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Modpack Config: <span id="config-modpack-name"></span></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Configuration (JSON)</label>
                    <textarea class="form-control" id="config-textarea" rows="12" style="font-family:monospace"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-config">Save</button>
            </div>
        </div>
    </div>
</div>
<script>
(function() {
    var api = '/extensions/minecraft-tools/api/modpacks';
    var headers = { 'Accept': 'application/json', 'Content-Type': 'application/json' };

    function csrf() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.content : '';
    }

    function fetchJSON(url, opts) {
        opts = opts || {};
        opts.credentials = 'same-origin';
        opts.headers = Object.assign({}, headers, opts.headers || {});
        if (opts.method && opts.method !== 'GET') {
            opts.headers['X-CSRF-TOKEN'] = csrf();
        }
        return fetch(url, opts).then(function(r) { return r.json(); });
    }

    function escapeHtml(s) {
        var d = document.createElement('div');
        d.textContent = s || '';
        return d.innerHTML;
    }

    function loadModpacks() {
        fetchJSON(api).then(function(d) {
            var tbody = document.getElementById('modpacks-list');
            var modpacks = d.modpacks || [];
            if (!modpacks.length) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No modpacks installed. Use the Search tab to add modpacks.</td></tr>';
                return;
            }
            tbody.innerHTML = modpacks.map(function(m) {
                return '<tr>' +
                    '<td><strong>' + escapeHtml(m.name) + '</strong></td>' +
                    '<td>' + escapeHtml(m.version || '-') + '</td>' +
                    '<td>' + escapeHtml(m.source || 'local') + '</td>' +
                    '<td>' +
                        '<button class="btn btn-xs btn-info change-version" data-modpack="' + escapeHtml(m.name) + '">Versions</button> ' +
                        '<button class="btn btn-xs btn-info config-modpack" data-modpack="' + escapeHtml(m.name) + '">Config</button> ' +
                        '<button class="btn btn-xs btn-danger remove-modpack" data-modpack="' + escapeHtml(m.name) + '">Remove</button>' +
                    '</td></tr>';
            }).join('');
        });
    }

    document.getElementById('refresh-modpacks').addEventListener('click', loadModpacks);

    document.getElementById('modpacks-list').addEventListener('click', function(e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        var modpack = btn.dataset.modpack;

        if (btn.classList.contains('remove-modpack')) {
            if (!confirm('Remove modpack config for ' + modpack + '?')) return;
            fetchJSON(api + '/' + encodeURIComponent(modpack), { method: 'DELETE' })
                .then(function() { loadModpacks(); });
        }

        if (btn.classList.contains('change-version')) {
            document.getElementById('version-modpack-name').textContent = modpack;
            var sel = document.getElementById('version-select');
            sel.innerHTML = '<option>Loading...</option>';
            $('#version-modal').modal('show');
            fetchJSON(api + '/' + encodeURIComponent(modpack) + '/versions').then(function(d) {
                var versions = d.versions || [];
                if (!versions.length) {
                    sel.innerHTML = '<option>No versions available</option>';
                    return;
                }
                sel.innerHTML = versions.map(function(v) {
                    var id = v.version_number || v.id || v;
                    return '<option value="' + escapeHtml(id) + '">' + escapeHtml(id) + '</option>';
                }).join('');
            });
        }

        if (btn.classList.contains('config-modpack')) {
            document.getElementById('config-modpack-name').textContent = modpack;
            fetchJSON(api + '/' + encodeURIComponent(modpack) + '/config').then(function(d) {
                document.getElementById('config-textarea').value = JSON.stringify(d.config || {}, null, 2);
                $('#config-modal').modal('show');
            });
        }
    });

    document.getElementById('switch-version').addEventListener('click', function() {
        var modpack = document.getElementById('version-modpack-name').textContent;
        var version = document.getElementById('version-select').value;
        if (!version) { alert('Select a version'); return; }
        fetchJSON(api + '/' + encodeURIComponent(modpack) + '/switch-version', {
            method: 'POST',
            body: JSON.stringify({ version: version })
        }).then(function() { $('#version-modal').modal('hide'); loadModpacks(); });
    });

    document.getElementById('save-config').addEventListener('click', function() {
        var modpack = document.getElementById('config-modpack-name').textContent;
        var raw = document.getElementById('config-textarea').value;
        var config;
        try { config = JSON.parse(raw); } catch(e) { alert('Invalid JSON'); return; }
        fetchJSON(api + '/' + encodeURIComponent(modpack) + '/config', {
            method: 'PUT',
            body: JSON.stringify({ config: config })
        }).then(function() { $('#config-modal').modal('hide'); loadModpacks(); });
    });

    document.getElementById('do-search').addEventListener('click', function() {
        var q = document.getElementById('search-query').value.trim();
        var source = document.getElementById('search-source').value;
        if (!q) return;
        var tbody = document.getElementById('search-results');
        tbody.innerHTML = '<tr><td colspan="3" class="text-center">Searching...</td></tr>';
        document.getElementById('search-results-box').style.display = '';

        fetchJSON(api + '/search', {
            method: 'POST',
            body: JSON.stringify({ query: q, source: source })
        }).then(function(d) {
            var results = d.results || [];
            if (!results.length) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No results found.</td></tr>';
                return;
            }
            tbody.innerHTML = results.map(function(r) {
                return '<tr>' +
                    '<td><strong>' + escapeHtml(r.name || 'Unknown') + '</strong></td>' +
                    '<td>' + escapeHtml((r.description || '').substring(0, 120)) + '</td>' +
                    '<td><button class="btn btn-xs btn-primary install-modpack" data-id="' + escapeHtml(r.id || '') + '" data-name="' + escapeHtml(r.name || '') + '">Install</button></td>' +
                '</tr>';
            }).join('');
        });
    });

    document.getElementById('search-results').addEventListener('click', function(e) {
        var btn = e.target.closest('.install-modpack');
        if (!btn) return;
        var name = btn.dataset.name;
        btn.disabled = true;
        btn.textContent = 'Installing...';
        fetchJSON(api, {
            method: 'POST',
            body: JSON.stringify({ name: name, source: document.getElementById('search-source').value })
        }).then(function() {
            btn.textContent = 'Installed';
            btn.classList.replace('btn-primary', 'btn-success');
        });
    });

    document.getElementById('search-query').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') document.getElementById('do-search').click();
    });

    loadModpacks();
})();
</script>
@endsection
