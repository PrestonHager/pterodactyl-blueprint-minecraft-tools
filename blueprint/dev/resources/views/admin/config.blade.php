@extends('blueprint::admin.layouts.default')

@section('content')
<div class="p-4">
    <h2>Config Editor</h2>
    <div class="grid grid-cols-1 gap-4">
        <div class="border rounded p-4">
            <h3>Extension Config</h3>
            <p>Manage extension configuration settings</p>
            <button class="btn btn-primary mt-2">Edit Config</button>
        </div>
        <div class="border rounded p-4">
            <h3>Server Configs</h3>
            <p>View and edit server configuration files</p>
            <button class="btn btn-primary mt-2">Manage Server Configs</button>
        </div>
    </div>
</div>
@endsection