# Minecraft Tools - Planning Documents Index

This directory contains the complete planning documentation for the Minecraft Tools Pterodactyl extension. Each document covers a specific feature area with detailed specs, milestones, and deliverables.

---

## Document Index

| # | Document | Description |
|---|----------|-------------|
| 00 | [Project Overview](00-project-overview.md) | Vision, architecture, timeline, milestones, risks |
| 01 | [Admin Panel Planning](01-admin-panel-planning.md) | Admin views, API endpoints, components, deliverables |
| 02 | [User Panel Planning](02-user-panel-planning.md) | Per-server user tools, permissions, integration |
| 03 | [Upstream Integrations](03-upstream-integrations.md) | Modrinth, CurseForge, SpigotMC, PaperMC, Fabric APIs |
| 04 | [Whitelist Management](04-whitelist-management.md) | Join requests, approvals, tiers, Discord, sync |
| 05 | [Egg & Version Management](05-egg-version-management.md) | Pterodactyl eggs, version switching, snapshots |
| 06 | [User Automation](06-user-automation.md) | Provisioning, updates, notifications, backups, audit |

---

## Quick Reference

### Development Phases

| Phase | Weeks | Focus | Documents |
|-------|-------|-------|-----------|
| 1 | 1-3 | Foundation, scaffold, admin dashboard | 01 |
| 2 | 4-6 | Plugin management, Modrinth/CurseForge | 01, 03 |
| 3 | 7-9 | Modpack management | 01, 03 |
| 4 | 10-12 | Player & whitelist management | 01, 02, 04 |
| 5 | 13-15 | User automation, join requests | 04, 06 |
| 6 | 16-18 | Egg integration, version switching | 03, 05 |
| 7 | 19-21 | Polish, testing, release | All |

### User Roles

| Role | Scope | Key Documents |
|------|-------|---------------|
| System Admin | All servers, all features | 01, 06 |
| Server Owner | Their servers, full control | 02, 05 |
| Moderator | Players, whitelist | 02, 04 |
| User | Plugins, configs | 02 |
| Guest | View, join requests | 04 |

---

## How to Use These Documents

1. **Start** with the [Project Overview](00-project-overview.md) to understand the full scope.
2. **Pick a phase** from the roadmap and read the corresponding feature documents.
3. **Reference** API endpoints, schemas, and component lists for implementation.
4. **Track** milestone progress against the deliverables checklists in each document.

## Status Tracking

Each document includes milestone tables with status markers. As features are implemented, update the status from `Pending` to `In Progress` to `Complete`.

## Related

- Main project README: [`../../README.md`](../../README.md)
- Source code: [`../../src/`](../../src/)
