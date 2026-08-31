@extends('admin.layouts.default')

@section('title')
    Minecraft Tools — Versions
@endsection

@section('content-header')
    <h1>Version Management<small>Browse, install, and switch Minecraft server versions</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="/admin/extensions/minecraft-tools">Minecraft Tools</a></li>
        <li class="active">Versions</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Server Type</h3>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <select class="form-control" id="server-type">
                        <option value="paper">Paper</option>
                        <option value="spigot">Spigot</option>
                        <option value="purpur">Purpur</option>
                        <option value="fabric">Fabric</option>
                    </select>
                </div>
                <button type="button" class="btn btn-primary btn-block" id="load-versions"><i class="fa fa-refresh"></i> Load Versions</button>
            </div>
        </div>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Current Version</h3>
            </div>
            <div class="box-body">
                <p id="current-version" class="text-muted">Loading...</p>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Available Versions</h3>
            </div>
            <div class="box-body table-responsive no-padding" style="max-height:500px;overflow-y:auto">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Version</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="versions-list">
                        <tr><td colspan="2" class="text-center text-muted">Select a server type and click Load Versions</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="box" id="builds-box" style="display:none">
            <div class="box-header with-border">
                <h3 class="box-title">Builds for <span id="builds-version"></span></h3>
            </div>
            <div class="box-body table-responsive no-padding" style="max-height:400px;overflow-y:auto">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Build</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="builds-list">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
(function() {
    var api = '/extensions/minecraft-tools/api/versions';
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

    function loadCurrent() {
        fetchJSON(api + '/current').then(function(d) {
            document.getElementById('current-version').innerHTML = d.current
                ? '<strong>' + escapeHtml(d.current) + '</strong>'
                : '<span class="text-muted">No version tracking available (managed through Wings)</span>';
        });
    }

    function loadVersions() {
        var type = document.getElementById('server-type').value;
        var tbody = document.getElementById('versions-list');
        tbody.innerHTML = '<tr><td colspan="2" class="text-center">Loading...</td></tr>';

        fetchJSON(api + '/available?type=' + encodeURIComponent(type)).then(function(d) {
            var versions = d.versions || [];
            if (!versions.length) {
                tbody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">No versions available.</td></tr>';
                return;
            }
            tbody.innerHTML = versions.slice().reverse().map(function(v) {
                return '<tr>' +
                    '<td><strong>' + escapeHtml(v) + '</strong></td>' +
                    '<td><button class="btn btn-xs btn-info show-builds" data-version="' + escapeHtml(v) + '">View Builds</button> ' +
                        '<button class="btn btn-xs btn-primary install-version" data-version="' + escapeHtml(v) + '">Install</button></td>' +
                '</tr>';
            }).join('');
        });
    }

    document.getElementById('load-versions').addEventListener('click', loadVersions);

    document.getElementById('versions-list').addEventListener('click', function(e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        var version = btn.dataset.version;
        var type = document.getElementById('server-type').value;

        if (btn.classList.contains('show-builds')) {
            document.getElementById('builds-version').textContent = version;
            document.getElementById('builds-box').style.display = '';
            var tbody = document.getElementById('builds-list');
            tbody.innerHTML = '<tr><td colspan="2" class="text-center">Loading...</td></tr>';
            fetchJSON(api + '/builds/' + encodeURIComponent(version) + '?type=' + encodeURIComponent(type))
                .then(function(d) {
                    var builds = d.builds || [];
                    if (!builds.length) {
                        tbody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">No builds found.</td></tr>';
                        return;
                    }
                    tbody.innerHTML = builds.slice().reverse().map(function(b) {
                        var buildNum = b.build || b;
                        return '<tr>' +
                            '<td>Build ' + escapeHtml(String(buildNum)) + '</td>' +
                            '<td><button class="btn btn-xs btn-primary install-build" data-version="' + escapeHtml(version) + '" data-build="' + escapeHtml(String(buildNum)) + '">Install</button></td>' +
                        '</tr>';
                    }).join('');
                });
        }

        if (btn.classList.contains('install-version')) {
            if (!confirm('Install version ' + version + '?')) return;
            btn.disabled = true;
            fetchJSON(api, {
                method: 'POST',
                body: JSON.stringify({ version: version, type: type })
            }).then(function() {
                btn.textContent = 'Requested';
                btn.classList.replace('btn-primary', 'btn-success');
            });
        }
    });

    document.getElementById('builds-list').addEventListener('click', function(e) {
        var btn = e.target.closest('.install-build');
        if (!btn) return;
        var version = btn.dataset.version;
        var type = document.getElementById('server-type').value;
        if (!confirm('Install ' + type + ' ' + version + ' build ' + btn.dataset.build + '?')) return;
        btn.disabled = true;
        fetchJSON(api, {
            method: 'POST',
            body: JSON.stringify({ version: version, type: type })
        }).then(function() {
            btn.textContent = 'Requested';
            btn.classList.replace('btn-primary', 'btn-success');
        });
    });

    loadCurrent();
})();
</script>
@endsection
