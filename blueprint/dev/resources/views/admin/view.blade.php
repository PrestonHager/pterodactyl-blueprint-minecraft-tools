@extends('admin.layouts.default')

@section('title')
    Minecraft Tools
@endsection

@section('content-header')
    <h1>Minecraft Tools<small>Server management utilities for Minecraft</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Extensions</li>
        <li class="active">Minecraft Tools</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <a href="/extensions/minecraft-tools/plugins" class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-puzzle-piece"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Plugins</span>
                <span class="info-box-number" id="plugin-count">--</span>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/extensions/minecraft-tools/versions" class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-code-fork"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Versions</span>
                <span class="info-box-number" id="version-count">--</span>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/extensions/minecraft-tools/players" class="info-box bg-yellow">
            <span class="info-box-icon"><i class="fa fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Players</span>
                <span class="info-box-number" id="player-count">--</span>
            </div>
        </a>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <a href="/extensions/minecraft-tools/modpacks" class="info-box bg-purple">
            <span class="info-box-icon"><i class="fa fa-cube"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Modpacks</span>
                <span class="info-box-number" id="modpack-count">--</span>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/extensions/minecraft-tools/config" class="info-box bg-red">
            <span class="info-box-icon"><i class="fa fa-cogs"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Config Editor</span>
                <span class="info-box-number" id="config-count">--</span>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/extensions/minecraft-tools/icon" class="info-box bg-teal">
            <span class="info-box-icon"><i class="fa fa-image"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Server Icon</span>
                <span class="info-box-number">Generator</span>
            </div>
        </a>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var api = '/extensions/minecraft-tools/api';
    var opts = { credentials: 'same-origin', headers: { 'Accept': 'application/json' } };

    function fetchCount(url, elId) {
        fetch(api + url, opts)
            .then(function(r) { return r.json(); })
            .then(function(d) {
                var el = document.getElementById(elId);
                if (!el) return;
                if (d.plugins !== undefined) el.textContent = d.plugins.length;
                else if (d.players !== undefined) el.textContent = d.total || 0;
                else if (d.modpacks !== undefined) el.textContent = d.modpacks.length;
                else if (d.files !== undefined) el.textContent = d.files.length;
                else el.textContent = '0';
            })
            .catch(function() {});
    }

    fetchCount('/plugins', 'plugin-count');
    fetchCount('/versions', 'version-count');
    fetchCount('/players', 'player-count');
    fetchCount('/modpacks', 'modpack-count');
    fetchCount('/config/files', 'config-count');
});
</script>
@endsection
