# User Automation Planning

## Overview

This document details the automation systems for user onboarding, server provisioning, access requests, notifications, and audit logging. The goal is to minimize manual administrative work by automating repetitive tasks while maintaining security and control.

---

## Automation Areas

| Area | Description | Automation Level |
|------|-------------|------------------|
| Server provisioning | Auto-create servers from templates | High |
| Access requests | Automatic whitelist approval | Configurable |
| Updates | Plugin/modpack/version update checks | Fully automated |
| Notifications | Email/Discord/panel notifications | Fully automated |
| Backups | Scheduled config/plugin backups | Fully automated |
| Monitoring | Server health and activity monitoring | Fully automated |
| Cleanup | Expired whitelist/backup cleanup | Fully automated |
| Onboarding | New user welcome flow | High |

---

## 1. Server Provisioning

### Auto-Provisioning Flow

```
1. User requests a new server (panel form or admin action)
2. System validates user permissions/allowed server types
3. System selects template (predefined egg, resources, variables)
4. System calls Pterodactyl API to create server
5. System runs post-install jobs (default plugins, configs, whitelist)
6. System assigns default permissions to user
7. User notified with server details
```

### Server Templates

```yaml
# Server templates for one-click provisioning
templates:
  default:
    name: "Vanilla Survival"
    egg: "vanilla"
    memory: 2048
    disk: 10240
    databases: 1
    ports: 1
    variables:
      DIFFICULTY: normal
      MODE: survival
      MOTD: "Welcome to our server!"
    plugins: ["essentials", "worldedit"]
    configs:
      server.properties:
        server-port: "{{SERVER_PORT}}"
    
  paper-survival:
    name: "Paper Survival"
    egg: "paper"
    memory: 4096
    disk: 20480
    databases: 2
    ports: 1
    variables:
      PAPER_VERSION: "1.20.4"
    plugins: ["essentialsx", "worldedit", "coreprotect"]
    
  fabric-client:
    name: "Fabric Modded"
    egg: "fabric"
    memory: 4096
    disk: 20480
    variables:
      FABRIC_LOADER_VERSION: "0.15.7"
    modpack: "selected-at-provision-time"
```

### Database Schema

```sql
-- Server templates
CREATE TABLE minecraft_tools_server_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(64) NOT NULL,
    slug VARCHAR(64) UNIQUE NOT NULL,
    egg_id BIGINT UNSIGNED NOT NULL,
    description TEXT NULL,
    template_name VARCHAR(64) NOT NULL,
    memory INT NOT NULL,
    disk INT NOT NULL,
    cpu INT DEFAULT 100,
    databases INT DEFAULT 0,
    ports INT DEFAULT 1,
    variables JSON NULL,
    plugins JSON NULL,
    configs JSON NULL,
    modpack_slug VARCHAR(64) NULL,
    enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Provisioning jobs
CREATE TABLE minecraft_tools_provisioning_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    server_id BIGINT UNSIGNED NOT NULL,
    template_id BIGINT UNSIGNED NOT NULL,
    requested_by BIGINT UNSIGNED NOT NULL,
    status ENUM('pending', 'creating', 'installing', 'complete', 'failed') DEFAULT 'pending',
    error TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
);
```

---

## 2. Access & Whitelist Automation

### Automated Approval

**When enabled**, the system automatically processes join requests based on rules:

1. Validate username via Mojang API
2. Check against approval rules (account age, ban lists, etc.)
3. Auto-approve, auto-reject, or route to manual review
4. On approval: whitelist add + welcome commands + notification

### Auto-Expiry

**Scheduled job runs periodically to:**
- Remove expired whitelist entries
- Notify users before expiry (5d, 1d, 1h reminders)
- Clean up revoked/expired request records (after 30 days)

### Cross-Server Whitelist Sync

If enabled, whitelist for one server auto-syncs to connected servers:

```
Server A whitelist  →  Server B, Server C (shared community)
```

Triggered by:
- New whitelist entry
- Removed entry
- Tier change
- Discord role change (via integration)

---

## 3. Update Automation

### Plugin Updates

**Scheduled checks** (configurable interval, default 1 hour):
- Check installed plugins against upstream (Modrinth/SpigotMC/CurseForge)
- Notify admin of available updates
- Optional: auto-update (approve-only by default)
- Update changelog display before install

### Version Updates

**Scheduled checks** (default 6 hours):
- Check Paper/Fabric build availability
- Detect new Minecraft versions
- Notify admin + players of client version changes

### Modpack Updates

**Scheduled checks** (default 12 hours):
- Check installed modpacks against upstream
- Detect new modpack versions
- Show mod list changes

### Update Dashboard

```
+------------------------------------------+
|  UPDATE CENTER                            |
+------------------------------------------+
|  PLUGINS (3 updates available)            |
|  - EssentialsX: 5.2.1 → 5.3.0  [Update]  |
|  - WorldEdit: 7.3.0 → 7.3.2    [Update]  |
|  - CoreProtect: 22.6 → 22.7    [Update]  |
|                                          |
|  VERSIONS (1 update available)            |
|  - Paper: 1.20.4-Build 496 → 497 [Update] |
|                                          |
|  MODPACKS (0 updates available)           |
|                                          |
+------------------------------------------+
```

---

## 4. Notification Automation

### Notification System

| Channel | Event | Priority | Rate Limited |
|---------|-------|----------|--------------|
| Panel (in-app) | All events | - | No |
| Email | Approval, rejection, expiry warnings | Medium | Yes |
| Discord DM | Status changes (if linked) | Medium | Yes |
| Discord channel | Server-wide announcements | Low | Yes (throttled) |
| Webhook | External integrations | Low | Yes |
| In-game | Player connect/disconnect, whitelist changes | High | Yes |

### Event → Notification Mapping

| Event | Panel | Email | Discord | Webhook |
|-------|-------|-------|---------|---------|
| Whitelist request | ✅ | ❌ | ✅ | ✅ |
| Request approved | ✅ | ✅ | ✅ | ✅ |
| Request rejected | ✅ | ✅ | ✅ | ✅ |
| Whitelist expiry (5 days) | ✅ | ✅ | ✅ | ❌ |
| Whitelist expiry (today) | ✅ | ✅ | ✅ | ❌ |
| Plugin update available | ✅ | ❌ | ❌ | ✅ |
| Version update available | ✅ | ✅ (admins) | ❌ | ✅ |
| Server offline | ✅ | ✅ | ❌ | ✅ |
| Backup completed | ✅ | ❌ | ❌ | ❌ |
| Backup failed | ✅ | ✅ | ❌ | ✅ |

### Notification Preferences

Users can configure notification preferences:
```json
{
  "email": true,
  "discord_dm": true,
  "panel": true,
  "events": {
    "whitelist_approved": true,
    "whitelist_rejected": true,
    "whitelist_expiring": true,
    "plugin_updates": true,
    "server_offline": false,
    "backups": false
  }
}
```

---

## 5. Backup Automation

### Backup Schedule Options

| Schedule | Interval | Retention |
|----------|----------|-----------|
| Off | Never | - |
| Hourly | Every 60 min | 24 backups |
| Daily (default) | Every 24 hours | 14 backups |
| Weekly | Every 7 days | 8 backups |
| Custom | Cron expression | Configurable |

### What Gets Backed Up

| Item | Description | Size |
|------|-------------|------|
| Config files | server.properties, spigot.yml, etc. | Small |
| Plugin configs | Plugin YAML configs | Small |
| World data | Optional (can be large) | Large |
| Whitelist | whitelist.json | Tiny |
| Server icon | server-icon.png | Tiny |
| Modpack | Installed modpack (optional) | Large |

### Backup Storage
 
| Location | Use |
|----------|-----|
| Local disk | Quick backups, short retention |
| S3/Ceph | Long-term backups, offsite redundancy |
| Pterodactyl backup API | Server backups via panel |

### Cleanup Job

Scheduled daily cleanup task:
```
- Remove expired whitelist entries
- Remove old backups beyond retention
- Remove old version snapshots (keep last 10)
- Purge audit logs older than X days (configurable)
- Clean failed download temp files
```

---

## 6. Health Monitoring

### Monitor Checks

| Check | Frequency | Alert |
|-------|-----------|-------|
| Server online | Every 5 min | Offline alert |
| API health | Every 10 min | Panel degraded |
| Upstream API health | Every 30 min | Degraded source |
| Storage free space | Every 30 min | Disk full warning |
| Queue backlog | Every 5 min | Jobs stuck |
| Error rate | Every minute | Spike detection |

### Health Dashboard

```
+------------------------------------------+
|  SYSTEM HEALTH                            |
+------------------------------------------+
|  Pterodactyl API:    ● Online (200ms)     |
|  Modrinth API:       ● Online             |
|  CurseForge API:     ● Online             |
|  PaperMC API:        ● Online             |
|  Queue Worker:       ● Running (0 jobs)   |
|  Storage:            ● 45% used (400GB)   |
|  Redis:              ● Connected          |
|                                          |
|  RECENT INCIDENTS                         |
|  - None in the last 24 hours              |
|                                          |
+------------------------------------------+
```

---

## 7. Audit Logging

### What Gets Logged

| Category | Events |
|----------|--------|
| Authentication | Login, logout, failed login (panel + API) |
| Whitelist | Add, remove, tier change, expiry |
| Join requests | Submit, approve, reject, cancel |
| Plugins | Install, uninstall, enable, disable |
| Versions | Install, switch, rollback |
| Modpacks | Install, switch, update, backup |
| Configs | Edit, save, backup, restore |
| Icons | Upload, delete, generate, template apply |
| Settings | Extension settings changes |
| Users | Role changes, permissions changes |

### Audit Log Schema

```sql
CREATE TABLE minecraft_tools_audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    server_id BIGINT UNSIGNED NULL,
    action VARCHAR(16) NOT NULL,
    resource_type VARCHAR(32) NOT NULL,
    resource_id VARCHAR(64) NULL,
    metadata JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_resource (resource_type, resource_id),
    INDEX idx_server (server_id),
    INDEX idx_created (created_at)
);
```

### Audit Log View (Admin)

```
+------------------------------------------+
|  AUDIT LOG                                |
+------------------------------------------+
|  Filters: [Resource] [Action] [Date From]|
|                                          |
|  Date       | User | Action     | Resource | Details    |
|------------|------|------------|----------|------------|
| 12:00:01  | Admin| whitelist  | add      | Steve added|
| 12:01:15  | Steve| plugin     | install   | WorldEdit  |
| 12:05:22  | Admin| version    | switch    | Paper 1.20 |
| 12:10:44  | System| backup    | complete  | Daily cfg  |
|                                          |
+------------------------------------------+
```

---

## 8. User Onboarding Flow

### Complete Onboarding Automation

```
1. NEW USER CREATED (via panel, admin, or auto-provision)
   ↓
2. Welcome email sent (with server details, docs, links)
   ↓
3. Default server provisioned (from template if enabled)
   ↓
4. Default Discord roles assigned (if Discord linked)
   ↓
5. Whitelist created with user as owner
   ↓
6. Welcome message posted to server console/channel
   ↓
7. User guided through setup wizard (first login)
```

### Onboarding Wizard Steps

| Step | Content |
|------|---------|
| 1. Welcome | Intro, what to expect |
| 2. Server setup | Choose template if not pre-assigned |
| 3. Whitelist | Add friends' usernames |
| 4. Install plugins | Recommended plugins |
| 5. Server icon | Upload or choose |
| 6. Public page | Configure join page (if public) |
| 7. Complete | Summary, next steps |

---

## Configuration Examples

```yaml
# config/minecraft-tools.php in automation section
automation:
  provisioning:
    enabled: true
    allow_user_request: true
    require_admin_approval: true
    templates: ['default', 'paper-survival', 'fabric-client']
    default_template: 'default'
    
  updates:
    plugins:
      check_interval: 3600
      auto_install: false        # Always require approval
      send_notification: true
    versions:
      check_interval: 21600
      notify_admins: true
      notify_players: true
    modpacks:
      check_interval: 43200
      
  backups:
    schedule: '0 2 * * *'       # Daily at 2am
    retention: 14
    include_world: false
    storage: 'local'
    
  notifications:
    email: true
    discord: false
    panel: true
    webhook_url: null
    
  monitoring:
    enabled: true
    check_interval: 300
    alert_channels: ['panel', 'email']
    
  audit:
    enabled: true
    retention_days: 90
    include_request_body: false  # Privacy
    
  cleanup:
    enabled: true
    schedule: '0 3 * * *'       # Daily at 3am
    expired_whitelist: true
    retention: 
      audit: 90
      backups: 30
      snapshots: 10
      logs: 180
```

---

## Scheduled Tasks

### Laravel Scheduler Tasks

| Task | Schedule | Job |
|------|----------|-----|
| Cleanup expired whitelists | Every hour | `CleanupExpiredWhitelistJob` |
| Check plugin updates | Every hour | `CheckPluginUpdatesJob` |
| Check version updates | Every 6 hours | `CheckVersionUpdatesJob` |
| Check modpack updates | Every 12 hours | `CheckModpackUpdatesJob` |
| Daily backups | Configurable | `CreateDailyBackupJob` |
| Backup retention cleanup | Every night | `CleanupBackupRetentionJob` |
| Server health check | Every 5 min | `HealthCheckJob` |
| Send expiry warnings | Every hour | `SendExpiryWarningsJob` |
| Send notifications queue | Every minute | `ProcessNotificationQueueJob` |
| Purge old audit logs | Weekly | `PurgeAuditLogsJob` |

---

## API Endpoints

### Automation Configuration
```
GET    /api/automation/config                 # Get automation config
PUT    /api/automation/config                 # Update automation config
GET    /api/automation/status                 # Get automation status
POST   /api/automation/run/{job}              # Manually trigger job
```

### Server Templates
```
GET    /api/templates                          # List templates
POST   /api/templates                          # Create template
GET    /api/templates/{id}                     # Get template
PUT    /api/templates/{id}                     # Update template
DELETE /api/templates/{id}                     # Delete template
POST   /api/servers/provision                  # Provision server from template
```

### Notifications
```
GET    /api/notifications                       # List notifications
GET    /api/notifications/{id}                 # Get notification
PUT    /api/notifications/preferences          # Update preferences
POST   /api/notifications/{id}/read            # Mark as read
DELETE /api/notifications/{id}                 # Delete notification
```

### Audit
```
GET    /api/audit                              # List audit log
GET    /api/audit/stats                        # Get audit statistics
GET    /api/audit/export                       # Export audit log
```

### Health/Monitoring
```
GET    /api/monitoring/status                  # Get health status
GET    /api/monitoring/history                 # Get health history
GET    /api/monitoring/alerts                  # Get active alerts
```

---

## Milestones

| Milestone | Feature | Target | Dependencies |
|-----------|---------|--------|--------------|
| M5.1 | Join request form | Week 10 | M4.2 |
| M5.2 | Auto-whitelist on approval | Week 10 | M5.1, M4.4 |
| M5.3 | Approval rules engine | Week 11 | M5.1 |
| M5.4 | Notification center | Week 11 | M5.1 |
| M5.5 | Discord bot integration | Week 12 | M5.4 |
| M5.6 | Server templates & provisioning | Week 13 | M6.1 |
| M5.7 | Audit logging | Week 13 | M5.1 |
| M5.8 | Scheduled backup automation | Week 14 | M5.4 |
| M5.9 | Update automation | Week 15 | M2.x, M3.x |
| M5.10 | Health monitoring | Week 16 | M5.9 |
| M5.11 | User onboarding wizard | Week 17 | M5.6 |

---

## Testing & Rollout

### Staging Tests
1. Provision test server from each template
2. Test join request → approval → whitelist flow
3. Test update notification pipeline
4. Test backup/restore cycle
5. Test audit log completeness
6. Test Discord integration

### Production Rollout
1. Enable read-only features first (audit, monitoring)
2. Enable notifications (informational)
3. Enable updates (approval-gated)
4. Enable provisioning (admin-only)
5. Enable user-facing automation (last)

### Rollback Plan
- Disable automation -> revert to manual mode
- All automation is configurable and can be turned off
- Clear recovery point via audit logs + snapshots
