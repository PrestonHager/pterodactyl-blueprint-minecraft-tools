@extends('admin.layouts.default')

@section('title')
    Minecraft Tools — Plugins
@endsection

@section('content-header')
    <h1>Plugin Management<small>Install, configure, and manage server plugins</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="/admin/extensions/minecraft-tools">Minecraft Tools</a></li>
        <li class="active">Plugins</li>
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
                    <h3 class="box-title">Installed Plugins</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" id="refresh-plugins"><i class="fa fa-refresh"></i></button>
                    </div>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Version</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="plugins-list">
                            <tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- SEARCH TAB --}}
        <div class="tab-pane" id="search">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Search for Plugins</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label>Source</label>
                        <select class="form-control" id="search-source" style="width:200px">
                            <option value="spigot">SpigotMC</option>
                            <option value="modrinth">Modrinth</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search-query" placeholder="Search plugins...">
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

{{-- CONFIG MODAL --}}
<div class="modal fade" id="config-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Plugin Config: <span id="config-plugin-name"></span></h4>
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
    var api = '/extensions/minecraft-tools/api/plugins';
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

    function loadPlugins() {
        fetchJSON(api).then(function(d) {
            var tbody = document.getElementById('plugins-list');
            var plugins = d.plugins || [];
            if (!plugins.length) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No plugins installed. Use the Search tab to add plugins.</td></tr>';
                return;
            }
            tbody.innerHTML = plugins.map(function(p) {
                var status = p.enabled
                    ? '<span class="label label-success">Enabled</span>'
                    : '<span class="label label-default">Disabled</span>';
                return '<tr>' +
                    '<td><strong>' + escapeHtml(p.name) + '</strong></td>' +
                    '<td>' + escapeHtml(p.version || '-') + '</td>' +
                    '<td>' + escapeHtml(p.source || 'local') + '</td>' +
                    '<td>' + status + '</td>' +
                    '<td>' +
                        (p.enabled
                            ? '<button class="btn btn-xs btn-warning toggle-plugin" data-plugin="' + escapeHtml(p.name) + '" data-action="disable">Disable</button> '
                            : '<button class="btn btn-xs btn-success toggle-plugin" data-plugin="' + escapeHtml(p.name) + '" data-action="enable">Enable</button> ') +
                        '<button class="btn btn-xs btn-info config-plugin" data-plugin="' + escapeHtml(p.name) + '">Config</button> ' +
                        '<button class="btn btn-xs btn-danger remove-plugin" data-plugin="' + escapeHtml(p.name) + '">Remove</button>' +
                    '</td></tr>';
            }).join('');
        });
    }

    document.getElementById('refresh-plugins').addEventListener('click', loadPlugins);

    document.getElementById('plugins-list').addEventListener('click', function(e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        var plugin = btn.dataset.plugin;

        if (btn.classList.contains('toggle-plugin')) {
            var action = btn.dataset.action;
            fetchJSON(api + '/' + encodeURIComponent(plugin) + '/' + action, { method: 'POST' })
                .then(function() { loadPlugins(); });
        }

        if (btn.classList.contains('remove-plugin')) {
            if (!confirm('Remove plugin config for ' + plugin + '?')) return;
            fetchJSON(api + '/' + encodeURIComponent(plugin), { method: 'DELETE' })
                .then(function() { loadPlugins(); });
        }

        if (btn.classList.contains('config-plugin')) {
            document.getElementById('config-plugin-name').textContent = plugin;
            fetchJSON(api + '/' + encodeURIComponent(plugin) + '/config').then(function(d) {
                document.getElementById('config-textarea').value = JSON.stringify(d.config || {}, null, 2);
                $('#config-modal').modal('show');
            });
        }
    });

    document.getElementById('save-config').addEventListener('click', function() {
        var plugin = document.getElementById('config-plugin-name').textContent;
        var raw = document.getElementById('config-textarea').value;
        var config;
        try { config = JSON.parse(raw); } catch(e) { alert('Invalid JSON'); return; }
        fetchJSON(api + '/' + encodeURIComponent(plugin) + '/config', {
            method: 'PUT',
            body: JSON.stringify({ config: config })
        }).then(function() { $('#config-modal').modal('hide'); loadPlugins(); });
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
                var id = r.id || r.name || '';
                var name = r.name || r.title || 'Unknown';
                var desc = r.tag || r.description || '';
                return '<tr>' +
                    '<td><strong>' + escapeHtml(name) + '</strong></td>' +
                    '<td>' + escapeHtml(desc) + '</td>' +
                    '<td><button class="btn btn-xs btn-primary install-plugin" data-id="' + escapeHtml(id) + '" data-name="' + escapeHtml(name) + '">Install</button></td>' +
                '</tr>';
            }).join('');
        });
    });

    document.getElementById('search-results').addEventListener('click', function(e) {
        var btn = e.target.closest('.install-plugin');
        if (!btn) return;
        var name = btn.dataset.name;
        btn.disabled = true;
        btn.textContent = 'Installing...';
        fetchJSON(api + '/' + encodeURIComponent(name) + '/install', { method: 'POST' })
            .then(function() {
                btn.textContent = 'Installed';
                btn.classList.replace('btn-primary', 'btn-success');
            });
    });

    document.getElementById('search-query').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') document.getElementById('do-search').click();
    });

    loadPlugins();
})();
</script>
@endsection
