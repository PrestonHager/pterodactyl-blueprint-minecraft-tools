@extends('admin.layouts.default')

@section('title')
    Minecraft Tools — Players
@endsection

@section('content-header')
    <h1>Player Management<small>Manage player notes and server actions</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="/admin/extensions/minecraft-tools">Minecraft Tools</a></li>
        <li class="active">Players</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Player Notes</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" id="refresh-players"><i class="fa fa-refresh"></i></button>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Display Name</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="players-list">
                        <tr><td colspan="4" class="text-center text-muted">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Add Player Note</h3>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-control" id="add-username" placeholder="Steve">
                </div>
                <div class="form-group">
                    <label>UUID (optional)</label>
                    <input type="text" class="form-control" id="add-uuid" placeholder="00000000-0000-0000-0000-000000000000">
                </div>
                <div class="form-group">
                    <label>Display Name (optional)</label>
                    <input type="text" class="form-control" id="add-display-name" placeholder="Steve">
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea class="form-control" id="add-notes" rows="3" placeholder="Known griefer..."></textarea>
                </div>
                <button type="button" class="btn btn-primary btn-block" id="do-add-player"><i class="fa fa-plus"></i> Add Note</button>
            </div>
        </div>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Quick Actions</h3>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-control" id="action-username" placeholder="Username">
                </div>
                <div class="btn-group-vertical" style="width:100%">
                    <button type="button" class="btn btn-danger player-action" data-action="ban"><i class="fa fa-ban"></i> Ban</button>
                    <button type="button" class="btn btn-warning player-action" data-action="kick"><i class="fa fa-sign-out"></i> Kick</button>
                    <button type="button" class="btn btn-info player-action" data-action="whitelist"><i class="fa fa-check"></i> Whitelist</button>
                    <button type="button" class="btn btn-info player-action" data-action="unwhitelist"><i class="fa fa-times"></i> Unwhitelist</button>
                    <button type="button" class="btn btn-success player-action" data-action="op"><i class="fa fa-star"></i> OP</button>
                    <button type="button" class="btn btn-warning player-action" data-action="deop"><i class="fa fa-star-o"></i> De-OP</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal fade" id="edit-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Player: <span id="edit-player-name"></span></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Display Name</label>
                    <input type="text" class="form-control" id="edit-display-name">
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea class="form-control" id="edit-notes" rows="4"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-edit">Save</button>
            </div>
        </div>
    </div>
</div>
<script>
(function() {
    var api = '/extensions/minecraft-tools/api/players';
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

    function loadPlayers() {
        fetchJSON(api).then(function(d) {
            var tbody = document.getElementById('players-list');
            var players = d.players || [];
            if (!players.length) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No player notes yet. Add one using the form on the right.</td></tr>';
                return;
            }
            tbody.innerHTML = players.map(function(p) {
                return '<tr>' +
                    '<td><strong>' + escapeHtml(p.username) + '</strong></td>' +
                    '<td>' + escapeHtml(p.display_name || '-') + '</td>' +
                    '<td>' + escapeHtml((p.notes || '').substring(0, 80)) + ((p.notes || '').length > 80 ? '...' : '') + '</td>' +
                    '<td>' +
                        '<button class="btn btn-xs btn-info edit-player" data-player="' + escapeHtml(p.username) + '" data-display="' + escapeHtml(p.display_name || '') + '" data-notes="' + escapeHtml(p.notes || '') + '"><i class="fa fa-edit"></i></button> ' +
                        '<button class="btn btn-xs btn-danger delete-player" data-player="' + escapeHtml(p.username) + '"><i class="fa fa-trash"></i></button>' +
                    '</td></tr>';
            }).join('');
        });
    }

    document.getElementById('refresh-players').addEventListener('click', loadPlayers);

    document.getElementById('do-add-player').addEventListener('click', function() {
        var username = document.getElementById('add-username').value.trim();
        if (!username) { alert('Username is required'); return; }
        fetchJSON(api, {
            method: 'POST',
            body: JSON.stringify({
                username: username,
                uuid: document.getElementById('add-uuid').value.trim() || null,
                display_name: document.getElementById('add-display-name').value.trim() || null,
                notes: document.getElementById('add-notes').value.trim() || null
            })
        }).then(function() {
            document.getElementById('add-username').value = '';
            document.getElementById('add-uuid').value = '';
            document.getElementById('add-display-name').value = '';
            document.getElementById('add-notes').value = '';
            loadPlayers();
        });
    });

    document.getElementById('players-list').addEventListener('click', function(e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        var player = btn.dataset.player;

        if (btn.classList.contains('edit-player')) {
            document.getElementById('edit-player-name').textContent = player;
            document.getElementById('edit-display-name').value = btn.dataset.display || '';
            document.getElementById('edit-notes').value = btn.dataset.notes || '';
            $('#edit-modal').modal('show');
        }

        if (btn.classList.contains('delete-player')) {
            if (!confirm('Delete notes for ' + player + '?')) return;
            fetchJSON(api + '/' + encodeURIComponent(player), { method: 'DELETE' })
                .then(function() { loadPlayers(); });
        }
    });

    document.getElementById('save-edit').addEventListener('click', function() {
        var player = document.getElementById('edit-player-name').textContent;
        fetchJSON(api + '/' + encodeURIComponent(player), {
            method: 'PUT',
            body: JSON.stringify({
                display_name: document.getElementById('edit-display-name').value.trim() || null,
                notes: document.getElementById('edit-notes').value.trim() || null
            })
        }).then(function() { $('#edit-modal').modal('hide'); loadPlayers(); });
    });

    document.querySelectorAll('.player-action').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var action = btn.dataset.action;
            var username = document.getElementById('action-username').value.trim();
            if (!username) { alert('Enter a username'); return; }
            if (!confirm(action.toUpperCase() + ' player ' + username + '?')) return;
            fetchJSON(api + '/' + encodeURIComponent(username) + '/' + action, { method: 'POST' })
                .then(function(d) { alert(d.message || 'Action recorded'); });
        });
    });

    loadPlayers();
})();
</script>
@endsection
