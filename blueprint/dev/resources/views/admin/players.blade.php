@extends('blueprint::admin.layouts.default')

@section('content')
<div class="p-4">
    <h2>Player & Whitelist Management</h2>
    <div class="grid grid-cols-1 gap-4">
        <div class="border rounded p-4">
            <h3>Whitelist Management</h3>
            <p>Manage player whitelist for the server</p>
            <button class="btn btn-primary mt-2">Manage Whitelist</button>
        </div>
        <div class="border rounded p-4">
            <h3>Player Management</h3>
            <p>Manage player permissions and settings</p>
            <button class="btn btn-primary mt-2">Manage Players</button>
        </div>
    </div>
</div>
@endsection