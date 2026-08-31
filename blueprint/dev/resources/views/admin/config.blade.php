@extends('admin.layouts.default')

@section('title')
    Minecraft Tools — Config Editor
@endsection

@section('content-header')
    <h1>Config Editor<small>Edit server configuration files and manage backups</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="/admin/extensions/minecraft-tools">Minecraft Tools</a></li>
        <li class="active">Config Editor</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Files</h3>
            </div>
            <div class="box-body no-padding">
                <ul class="nav nav-pills nav-stacked" id="file-list">
                    <li class="text-center text-muted">Loading...</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Editor: <span id="current-file">select a file</span></h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-sm btn-success" id="save-file"><i class="fa fa-save"></i> Save</button>
                    <button type="button" class="btn btn-sm btn-info" id="backup-file"><i class="fa fa-history"></i> Backup</button>
                </div>
            </div>
            <div class="box-body">
                <textarea class="form-control" id="file-content" rows="22" style="font-family:monospace" placeholder="Select a file from the left to begin editing..."></textarea>
                <p class="help-block">Saving changes requires Wings integration on the game server. Backups are stored in the panel database.</p>
            </div>
        </div>
    </div>
</div>
<script>
(function() {
    var api = '/extensions/minecraft-tools/api/config';
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

    function loadFiles() {
        fetchJSON(api + '/files').then(function(d) {
            var files = d.files || [];
            var ul = document.getElementById('file-list');
            if (!files.length) {
                ul.innerHTML = '<li class="text-center text-muted">No files</li>';
                return;
            }
            ul.innerHTML = files.map(function(f) {
                return '<li><a href="#" class="file-link" data-file="' + escapeHtml(f) + '"><i class="fa fa-file-code-o"></i> ' + escapeHtml(f) + '</a></li>';
            }).join('');
        });
    }

    document.getElementById('file-list').addEventListener('click', function(e) {
        e.preventDefault();
        var link = e.target.closest('.file-link');
        if (!link) return;
        var file = link.dataset.file;
        document.getElementById('current-file').textContent = file;
        document.querySelectorAll('.file-link').forEach(function(l) { l.parentElement.classList.remove('active'); });
        link.parentElement.classList.add('active');

        fetchJSON(api + '/' + encodeURIComponent(file) + '/raw').then(function(d) {
            document.getElementById('file-content').value = d.content || d.raw || '';
        });
    });

    document.getElementById('save-file').addEventListener('click', function() {
        var file = document.getElementById('current-file').textContent;
        if (file === 'select a file') { alert('Select a file first'); return; }
        var content = document.getElementById('file-content').value;
        fetchJSON(api + '/' + encodeURIComponent(file) + '/raw', {
            method: 'PUT',
            body: JSON.stringify({ content: content })
        }).then(function(d) {
            alert(d.message || 'Save request recorded. Applying changes on the game server requires Wings integration.');
        });
    });

    document.getElementById('backup-file').addEventListener('click', function() {
        var file = document.getElementById('current-file').textContent;
        if (file === 'select a file') { alert('Select a file first'); return; }
        var content = document.getElementById('file-content').value;
        fetchJSON(api + '/' + encodeURIComponent(file) + '/backup', {
            method: 'POST',
            body: JSON.stringify({ content: content })
        }).then(function(d) {
            alert('Backup stored (id ' + (d.backup && d.backup.id || '') + ').');
        });
    });

    loadFiles();
})();
</script>
@endsection
