@extends('layouts.admin')

@section('title')
    Minecraft Tools — Settings
@endsection

@section('content-header')
    <h1>Minecraft Tools<small>Configure the plugin and mod portals used by the per-server pages</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Minecraft Tools</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Plugin Portal</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" onclick="loadSettings()"><i class="fa fa-refresh"></i></button>
                </div>
            </div>
            <div class="box-body">
                <p class="text-muted">
                    Search results shown on the per-server <strong>Plugins</strong> page are resolved through this provider.
                </p>
                <div class="form-group">
                    <label>Provider</label>
                    <select class="form-control" id="plugins-provider" style="width:240px">
                        <option value="modrinth">Modrinth</option>
                        <option value="hangar">Hangar</option>
                        <option value="spigot">SpigotMC</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Base URL <small class="text-muted">(leave empty for the default endpoint)</small></label>
                    <input type="text" class="form-control" id="plugins-base-url" placeholder="https://api.modrinth.com/v2/">
                </div>
                <div class="form-group">
                    <label>API Key <small class="text-muted">(optional)</small></label>
                    <input type="password" class="form-control" id="plugins-api-key" autocomplete="new-password" placeholder="••••••••••••">
                </div>
                <label class="checkbox-inline">
                    <input type="checkbox" id="plugins-enabled" checked> Portal enabled
                </label>
            </div>
            <div class="box-footer">
                <button type="button" class="btn btn-primary" onclick="saveSettings('plugins')">Save Plugin Portal</button>
                <span class="text-muted" id="plugins-status"></span>
            </div>
        </div>

        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Mod Portal</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" onclick="loadSettings()"><i class="fa fa-refresh"></i></button>
                </div>
            </div>
            <div class="box-body">
                <p class="text-muted">
                    Search results shown on the per-server <strong>Mods</strong> page are resolved through this provider.
                </p>
                <div class="form-group">
                    <label>Provider</label>
                    <select class="form-control" id="mods-provider" style="width:240px">
                        <option value="modrinth">Modrinth</option>
                        <option value="hangar">Hangar</option>
                        <option value="spigot">SpigotMC</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Base URL <small class="text-muted">(leave empty for the default endpoint)</small></label>
                    <input type="text" class="form-control" id="mods-base-url" placeholder="https://api.modrinth.com/v2/">
                </div>
                <div class="form-group">
                    <label>API Key <small class="text-muted">(optional)</small></label>
                    <input type="password" class="form-control" id="mods-api-key" autocomplete="new-password" placeholder="••••••••••••">
                </div>
                <label class="checkbox-inline">
                    <input type="checkbox" id="mods-enabled" checked> Portal enabled
                </label>
            </div>
            <div class="box-footer">
                <button type="button" class="btn btn-primary" onclick="saveSettings('mods')">Save Mod Portal</button>
                <span class="text-muted" id="mods-status"></span>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">About</h3>
            </div>
            <div class="box-body">
                <p>
                    <strong>Minecraft Tools</strong> manages Minecraft server files directly through Wings.
                </p>
                <p class="text-muted">
                    All file-based features (players, plugins, mods, versions, icon and config) now live on the
                    per-server pages in the user area, and operate on the actual server files.
                </p>
            </div>
        </div>
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Status</h3>
            </div>
            <div class="box-body">
                <dl class="dl-horizontal">
                    <dt>Panel servers</dt>
                    <dd id="status-servers">--</dd>
                </dl>
                <p class="text-muted small">
                    Note: this extension is tied to the wings node of each server. If the node is offline the per-server
                    pages will report connection errors.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var api = '/extensions/minecrafttools/api';

    function csrf() {
        var m = document.querySelector('meta[name="csrf-token"]') || document.querySelector('meta[name="_token"]');
        return m ? m.content : '';
    }

    function doFetch(url, options) {
        options = options || {};
        options.credentials = 'same-origin';
        options.headers = Object.assign({ 'Accept': 'application/json', 'Content-Type': 'application/json' }, options.headers || {});
        if (options.method) {
            options.headers['X-CSRF-TOKEN'] = csrf();
        }
        return fetch(url, options).then(function(r) { return r.json(); });
    }

    window.loadSettings = function() {
        doFetch(api + '/settings').then(function(d) {
            var cfg = d.config || { plugins: {}, mods: {} };
            ['plugins', 'mods'].forEach(function(kind) {
                var s = cfg[kind] || {};
                var provider = document.getElementById(kind + '-provider');
                var baseUrl = document.getElementById(kind + '-base-url');
                var apiKey = document.getElementById(kind + '-api-key');
                var enabled = document.getElementById(kind + '-enabled');
                if (provider) provider.value = s.provider || 'modrinth';
                if (baseUrl) baseUrl.value = s.base_url || '';
                if (apiKey) apiKey.value = s.api_key || '';
                if (enabled) enabled.checked = !!s.enabled;
            });
        });
    };

    window.saveSettings = function(kind) {
        var status = document.getElementById(kind + '-status');
        status.textContent = 'Saving...';

        var config = doFetch(api + '/settings').then(function(d) {
            var cfg = d.config || { plugins: {}, mods: {} };
            cfg[kind] = {
                provider: document.getElementById(kind + '-provider').value,
                base_url: document.getElementById(kind + '-base-url').value,
                api_key: document.getElementById(kind + '-api-key').value,
                enabled: document.getElementById(kind + '-enabled').checked
            };
            return cfg;
        });

        config.then(function(cfg) {
            return doFetch(api + '/settings', {
                method: 'PUT',
                body: JSON.stringify({ config: cfg })
            });
        }).then(function(d) {
            status.textContent = d.status === 'success' ? 'Saved.' : (d.message || 'Failed to save.');
            if (d.status === 'success') {
                document.getElementById(kind + '-api-key').value = (d.config || {})[kind].api_key || '';
            }
        }).catch(function() {
            status.textContent = 'Failed to save.';
        });
    };

    doFetch(api + '/status', {})
        .catch(function() { return { servers: '--' }; })
        .then(function(d) {
            var el = document.getElementById('status-servers');
            if (el) el.textContent = d && d.servers !== undefined ? d.servers : '--';
        });

    loadSettings();
})();
</script>
@endsection