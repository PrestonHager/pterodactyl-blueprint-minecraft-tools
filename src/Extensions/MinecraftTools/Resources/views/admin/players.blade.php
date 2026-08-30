@extends('admin.layouts.default')

@section('title', 'Players - Minecraft Tools')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Player Management</h1>
                <div>
                    <button type="button" class="btn btn-primary me-2" id="addPlayerBtn">
                        <i class="fas fa-plus"></i> Add Player
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="refreshPlayersBtn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 id="onlineCount">0</h5>
                            <p class="mb-0">Online Players</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 id="whitelistedCount">0</h5>
                            <p class="mb-0">Whitelisted</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <h5 id="bannedCount">0</h5>
                            <p class="mb-0">Banned</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 id="oppedCount">0</h5>
                            <p class="mb-0">Operators</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="playerSearch" placeholder="Search players...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="playerType">
                                <option value="all">All Players</option>
                                <option value="online">Online</option>
                                <option value="whitelisted">Whitelisted</option>
                                <option value="banned">Banned</option>
                                <option value="ops">Operators</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-primary w-100" id="exportPlayersBtn">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="playersTable">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>UUID</th>
                                    <th>Status</th>
                                    <th>Rank</th>
                                    <th>Last Seen</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Players loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Player Modal -->
<div class="modal fade" id="addPlayerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Player Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addPlayerForm">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <select class="form-select" name="action" required>
                            <option value="whitelist">Add to Whitelist</option>
                            <option value="ban">Ban Player</option>
                            <option value="op">Give OP</option>
                        </select>
                    </div>
                    <div class="mb-3" id="reasonField">
                        <label class="form-label">Reason (optional)</label>
                        <textarea class="form-control" name="reason" rows="2"></textarea>
                    </div>
                    <div class="mb-3" id="expiresField" style="display: none;">
                        <label class="form-label">Ban Expires (optional)</label>
                        <input type="datetime-local" class="form-control" name="expires_at">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmPlayerAction">Execute</button>
            </div>
        </div>
    </div>
</div>

<!-- Player Details Modal -->
<div class="modal fade" id="playerDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="playerDetailsTitle">Player Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="playerDetailsBody">
                Loading...
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadPlayers();
    loadStats();
    
    document.getElementById('addPlayerBtn').addEventListener('click', function() {
        document.getElementById('addPlayerForm').reset();
        document.getElementById('expiresField').style.display = 'none';
        new bootstrap.Modal(document.getElementById('addPlayerModal')).show();
    });
    
    document.getElementById('refreshPlayersBtn').addEventListener('click', loadPlayers);
    document.getElementById('playerSearch').addEventListener('input', debounce(loadPlayers, 300));
    document.getElementById('playerType').addEventListener('change', loadPlayers);
    document.getElementById('confirmPlayerAction').addEventListener('click', executePlayerAction);
    document.getElementById('exportPlayersBtn').addEventListener('click', exportPlayers);
    
    document.querySelector('#addPlayerForm select[name="action"]').addEventListener('change', function() {
        const reasonField = document.getElementById('reasonField');
        const expiresField = document.getElementById('expiresField');
        
        if (this.value === 'ban') {
            reasonField.style.display = 'block';
            expiresField.style.display = 'block';
        } else {
            reasonField.style.display = 'block';
            expiresField.style.display = 'none';
        }
    });
});

function loadPlayers() {
    const search = document.getElementById('playerSearch').value;
    const type = document.getElementById('playerType').value;
    
    fetch(`/api/extensions/minecraft-tools/players?search=${encodeURIComponent(search)}&type=${type}`, {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        renderPlayers(data.players);
    })
    .catch(console.error);
}

function loadStats() {
    Promise.all([
        fetch('/api/extensions/minecraft-tools/players/online', { headers: { 'Authorization': `Bearer {{ auth()->user()->api_token }}` } }).then(r => r.json()),
        fetch('/api/extensions/minecraft-tools/players/whitelisted', { headers: { 'Authorization': `Bearer {{ auth()->user()->api_token }}` } }).then(r => r.json()),
        fetch('/api/extensions/minecraft-tools/players/banned', { headers: { 'Authorization': `Bearer {{ auth()->user()->api_token }}` } }).then(r => r.json()),
        fetch('/api/extensions/minecraft-tools/players/ops', { headers: { 'Authorization': `Bearer {{ auth()->user()->api_token }}` } }).then(r => r.json()),
    ]).then(([online, whitelisted, banned, ops]) => {
        document.getElementById('onlineCount').textContent = online.players?.length || 0;
        document.getElementById('whitelistedCount').textContent = whitelisted.players?.length || 0;
        document.getElementById('bannedCount').textContent = banned.players?.length || 0;
        document.getElementById('oppedCount').textContent = ops.players?.length || 0;
    }).catch(console.error);
}

function renderPlayers(players) {
    const tbody = document.querySelector('#playersTable tbody');
    tbody.innerHTML = '';
    
    players.forEach(player => {
        const row = document.createElement('tr');
        const statusBadges = [];
        
        if (player.online) statusBadges.push('<span class="badge bg-success">Online</span>');
        if (player.whitelisted) statusBadges.push('<span class="badge bg-info">Whitelisted</span>');
        if (player.banned) statusBadges.push('<span class="badge bg-danger">Banned</span>');
        if (player.op) statusBadges.push('<span class="badge bg-warning text-dark">OP</span>');
        if (statusBadges.length === 0) statusBadges.push('<span class="badge bg-secondary">Offline</span>');
        
        row.innerHTML = `
            <td>${player.username}</td>
            <td><code>${player.uuid || 'N/A'}</code></td>
            <td>${statusBadges.join(' ')}</td>
            <td>${player.rank || 'Default'}</td>
            <td>${player.last_seen ? new Date(player.last_seen).toLocaleString() : 'Never'}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="viewPlayer('${player.username}')">View</button>
                    ${player.online ? `<button class="btn btn-outline-warning" onclick="kickPlayer('${player.username}')">Kick</button>` : ''}
                    ${player.whitelisted ? 
                        `<button class="btn btn-outline-info" onclick="toggleWhitelist('${player.username}', false)">Remove Whitelist</button>` :
                        `<button class="btn btn-outline-success" onclick="toggleWhitelist('${player.username}', true)">Whitelist</button>`
                    }
                    ${player.banned ? 
                        `<button class="btn btn-outline-danger" onclick="toggleBan('${player.username}', false)">Unban</button>` :
                        `<button class="btn btn-outline-danger" onclick="toggleBan('${player.username}', true)">Ban</button>`
                    }
                    ${player.op ? 
                        `<button class="btn btn-outline-warning" onclick="toggleOp('${player.username}', false)">Deop</button>` :
                        `<button class="btn btn-outline-warning" onclick="toggleOp('${player.username}', true)">Op</button>`
                    }
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function executePlayerAction() {
    const form = document.getElementById('addPlayerForm');
    const formData = new FormData(form);
    const action = formData.get('action');
    const username = formData.get('username');
    
    let endpoint, method = 'POST';
    
    switch (action) {
        case 'whitelist': endpoint = `players/${username}/whitelist`; break;
        case 'ban': endpoint = `players/${username}/ban`; break;
        case 'op': endpoint = `players/${username}/op`; break;
    }
    
    fetch(`/api/extensions/minecraft-tools/${endpoint}`, {
        method,
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            reason: formData.get('reason'),
            expires_at: formData.get('expires_at') || null,
        }),
    })
    .then(response => response.json())
    .then(data => {
        bootstrap.Modal.getInstance(document.getElementById('addPlayerModal')).hide();
        loadPlayers();
        loadStats();
        showAlert(`Player ${action}ed successfully`, 'success');
    })
    .catch(console.error);
}

function viewPlayer(username) {
    const modal = new bootstrap.Modal(document.getElementById('playerDetailsModal'));
    document.getElementById('playerDetailsTitle').textContent = username;
    document.getElementById('playerDetailsBody').innerHTML = 'Loading...';
    modal.show();
    
    fetch(`/api/extensions/minecraft-tools/players/${username}`, {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        const p = data.player;
        document.getElementById('playerDetailsBody').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th>Username</th><td>${p.username}</td></tr>
                        <tr><th>UUID</th><td><code>${p.uuid || 'N/A'}</code></td></tr>
                        <tr><th>Online</th><td>${p.online ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'}</td></tr>
                        <tr><th>Whitelisted</th><td>${p.whitelisted ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'}</td></tr>
                        <tr><th>Banned</th><td>${p.banned ? '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-secondary">No</span>'}</td></tr>
                        <tr><th>Operator</th><td>${p.op ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'}</td></tr>
                        <tr><th>Rank</th><td>${p.rank || 'Default'}</td></tr>
                        <tr><th>First Join</th><td>${p.first_join ? new Date(p.first_join).toLocaleString() : 'Unknown'}</td></tr>
                        <tr><th>Last Seen</th><td>${p.last_seen ? new Date(p.last_seen).toLocaleString() : 'Never'}</td></tr>
                        <tr><th>Playtime</th><td>${p.playtime || 'Unknown'}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <h6>Inventory</h6>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewInventory('${p.username}')">View Inventory</button>
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="viewEnderchest('${p.username}')">View Enderchest</button>
                    </div>
                    <div class="mb-3">
                        <h6>Logs</h6>
                        <button class="btn btn-sm btn-outline-secondary" onclick="viewLogs('${p.username}')">View Logs</button>
                    </div>
                </div>
            </div>
        `;
    })
    .catch(console.error);
}

function kickPlayer(username) {
    const reason = prompt(`Reason for kicking ${username}:`) || 'Kicked by admin';
    if (!reason) return;
    
    fetch(`/api/extensions/minecraft-tools/players/${username}/kick`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ reason }),
    })
    .then(response => response.json())
    .then(data => {
        loadPlayers();
        loadStats();
        showAlert('Player kicked', 'success');
    })
    .catch(console.error);
}

function toggleWhitelist(username, add) {
    const endpoint = add ? 'whitelist' : 'unwhitelist';
    fetch(`/api/extensions/minecraft-tools/players/${username}/${endpoint}`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        loadPlayers();
        loadStats();
        showAlert(`Player ${add ? 'whitelisted' : 'removed from whitelist'}`, 'success');
    })
    .catch(console.error);
}

function toggleBan(username, add) {
    if (add) {
        const reason = prompt(`Reason for banning ${username}:`) || 'Banned by admin';
        const expires = prompt('Ban expires (ISO date, optional):') || null;
        
        fetch(`/api/extensions/minecraft-tools/players/${username}/ban`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer {{ auth()->user()->api_token }}`,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ reason, expires_at: expires }),
        })
        .then(response => response.json())
        .then(data => {
            loadPlayers();
            loadStats();
            showAlert('Player banned', 'success');
        })
        .catch(console.error);
    } else {
        fetch(`/api/extensions/minecraft-tools/players/${username}/unban`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer {{ auth()->user()->api_token }}`,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            loadPlayers();
            loadStats();
            showAlert('Player unbanned', 'success');
        })
        .catch(console.error);
    }
}

function toggleOp(username, add) {
    const endpoint = add ? 'op' : 'deop';
    fetch(`/api/extensions/minecraft-tools/players/${username}/${endpoint}`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        loadPlayers();
        loadStats();
        showAlert(`Player ${add ? 'opped' : 'deopped'}`, 'success');
    })
    .catch(console.error);
}

function viewInventory(username) {
    alert('Inventory viewing not yet implemented');
}

function viewEnderchest(username) {
    alert('Enderchest viewing not yet implemented');
}

function viewLogs(username) {
    alert('Logs viewing not yet implemented');
}

function exportPlayers() {
    fetch('/api/extensions/minecraft-tools/players?limit=1000', {
        headers: {
            'Authorization': `Bearer {{ auth()->user()->api_token }}`,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        const csv = 'Username,UUID,Online,Whitelisted,Banned,OP,Rank,Last Seen\n' +
            data.players.map(p => 
                `"${p.username}","${p.uuid || ''}",${p.online},${p.whitelisted},${p.banned},${p.op},"${p.rank || 'Default'}","${p.last_seen || ''}"`
            ).join('\n');
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `players-${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        URL.revokeObjectURL(url);
    })
    .catch(console.error);
}

function showAlert(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alert.style.zIndex = '9999';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
}

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}
</script>
@endsection