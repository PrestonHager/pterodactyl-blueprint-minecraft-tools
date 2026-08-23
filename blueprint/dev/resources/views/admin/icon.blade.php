@extends('blueprint::admin.layouts.default')

@section('content')
<div class="p-4">
    <h2>Server Icon Editor</h2>
    <div class="grid grid-cols-1 gap-4">
        <div class="border rounded p-4">
            <h3>Current Icon</h3>
            <p>View and manage the server's current icon</p>
            <img src="/placeholder-icon.png" alt="Server Icon" class="mt-2 rounded"/>
            <button class="btn btn-primary mt-2">Change Icon</button>
        </div>
        <div class="border rounded p-4">
            <h3>Icon Upload</h3>
            <p>Upload a new server icon (PNG/JPG, 64x64)</p>
            <input type="file" accept="image/*" class="mt-2"/>
            <button class="btn btn-primary mt-2">Upload</button>
        </div>
    </div>
</div>
@endsection