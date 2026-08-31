# Minecraft Tools - Project Overview

## Vision Statement

Minecraft Tools is a comprehensive Pterodactyl panel extension that provides server administrators and users with powerful tools for managing Minecraft server utilities, mods, plugins, versions, and player access — all from within the Pterodactyl dashboard.

## Target Audience

- **Server Administrators**: Need centralized control over server versions, plugin management, player access, and configuration
- **Server Users/Players**: Need ability to manage their own servers (install plugins, switch versions, manage configs) and request access to servers
- **Hosting Providers**: Want to offer value-added Minecraft management tools to their customers

## Core Value Proposition

Replace the need for multiple disconnected tools (FTP clients, web interfaces, external APIs) with a single, integrated Pterodactyl extension that handles all Minecraft server management tasks.

---

## Feature Categories

### 1. Admin Panel Features
Full administrative control over all extension functionality, server-wide settings, and user management.

**Document**: `01-admin-panel-planning.md`

### 2. User Panel Features  
Per-server tools accessible to server owners and authorized users for day-to-day server management.

**Document**: `02-user-panel-planning.md`

### 3. Upstream Integrations
Connections to external APIs (Modrinth, CurseForge, SpigotMC, etc.) for plugin, modpack, and mod discovery.

**Document**: `03-upstream-integrations.md`

### 4. Whitelist & Access Management
Automated whitelist management, join requests, approval workflows, and access control.

**Document**: `04-whitelist-management.md`

### 5. Egg & Version Management
Integration with Pterodactyl eggs to install/switch Minecraft versions, server types (Paper, Fabric, Forge, etc.).

**Document**: `05-egg-version-management.md`

### 6. User Automation
Tools for automating user onboarding, server provisioning, and access requests.

**Document**: `06-user-automation.md`

---

## Technical Architecture

### Technology Stack

| Layer | Technology |
|-------|-----------|
| Frontend | Vue 3 + Vite + Bootstrap 5 |
| Backend | PHP 8.1+ / Laravel |
| API Framework | Blueprint Extensions for Pterodactyl |
| Database | MySQL/MariaDB (via Laravel migrations) |
| Caching | Redis (API responses, search results) |
| File Storage | Local/Cloud (icons, backups, configs) |

### Database Schema (High-Level)

```
minecraft_tools_plugin_configs
minecraft_tools_modpack_configs
minecraft_tools_icon_history
minecraft_tools_config_backups
minecraft_tools_player_notes
minecraft_tools_whitelist_entries
minecraft_tools_join_requests
minecraft_tools_user_access
minecraft_tools_server_versions
minecraft_tools_user_preferences
minecraft_tools_automation_rules
minecraft_tools_audit_log
```

### External API Integrations

| Service | Purpose | Rate Limits |
|---------|---------|-------------|
| Modrinth API v2 | Plugins, Modpacks | 100 req/min |
| CurseForge API v1 | Mods, Modpacks | 60 req/min |
| Spiget API | Spigot plugins | 60 req/min |
| PaperMC API | Paper builds | 60 req/min |
| Fabric Meta | Fabric loaders | 60 req/min |
| Technic API | Technic modpacks | 30 req/min |
| FTB API | FTB modpacks | 30 req/min |

---

## Milestones & Roadmap

### Phase 1: Foundation (Weeks 1-3)
**Goal**: Core infrastructure, admin panel, and basic version management

| Milestone | Deliverables | Status |
|-----------|-------------|--------|
| M1.1 | Extension scaffold, Service Provider, Routes, DB schema | Complete |
| M1.2 | Admin panel: Main dashboard, navigation | In Progress |
| M1.3 | Version management: List/install Paper builds | Pending |
| M1.4 | Basic config editor (server.properties) | Pending |

### Phase 2: Plugin Management (Weeks 4-6)
**Goal**: Full plugin lifecycle management with upstream integrations

| Milestone | Deliverables | Status |
|-----------|-------------|--------|
| M2.1 | Modrinth plugin search & install | Pending |
| M2.2 | CurseForge plugin search & install | Pending |
| M2.3 | SpigotMC plugin search & install | Pending |
| M2.4 | Plugin enable/disable/uninstall | Pending |
| M2.5 | Plugin config editor | Pending |

### Phase 3: Modpack Management (Weeks 7-9)
**Goal**: Modpack installation, switching, and management

| Milestone | Deliverables | Status |
|-----------|-------------|--------|
| M3.1 | Modrinth modpack search & install | Pending |
| M3.2 | CurseForge modpack search & install | Pending |
| M3.3 | Technic/FTB modpack support | Pending |
| M3.4 | Modpack version switching | Pending |
| M3.5 | Modpack backup/restore | Pending |

### Phase 4: Player & Whitelist Management (Weeks 10-12)
**Goal**: Complete player management with automated whitelist

| Milestone | Deliverables | Status |
|-----------|-------------|--------|
| M4.1 | Player listing & details | Pending |
| M4.2 | Whitelist add/remove/bulk | Pending |
| M4.3 | Ban management | Pending |
| M4.4 | OP management | Pending |
| M4.5 | Player inventory/enderchest viewer | Pending |

### Phase 5: User Automation (Weeks 13-15)
**Goal**: Automated onboarding, join requests, and access control

| Milestone | Deliverables | Status |
|-----------|-------------|--------|
| M5.1 | Join request form & approval workflow | Pending |
| M5.2 | Auto-whitelist on approved requests | Pending |
| M5.3 | Discord bot integration for requests | Pending |
| M5.4 | User permission tiers | Pending |
| M5.5 | Audit logging | Pending |

### Phase 6: Egg Integration (Weeks 16-18)
**Goal**: Install/switch server eggs from within the extension

| Milestone | Deliverables | Status |
|-----------|-------------|--------|
| M6.1 | Pterodactyl egg API integration | Pending |
| M6.2 | Egg search & browse | Pending |
| M6.3 | One-click egg installation | Pending |
| M6.4 | Version switching (client-side reinstall) | Pending |
| M6.5 | Egg templates (presets) | Pending |

### Phase 7: Polish & Release (Weeks 19-21)
**Goal**: Testing, documentation, and v1.0 release

| Milestone | Deliverables | Status |
|-----------|-------------|--------|
| M7.1 | Unit & integration tests | Pending |
| M7.2 | Frontend polish & responsive design | Pending |
| M7.3 | Documentation & README | Pending |
| M7.4 | Performance optimization & caching | Pending |
| M7.5 | v1.0 Release | Pending |

---

## Success Metrics

| Metric | Target |
|--------|--------|
| Plugin install time | < 30 seconds |
| Modpack install time | < 60 seconds |
| Version switch time | < 120 seconds |
| API response time | < 500ms (cached) |
| Admin panel load time | < 2 seconds |
| User panel load time | < 1.5 seconds |
| Test coverage | > 80% |

---

## Risks & Mitigations

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| External API rate limiting | High | High | Implement aggressive caching, queue-based processing |
| Pterodactyl API changes | High | Medium | Abstract API layer, monitor changelog |
| Large server file operations | Medium | Medium | Background jobs, progress tracking |
| User permission conflicts | Medium | Low | Clear permission model, admin overrides |
| Database growth (backups/logs) | Low | High | Automatic cleanup, configurable retention |

---

## Dependencies

- Pterodactyl Panel v1.11+
- Blueprint Extensions framework
- Redis (recommended for caching)
- PHP 8.1+ with extensions: gd, imagick, curl, json, mbstring
- Node.js 18+ (for frontend build)

---

## Document Index

| Document | Description |
|----------|-------------|
| `01-admin-panel-planning.md` | Admin panel features, views, and deliverables |
| `02-user-panel-planning.md` | User-facing server management tools |
| `03-upstream-integrations.md` | External API integrations (Modrinth, CurseForge, etc.) |
| `04-whitelist-management.md` | Whitelist automation, join requests, access control |
| `05-egg-version-management.md` | Pterodactyl egg integration, version switching |
| `06-user-automation.md` | Automated onboarding, permissions, audit logging |
