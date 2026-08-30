@extends('admin.layouts.default')

@section('title', 'Minecraft Tools')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Minecraft Tools</h1>
                <div>
                    <a href="{{ route('admin.extensions.minecraft-tools.plugins') }}" class="btn btn-primary me-2">
                        <i class="fas fa-plug"></i> Plugins
                    </a>
                    <a href="{{ route('admin.extensions.minecraft-tools.versions') }}" class="btn btn-primary me-2">
                        <i class="fas fa-code-branch"></i> Versions
                    </a>
                    <a href="{{ route('admin.extensions.minecraft-tools.players') }}" class="btn btn-primary me-2">
                        <i class="fas fa-users"></i> Players
                    </a>
                    <a href="{{ route('admin.extensions.minecraft-tools.modpacks') }}" class="btn btn-primary me-2">
                        <i class="fas fa-cube"></i> Modpacks
                    </a>
                    <a href="{{ route('admin.extensions.minecraft-tools.config') }}" class="btn btn-primary me-2">
                        <i class="fas fa-cog"></i> Config
                    </a>
                    <a href="{{ route('admin.extensions.minecraft-tools.icon') }}" class="btn btn-primary">
                        <i class="fas fa-image"></i> Icon
                    </a>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Plugin Management</h5>
                        </div>
                        <div class="card-body">
                            <p>Manage server plugins from Spigot, Modrinth, and CurseForge</p>
                            <a href="{{ route('admin.extensions.minecraft-tools.plugins') }}" class="btn btn-outline-primary">Manage Plugins</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Game Versions</h5>
                        </div>
                        <div class="card-body">
                            <p>Install and switch between Minecraft versions (Vanilla, Paper, Fabric, Forge, etc.)</p>
                            <a href="{{ route('admin.extensions.minecraft-tools.versions') }}" class="btn btn-outline-primary">Manage Versions</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Player Management</h5>
                        </div>
                        <div class="card-body">
                            <p>Manage players, whitelist, bans, ops, and view player data</p>
                            <a href="{{ route('admin.extensions.minecraft-tools.players') }}" class="btn btn-outline-primary">Manage Players</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Modpack Management</h5>
                        </div>
                        <div class="card-body">
                            <p>Install and manage modpacks from Modrinth, CurseForge, Technic, and FTB</p>
                            <a href="{{ route('admin.extensions.minecraft-tools.modpacks') }}" class="btn btn-outline-primary">Manage Modpacks</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Config Editor</h5>
                        </div>
                        <div class="card-body">
                            <p>Edit server configuration files with syntax highlighting and validation</p>
                            <a href="{{ route('admin.extensions.minecraft-tools.config') }}" class="btn btn-outline-primary">Edit Configs</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Server Icon</h5>
                        </div>
                        <div class="card-body">
                            <p>Upload, generate, or choose from templates for your server icon</p>
                            <a href="{{ route('admin.extensions.minecraft-tools.icon') }}" class="btn btn-outline-primary">Manage Icon</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection