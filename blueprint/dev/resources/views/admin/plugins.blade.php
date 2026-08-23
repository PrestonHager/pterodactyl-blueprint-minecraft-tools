@extends('blueprint::admin.layouts.default')

@section('content')
<div class="p-4">
    <h2>Plugin Management</h2>
    <div class="grid grid-cols-1 gap-4">
        <div class="border rounded p-4">
            <h3>Installed Plugins</h3>
            <p>List and manage installed server plugins</p>
            <button class="btn btn-primary mt-2">View Plugins</button>
        </div>
        <div class="border rounded p-4">
            <h3>Plugin Upload</h3>
            <p>Upload new plugins to the server</p>
            <button class="btn btn-primary mt-2">Upload Plugin</button>
        </div>
    </div>
</div>
@endsection