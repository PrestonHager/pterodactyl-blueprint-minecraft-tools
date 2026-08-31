@extends('admin.layouts.default')

@section('title')
    Minecraft Tools — Server Icon
@endsection

@section('content-header')
    <h1>Server Icon<small>Generate, upload, and manage your server icon</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="/admin/extensions/minecraft-tools">Minecraft Tools</a></li>
        <li class="active">Server Icon</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Generate Icon</h3>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label>Text</label>
                    <input type="text" class="form-control" id="gen-text" value="MC">
                </div>
                <div class="form-group">
                    <label>Background Color</label>
                    <input type="color" class="form-control" id="gen-background" value="#2E86C1" style="height:40px">
                </div>
                <div class="form-group">
                    <label>Font Color</label>
                    <input type="color" class="form-control" id="gen-font-color" value="#FFFFFF" style="height:40px">
                </div>
                <div class="form-group">
                    <label>Font Size</label>
                    <input type="number" class="form-control" id="gen-font-size" value="24" min="6" max="50">
                </div>
                <button type="button" class="btn btn-primary" id="generate-icon"><i class="fa fa-magic"></i> Generate</button>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Preview</h3>
            </div>
            <div class="box-body text-center">
                <img id="icon-preview" class="img-thumbnail" style="max-width:128px;max-height:128px" alt="No icon yet">
                <p class="help-block">Generated icons are 64x64 PNG. Note: generation requires the GD extension on the server.</p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Upload Icon</h3>
            </div>
            <div class="box-body">
                <div class="input-group">
                    <input type="text" class="form-control" id="upload-url" placeholder="Paste image URL...">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-primary" id="upload-icon"><i class="fa fa-upload"></i> Upload</button>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Icon History</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" id="refresh-history"><i class="fa fa-refresh"></i></button>
                </div>
            </div>
            <div class="box-body">
                <div class="row" id="history-list">
                    <p class="text-center text-muted">Loading...</p>
                </div>
                <button type="button" class="btn btn-danger btn-xs" id="clear-history" style="margin-top:10px"><i class="fa fa-trash"></i> Clear History</button>
            </div>
        </div>
    </div>
</div>
<script>
(function() {
    var api = '/extensions/minecraft-tools/api/icon';
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

    function setPreview(dataUrl) {
        document.getElementById('icon-preview').src = dataUrl || '';
    }

    function loadCurrent() {
        fetchJSON(api).then(function(d) {
            if (d.icon) setPreview(d.icon);
        });
    }

    function loadHistory() {
        fetchJSON(api + '/history').then(function(d) {
            var rows = d.history || [];
            var list = document.getElementById('history-list');
            if (!rows.length) {
                list.innerHTML = '<p class="text-center text-muted">No icon history yet.</p>';
                return;
            }
            list.innerHTML = rows.map(function(r) {
                return '<div class="col-xs-3" style="margin-bottom:10px">' +
                    '<img src="' + escapeHtml(r.url || '') + '" class="img-thumbnail" style="width:100%">' +
                    '</div>';
            }).join('');
        });
    }

    document.getElementById('generate-icon').addEventListener('click', function() {
        fetchJSON(api + '/generate', {
            method: 'POST',
            body: JSON.stringify({
                text: document.getElementById('gen-text').value.trim() || 'MC',
                background: document.getElementById('gen-background').value,
                font_color: document.getElementById('gen-font-color').value,
                font_size: parseInt(document.getElementById('gen-font-size').value, 10) || 24
            })
        }).then(function(d) {
            if (d.icon) {
                setPreview(d.icon);
            } else {
                alert(d.message || 'Icon generation is not available on this server (GD extension missing). Use the Upload tab instead.');
            }
        });
    });

    document.getElementById('upload-icon').addEventListener('click', function() {
        var url = document.getElementById('upload-url').value.trim();
        if (!url) { alert('Enter an image URL'); return; }
        fetchJSON(api + '/upload', {
            method: 'POST',
            body: JSON.stringify({ image: url })
        }).then(function(d) {
            if (d.icon) setPreview(d.icon.url || d.icon);
            document.getElementById('upload-url').value = '';
            loadHistory();
        });
    });

    document.getElementById('refresh-history').addEventListener('click', loadHistory);

    document.getElementById('clear-history').addEventListener('click', function() {
        if (!confirm('Clear icon history?')) return;
        fetchJSON(api, { method: 'DELETE' }).then(function() { loadHistory(); });
    });

    loadCurrent();
    loadHistory();
})();
</script>
@endsection
