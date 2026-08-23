@extends('blueprint::admin.layouts.default')

@section('content')
<div class="p-4">
    <h2>Game Version Management</h2>
    <div class="grid grid-cols-1 gap-4">
        <div class="border rounded p-4">
            <h3>Available Versions</h3>
            <p>List available Minecraft versions for installation</p>
            <button class="btn btn-primary mt-2">Check Versions</button>
        </div>
        <div class="border rounded p-4">
            <h3>Version Installation</h3>
            <p>Install specific Minecraft version to server</p>
            <button class="btn btn-primary mt-2">Install Version</button>
        </div>
    </div>
</div>
@endsection