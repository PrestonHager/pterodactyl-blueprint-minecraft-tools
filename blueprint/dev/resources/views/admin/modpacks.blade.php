@extends('blueprint::admin.layouts.default')

@section('content')
<div class="p-4">
    <h2>Modpack Management</h2>
    <div class="grid grid-cols-1 gap-4">
        <div class="border rounded p-4">
            <h3>Modpack Library</h3>
            <p>Browse and manage available modpacks</p>
            <button class="btn btn-primary mt-2">Browse Modpacks</button>
        </div>
        <div class="border rounded p-4">
            <h3>Modpack Installation</h3>
            <p>Install modpacks to server instances</p>
            <button class="btn btn-primary mt-2">Install Modpack</button>
        </div>
    </div>
</div>
@endsection