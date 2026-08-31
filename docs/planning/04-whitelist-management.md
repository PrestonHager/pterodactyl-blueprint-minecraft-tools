# Whitelist & Access Management Planning

## Overview

This document details the whitelist management system, automated access approval workflow, and player permission tiers for the Minecraft Tools extension. The goal is to provide a "set and forget" whitelist system where admins approve access once and the system handles everything automatically.

---

## Core Concepts

### Whitelist Entry Types

| Type | Description | Example |
|------|-------------|---------|
| Permanent | Always allowed | Staff members, long-term members |
| Temporary | Allowed for a duration | Trial members (7 days) |
| Scheduled | Allowed at specific times | Weekly events, scheduled tenants |
| One-time | Single visit/event | Event participants |

### Access States

```
+--------------+
|              |
|   REQUESTED  |<--- User submits join request
|              |
+------+-------+
       |
       v
+--------------+       +--------------+
|              |       |              |
|   APPROVED   |<----->|   REJECTED   |
|              |       |              |
+------+-------+       +--------------+
       |
       v
+--------------+
|              |
|   WHITELISTED|  (active on server)
|              |
+------+-------+
       |
       v
+--------------+
|              |
|   EXPIRED    |  (time-based access ended)
|              |
+--------------+
```

---

## Join Request System

### User Journey

```
1. User visits server's join page
2. Fills out join request form
3. System validates Minecraft username (Mojang API)
4. Request enters "Pending" queue
5. Admin approves/rejects (auto or manual)
6. If approved: user added to whitelist automatically
7. User receives notification (email/panel/Discord)
```

### Join Request Form

**User Provides:**
- Minecraft username (validated via Mojang API)
- Optional: Discord username
- Optional: Reason for joining
- Optional: Referral (referring member)
- Optional: Age verification for 18+ servers

**System Captures:**
- IP address (for abuse prevention)
- Request timestamp
- User agent
- Server ID
- Channel (panel, Discord link, email)

### Request Statuses

| Status | Meaning | Actions |
|--------|---------|---------|
| `pending` | Awaiting approval | View, approve, reject |
| `approved` | Approved, not yet added | Add to whitelist |
| `whitelisted` | Active on server | View details, remove |
| `rejected` | Denied | View reason, allow resubmission |
| `expired` | Access expired | Renew, remove |
| `revoked` | Removed by admin | View audit trail |
| `flagged` | Marked suspicious | Manual review required |

---

## Whitelist Management

### Admin Whitelist Actions

| Action | Description |
|--------|-------------|
| Add player | Manually add by username or UUID |
| Bulk add | Import from CSV or text list |
| Remove player | Remove from whitelist |
| Bulk remove | Mass removal |
| Set expiry | Define end date for temporary access |
| Set tier | Assign permission level |
| Add notes | Internal notes on player |
| Transfer | Move player between servers |
| Sync | Sync whitelist to server files |
| Backup | Backup whitelist.json |

### Automatic Whitelist Sync

The system syncs whitelist data to the Minecraft server via one of:

1. **Server File Access**: Directly write `whitelist.json` via Pterodactyl file API
2. **RCON Command**: Execute `whitelist add <player>` via RCON
3. **Plugin Integration**: Use a companion Spigot/Paper plugin for real-time sync
4. **Websocket/Webhook**: Real-time updates via plugin webhook

### Sync Methods Comparison

| Method | Real-time | Requires Plugin | Complexity |
|--------|-----------|----------------|------------|
| File write | On restart | No | Low |
| RCON | Yes | No (RCON port) | Medium |
| Companion plugin | Yes | Yes | High |
| Webhook | Near real-time | Yes | High |

**Recommended**: RCON for reliability + companion plugin for advanced features.

---

## Permission Tiers

### Tier System

| Tier | Name | Server Permissions | Whitelist Permission |
|------|------|-------------------|---------------------|
| 0 | Visitor | Build off | View-only access |
| 1 | Member | Build on, no grief | Full access |
| 2 | Trusted | Extended permissions | Full access + priority |
| 3 | Moderator | WorldEdit, kick | Full access + moderation |
| 4 | Admin | All permissions | Full access + management |
| 5 | Owner | Everything | Master control |

### Tier Configuration per Server

```yaml
# Server whitelist configuration
whitelist:
  default_tier: 1
  tiers:
    visitor:
      commands_to_run: []        # Commands on whitelist add
      permissions: []            # LuckPerms groups
      color: gray
      priority: 10
      duration: null             # Permanent
      
    member:
      commands_to_run: ['lp user {player} parent add member']
      permissions: ['member.*']
      color: green
      priority: 20
      duration: null             # Permanent
      
    trusted:
      commands_to_run: ['lp user {player} parent add trusted']
      permissions: ['trusted.*']
      color: blue
      priority: 30
      duration: null
      
    moderator:
      commands_to_run: ['lp user {player} parent add moderator']
      permissions: ['moderator.*']
      color: gold
      priority: 40
      duration: null
```

---

## Approval Workflows

### Workflow Types

| Workflow | Description | When Used |
|----------|-------------|-----------|
| **Manual** | Admin reviews each request | Default for public servers |
| **Auto-approve** | Instantly approve all requests | Whitelisted-by-default servers |
| **Rules-based** | Auto-approve based on conditions | Community servers |
| **Referral-based** | Require existing member referral | Closed communities |
| **Hybrid** | Specific groups auto-approved | Private communities |

### Rules-Based Approval Rules

| Rule Type | Example | Evaluation |
|-----------|---------|------------|
| Age | Account age > 30 days | Mojang account created before date |
| Referral | Has valid referral code | Referral exists and valid |
| Notoriety | Not on banned list | Check external ban databases |
| Server | Whitelisted on sister server | Cross-server verification |
| Role | Has Discord role | Discord integration |
| Time | Join request during open window | Allow only during enrollment periods |

### Rule Example Configuration

```yaml
approval:
  mode: rules_based
  rules:
    - name: "Account older than 30 days"
      type: account_age
      comparison: greater_than
      value: 30
      action: approve
      
    - name: "Not on global ban list"
      type: ban_list
      list: [banned_server_1, banned_server_2]
      action: reject
      
    - name: "Discord member with member role"
      type: discord_role
      role_id: "123456789"
      action: approve
      weight: 100
```

---

## Discord Integration

### Purpose
Allow players to request whitelist access and receive updates via Discord.

### Features

| Feature | Description |
|---------|-------------|
| Join request bot | `/whitelist join <username>` command |
| Approval channel | Notifications of pending requests |
| Status updates | DM user on approval/rejection |
| Verification | Discord account linking + role assignment |
| Rules enforcement | Require rules agreement checkbox |

### Discord Commands

```
/whitelist join <username> [reason]
/whitelist status
/whitelist cancel
/whitelist help

/admin-whitelist pending
/admin-whitelist approve <request_id>
/admin-whitelist reject <request_id> <reason>
/admin-whitelist list
/admin-whitelist add <username> [tier] [duration]
/admin-whitelist remove <username>
```

### Discord Webhook Events

```json
{
  "event": "whitelist_request",
  "data": {
    "server_id": "abc123",
    "username": "Steve",
    "uuid": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx",
    "discord_id": "123456789",
    "reason": "Looking to join your community",
    "timestamp": "2024-01-01T12:00:00Z"
  }
}
```

---

## Database Schema

```sql
-- Join requests
CREATE TABLE minecraft_tools_join_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    server_id BIGINT UNSIGNED NOT NULL,
    username VARCHAR(16) NOT NULL,
    uuid CHAR(36) NULL,
    discord_id VARCHAR(32) NULL,
    email VARCHAR(255) NULL,
    reason TEXT NULL,
    referral_code VARCHAR(32) NULL,
    referral_by VARCHAR(16) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    status ENUM('pending', 'approved', 'whitelisted', 'rejected', 'expired', 'revoked', 'flagged') DEFAULT 'pending',
    status_reason TEXT NULL,
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_server_status (server_id, status),
    INDEX idx_username (username),
    FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
);

-- Whitelist entries
CREATE TABLE minecraft_tools_whitelist_entries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    server_id BIGINT UNSIGNED NOT NULL,
    username VARCHAR(16) NOT NULL,
    uuid CHAR(36) NULL,
    tier TINYINT DEFAULT 1,
    expires_at TIMESTAMP NULL,
    notes TEXT NULL,
    added_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_server_username (server_id, username),
    INDEX idx_server_tier (server_id, tier),
    FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
);

-- Whitelist audit log
CREATE TABLE minecraft_tools_whitelist_audit (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    server_id BIGINT UNSIGNED NOT NULL,
    username VARCHAR(16) NOT NULL,
    action ENUM('added', 'removed', 'expired', 'revoked', 'tier_change', 'sync') NOT NULL,
    actor BIGINT UNSIGNED NULL,
    details TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_server_username (server_id, username),
    FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
);

-- Approval rules
CREATE TABLE minecraft_tools_approval_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    server_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(64) NOT NULL,
    type ENUM('account_age', 'referral', 'ban_list', 'discord_role', 'manual', 'complex') NOT NULL,
    config JSON NOT NULL,
    action ENUM('approve', 'reject', 'manual_review') NOT NULL DEFAULT 'manual_review',
    weight INT DEFAULT 0,
    enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
);
```

---

## API Endpoints

### Join Requests
```
POST   /api/join-requests                     # Submit join request
GET    /api/join-requests                     # List requests (admin)
GET    /api/join-requests/{id}                # Get request details
POST   /api/join-requests/{id}/approve        # Approve request
POST   /api/join-requests/{id}/reject         # Reject request
POST   /api/join-requests/{id}/cancel         # Cancel request (user)
GET    /api/join-requests/status              # Check own request status
GET    /api/join-requests/referrals           # Get referral code
```

### Whitelist
```
GET    /api/whitelist                         # List whitelisted players
POST   /api/whitelist                         # Add player
DELETE /api/whitelist/{username}              # Remove player
PUT    /api/whitelist/{username}              # Update player tier/expiry
POST   /api/whitelist/bulk                    # Bulk add
POST   /api/whitelist/sync                    # Sync to server
GET    /api/whitelist/backups                 # List backups
POST   /api/whitelist/backups/{id}/restore    # Restore backup
GET    /api/whitelist/audit                   # View audit log
```

### Approval Rules
```
GET    /api/approval-rules                    # List rules
POST   /api/approval-rules                    # Create rule
PUT    /api/approval-rules/{id}               # Update rule
DELETE /api/approval-rules/{id}               # Delete rule
POST   /api/approval-rules/{id}/test          # Test rule
```

---

## Frontend Components

### Admin View - Whitelist Management

```
+---------------------------------------+
|  Whitelist Management                 |
+---------------------------------------+
|  TABS: Players | Requests | Rules    |
+---------------------------------------+
|                                       |
|  [Private server view]                |
|                                       |
|  +--------------------------------+   |
|  | Search: [________] [Filter]    |   |
|  |--------------------------------|   |
|  | Username  Tier  Expires  Act   |   |
|  | Steve     1     Never    [...] |   |
|  | Alex      2     12/31   [...]  |   |
|  | ...                             |   |
|  +--------------------------------+   |
|                                       |
+---------------------------------------+
```

### Public Join Request Form

```
+---------------------------------------+
|  Join [Server Name]                   |
+---------------------------------------+
|                                       |
|  Minecraft Username: [________]       |
|  Discord (optional): [________]       |
|  Reason: [________________]           |
|                                       |
|  [X] I agree to the server rules      |
|                                       |
|  [Submit Request]                     |
|                                       |
|  Status: Pending review               |
+---------------------------------------+
```

---

## Notification System

### Notification Channels

| Channel | Use Case |
|---------|----------|
| Panel notification | In-app status updates |
| Email | Approval/rejection confirmation |
| Discord DM | Discord-linked users |
| Webhook | Server/community webhooks |
| In-game | Companion plugin announcements |

### Notification Templates

**Approval:**
```
Subject: You've been whitelisted on {server_name}!
Body: Congratulations {username}! You've been approved for {server_name}.
You can now join at {server_address}.
```

**Rejection:**
```
Subject: Update on your {server_name} whitelist request
Body: Hi {username}, your request to join {server_name} was declined.
Reason: {reason}
```

---

## Milestones

| Milestone | Feature | Target | Dependencies |
|-----------|---------|--------|--------------|
| M4.1 | Whitelist add/remove (manual) | Week 9 | M1.1 |
| M4.2 | Join request form (basic) | Week 10 | M4.1 |
| M4.3 | Approval workflow (manual) | Week 10 | M4.2 |
| M4.4 | RCON sync | Week 11 | M4.1 |
| M4.5 | Tier system | Week 11 | M4.4 |
| M4.6 | Rules-based auto approval | Week 12 | M4.3 |
| M4.7 | Discord integration | Week 13 | M4.3 |
| M4.8 | Audit logging + backups | Week 14 | M4.1 |

---

## Security Considerations

1. **Username Validation**: Always validate against Mojang API to prevent spoofing
2. **UUID Storage**: Store UUID (not just username) for persistent identity
3. **Rate Limiting**: Prevent whitelist request spam
4. **IP Monitoring**: Track abuse patterns
5. **Approval Auditing**: Log all approval/rejection actions with actor
6. **Auto-revoke**: Automated removal of expired/inactive entries

---

## Expansion Ideas

### Future Features (Post v1.0)

| Feature | Description | Priority |
|---------|-------------|----------|
| Server-wide shared whitelist | One whitelist across multiple servers | High |
| Discord role sync | Auto-assign roles based on whitelist entries | High |
| In-game command integration | `/whitelist` commands via companion plugin | Medium |
| Batch import from Discord | Import members from Discord roles | Medium |
| Ban list integration | Cross-check global ban databases | Low |
| Subscription/membership | Paid whitelist access tiers | Low |
| API for external systems | Notify external tools of whitelist changes | Medium |
| Webhook on whitelist changes | Push events to community webhooks | Low |
