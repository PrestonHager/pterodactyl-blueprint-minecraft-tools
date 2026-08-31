# Egg & Version Management Planning

## Overview

This document details how Minecraft Tools integrates with Pterodactyl eggs to provide version management, server type switching, and one-click egg installation. The goal is to allow users to install different Minecraft versions (Paper, Fabric, Forge, etc.) and switch between them without leaving the panel.

---

## Pterodactyl Eggs Background

### What are Eggs?

Pterodactyl eggs are server installer templates that define:
- Which Docker image to use
- Installation script
- Start command
- Environment variables (variables panel)
- Configuration files to edit
- Default port mappings

### Minecraft Egg Example

```json
{
  "name": "Paper",
  "author": "pterodactyl",
  "description": "Paper Minecraft Server",
  "images": ["ghcr.io/pterodactyl/yolks:java_17"],
  "startup": "java -Xms128M -Xmx{{SERVER_MEMORY}} -jar {{SERVER_JARFILE}}",
  "config": {
    "files": {
      "server.properties": {
        "parser": "properties",
        "find": {
          "server-port": "{{SERVER_PORT}}"
        }
      }
    },
    "startup": {
      "done": "Done",
      "userInteraction": []
    },
    "stop": "stop"
  },
  "scripts": {
    "installation": {
      "script": "# ...",
      "container": "ghcr.io/pterodactyl/installers:alpine",
      "entrypoint": "ash"
    }
  },
  "variables": [
    {
      "name": "Server Jar File",
      "env_variable": "SERVER_JARFILE",
      "default_value": "paper.jar"
    }
  ]
}
```

---

## Version Types Supported

### Server Types

| Type | Egg | Notes |
|------|-----|-------|
| Vanilla | Vanilla Minecraft | Official Mojang server |
| Paper | Paper | Performance fork of Spigot |
| Spigot | Spigot | Most popular Bukkit fork |
| Purpur | Purpur | Paper fork with extras |
| Fabric | Fabric | Modding platform |
| Fabric + API | Fabric | With Fabric API |
| Forge | Forge | Legacy modding platform |
| NeoForge | NeoForge | Modern Forge successor |
| Quilt | Quilt | Fabric fork |
| Folia | Folia | Paper's multi-threaded fork |
| Velocity | Velocity (proxy) | Proxy/network server |
| BungeeCord | BungeeCord (proxy) | Legacy proxy |

### Mod Loader Compatibility

| Loader | Supports Mods | Supports Plugins | Game Versions |
|--------|--------------|------------------|---------------|
| Vanilla | ❌ | ❌ | All |
| Paper | ❌ | ✅ (Bukkit) | 1.8+ |
| Spigot | ❌ | ✅ (Bukkit) | 1.8+ |
| Purpur | ❌ | ✅ (Bukkit) | 1.16+ |
| Fabric | ✅ | ✅ (Fabric API) | 1.14+ |
| Forge | ✅ | ❌ | 1.1 - 1.20 |
| NeoForge | ✅ | ❌ | 1.20+ |
| Quilt | ✅ | ✅ (QSL) | 1.19+ |
| Folia | ❌ | ✅ (partial) | 1.20+ |

---

## Egg Integration Architecture

```
+------------------------+          +---------------------+
|                        |          |                     |
|  Minecraft Tools       |          |  Pterodactyl API    |
|  Extension             |          |                     |
|  |-------------------| |  REST    |  /api/application   |
|  | Egg Service       | |--------->|  /api/client        |
|  |-------------------| |          |                     |
|  | Version Service   | |          +---------------------+
|  |-------------------| |
|  | Install Service   | |
|  |-------------------| |
+------------------------+
         |
         v
   +------------+
   | Upstream   |
   | API        |
   | (PaperMC,  |
   | Fabric,    |
   | etc.)      |
   +------------+
```

### Pterodactyl API Endpoints Used

| Endpoint | Purpose |
|----------|---------|
| `GET /api/application/eggs` | List available eggs |
| `GET /api/application/eggs/{id}` | Egg details |
| `GET /api/application/eggs/{id}/variables` | Egg variables |
| `GET /api/application/nests` | List nests |
| `POST /api/application/servers` | Create server with egg |
| `PATCH /api/application/servers/{id}` | Update server egg |
| `POST /api/application/servers/{id}/reinstall` | Reinstall server |
| `GET /api/client/servers/{id}/startup` | Get startup config |
| `PUT /api/client/servers/{id}/startup/variable` | Update variable |

---

## Version Management Features

### 1. Version Browser

**Purpose**: Display available versions/builds for selection.

**Features**:
- List versions by server type
- Show compatible builds (Paper has multiple builds per version)
- Filter by game version (1.20, 1.21, etc.)
- Show recommended/latest version
- Version comparison table

**Components**:
| Component | Description |
|-----------|-------------|
| VersionTypeSelector | Tabs or dropdown for server type |
| VersionGrid | Cards/rows for versions |
| BuildSelector | Build picker for chosen version |
| VersionInfo | Details about selected version |
| CompareTable | Side-by-side version comparison |

---

### 2. Version Switching

**Purpose**: Switch server between versions/types.

**Flow**:
```
1. User selects target version and type
2. System validates compatibility with existing world/mods
3. System requests Pterodactyl egg change (if needed)
4. System sets SERVER_JARFILE variable
5. System triggers reinstall/restart
6. System monitors installation progress
7. User notified when complete
```

**Compatibility Checks**:
- World file compatibility (world version)
- Plugin compatibility (available plugins vs new server type)
- Mod compatibility (mod loader vs type)
- Modpack compatibility
- Resource pack compatibility

---

### 3. One-Click Egg Installation

**Purpose**: Install a new egg (Minecraft version/type) with one click.

**Features**:
- Browse available eggs (Paper, Fabric, Forge, etc.)
- Configure egg variables (memory, port, jar file)
- Preview installation script
- Install and automatically start server
- Monitor installation progress

**Components**:
| Component | Description |
|-----------|-------------|
| EggBrowser | List/search eggs |
| EggDetails | Full egg information |
| EggInstallForm | Configure variables |
| InstallProgress | Progress tracker |
| InstallLogs | Live install log |

---

### 4. Client-Side Reinstall (Game Version Changes)

**Purpose**: Update client-side game version for server members.

**Why**: When server switches versions, players need to match the server version to connect.

**Flow**:
```
1. Admin changes server version
2. System detects client version change needed
3. System sends notification to server members
4. Members see "Update required" in their panel view
5. Members click to see what version to install
6. System provides direct links/instructions
```

**Cutler - Client Version Display**:
- Show members current vs required version
- Provide server resource pack link if applicable
- Provide modpack download link if necessary
- Show "Join Server" instructions

---

### 5. Version Snapshots & Rollback

**Purpose**: Take version snapshots for rollback.

**Features**:
- Snapshot current version state (type, build, config)
- Save before version switch
- Rollback to previous version on failure

**Data Stored per Snapshot**:
```json
{
  "version": "1.20.4",
  "type": "paper",
  "build": 496,
  "jar_file": "paper-1.20.4.jar",
  "egg_id": 15,
  "variables": {
    "SERVER_JARFILE": "paper.jar",
    "SERVER_PORT": "25565"
  },
  "plugins": ["essentials.jar", "worldedit.jar"],
  "mods": ["fabric-api.jar", "sodium.jar"],
  "modpack": "skyfactory-5",
  "created_at": "2024-01-01T12:00:00Z"
}
```

---

## Database Schema

```sql
-- Server version history
CREATE TABLE minecraft_tools_server_versions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    server_id BIGINT UNSIGNED NOT NULL,
    server_type ENUM('vanilla', 'paper', 'spigot', 'purpur', 'fabric', 'forge', 'neoforge', 'quilt', 'folia') NOT NULL,
    version VARCHAR(16) NOT NULL,
    build VARCHAR(16) NULL,
    jar_file VARCHAR(128) NOT NULL,
    egg_id BIGINT UNSIGNED NOT NULL,
    variables JSON NULL,
    status ENUM('pending', 'installing', 'installed', 'failed', 'switching') DEFAULT 'pending',
    installed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_server_created (server_id, created_at),
    FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
);

-- Egg configurations
CREATE TABLE minecraft_tools_egg_configs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    server_id BIGINT UNSIGNED NOT NULL,
    egg_id BIGINT UNSIGNED NOT NULL,
    config JSON NOT NULL,
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_server_egg (server_id, egg_id),
    FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
);

-- Version change requests
CREATE TABLE minecraft_tools_version_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    server_id BIGINT UNSIGNED NOT NULL,
    requested_by BIGINT UNSIGNED NOT NULL,
    target_type VARCHAR(32) NOT NULL,
    target_version VARCHAR(16) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled', 'completed') DEFAULT 'pending',
    reason TEXT NULL,
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
);

-- Version snapshots
CREATE TABLE minecraft_tools_version_snapshots (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    server_id BIGINT UNSIGNED NOT NULL,
    version_id BIGINT UNSIGNED NULL,
    snapshot JSON NOT NULL,
    reason VARCHAR(255) NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_server_created (server_id, created_at),
    FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
);
```

---

## API Endpoints

### Egg Management
```
GET    /api/eggs                          # List available eggs
GET    /api/eggs/{id}                     # Get egg details
GET    /api/eggs/{id}/variables           # Get egg variables
GET    /api/eggs/{id}/scripts             # Get installation script
POST   /api/eggs/{id}/install             # Install egg on server
POST   /api/eggs/{id}/validate            # Validate config
GET    /api/eggs/nests                    # List nests
```

### Version Management
```
GET    /api/versions                       # List installed versions
POST   /api/versions                       # Install new version
GET    /api/versions/available             # Get available versions
GET    /api/versions/current               # Get current version
POST   /api/versions/switch                # Switch version
GET    /api/versions/builds/{version}      # Get builds for version
POST   /api/versions/{id}/reinstall        # Reinstall version
DELETE /api/versions/{id}                  # Remove version
POST   /api/versions/check-updates         # Check for updates
POST   /api/versions/rollback              # Rollback to snapshot
GET    /api/versions/snapshots             # List snapshots
POST   /api/versions/snapshots             # Create snapshot
```

### Egg Integration
```
GET    /api/integration/eggs/sync          # Sync eggs from Pterodactyl
POST   /api/integration/eggs/{id}/update   # Update egg config
GET    /api/integration/eggs/status        # Sync status
```

---

## Frontend Components

### Admin - Version Management View

```
+-----------------------------------------------+
|  Game Version Management                        |
+-----------------------------------------------+
|  CURRENT: Paper 1.20.4 (Build 496)  [Restart]  |
|                                             |
|  +-----------------------------------------+  |
|  | TYPES: [Vanilla][Paper][Purpur][Fabric]|  |
|  |         [Forge][NeoForge][Quilt][Folia]|  |
|  +-----------------------------------------+  |
|                                             |
|  AVAILABLE VERSIONS                          |
|  +-----------------------------------------+  |
|  | Version  | Build | Installed | Action   |  |
|  |----------|-------|-----------|----------|  |
|  | 1.20.4   | 496   | ✅        | [Switch] |  |
|  | 1.20.3   | 492   | ✅        | [Switch] |  |
|  | 1.20.2   | 480   | ✅        | [Switch] |  |
|  | 1.20.1   | 450   | ❌        | [Install] |  |
|  +-----------------------------------------+  |
|                                             |
|  [Check for Updates]  [Egg Settings]        |
+-----------------------------------------------+
```

### User - Client Version Display

```
+-----------------------------------------------+
|  Your server has moved to version 1.20.4       |
|                                             |
|  Current version: 1.20.2                      |
|  Required version: 1.20.4                     |
|                                             |
|  [Download required version]                 |
|                                             |
|  Steps:                                       |
|  1. Download the new version                  |
|  2. Backup your current version               |
|  3. Update your launcher                      |
|                                             |
|  Server type: Paper                           |
|  Modloader: Fabric + Fabric API               |
|                                             |
+-----------------------------------------------+
```

---

## Installation Workflow

### Version Install Job

```
1. User selects version type + version + build
2. Validate compatibility with current world
3. Create version snapshot (auto-backup)
4. If egg already installed:
   a. Set SERVER_JARFILE variable to new jar
   b. Trigger reinstall
5. If egg is new:
   a. Update server egg to new type
   b. Start installation via Pterodactyl API
6. Monitor installation (poll status / websocket)
7. On success:
   a. Update version history
   b. Notify user(s)
   c. Notify players of client version change
8. On failure:
   a. Rollback to snapshot
   b. Alert admin
```

### Compatibility Check Logic

```php
// Services/VersionCompatibilityService.php
class VersionCompatibilityService
{
    public function check(array $current, array $target): CompatibilityResult
    {
        $result = new CompatibilityResult();
        
        // Check world compatibility
        if (!$this->worldIsCompatible($current, $target)) {
            $result->addWarning('World data may not be compatible');
        }
        
        // Check plugins
        foreach ($this->getInstalledPlugins() as $plugin) {
            if (!$plugin->supportsVersion($target['version'])) {
                $result->addWarning("Plugin {$plugin->name} may not support version {$target['version']}");
            }
        }
        
        // Check mods
        foreach ($this->getInstalledMods() as $mod) {
            if (!$mod->supportsLoader($target['type'])) {
                $result->addError("Mod {$mod->name} does not support {$target['type']}");
            }
        }
        
        // Check modpack
        if ($this->getCurrentModpack() && !$this->getCurrentModpack()->supportsVersion($target['version'])) {
            $result->addError('Current modpack does not support this version');
        }
        
        return $result;
    }
}
```

---

## Preconfigured Egg Overrides

### Vanilla Egg
```yaml
version: 1.20.4
type: vanilla
startup: "java -Xms128M -Xmx{{SERVER_MEMORY}} -jar server.jar nogui"
variables:
  SERVER_JARFILE: server.jar
  DIFFICULTY: normal
  MODE: survival
```

### Paper Egg Override
```yaml
version: 1.20.4
type: paper
build: 496
startup: "java -Xms128M -Xmx{{SERVER_MEMORY}} -jar {{SERVER_JARFILE}}"
variables:
  SERVER_JARFILE: paper.jar
  PAPER_VERSION: 1.20.4
  PAPER_BUILD: 496
```

### Fabric Egg Override
```yaml
version: 1.20.4
type: fabric
loader_version: 0.15.7
startup: "java -Xms128M -Xmx{{SERVER_MEMORY}} -jar fabric-server-launch.jar nogui"
variables:
  SERVER_JARFILE: fabric-server-launch.jar
  FABRIC_LOADER_VERSION: 0.15.7
```

---

## Milestones

| Milestone | Feature | Target | Dependencies |
|-----------|---------|--------|--------------|
| M6.1 | Pterodactyl egg API client | Week 13 | M1.1 |
| M6.2 | Version browser (Paper) | Week 14 | M6.1, PaperAPI |
| M6.3 | Version switching (same egg) | Week 14 | M6.2 |
| M6.4 | Egg switching (different types) | Week 15 | M6.3 |
| M6.5 | Compatibility checks | Week 15 | M6.4 |
| M6.6 | One-click egg installation | Week 16 | M6.1 |
| M6.7 | Client version notifications | Week 16 | M6.2 |
| M6.8 | Snapshots & rollback | Week 17 | M6.4 |

---

## Testing Strategy

### Unit Tests
- Version URL construction
- Compatibility check logic
- Egg config parsing

### Integration Tests (Mock Pterodactyl)
- Version install flow
- Counterfactual egg switching
- Rollback on failure

### Manual Tests
- Real Paper version switch
- Fabric to Forge switch
- Version failure recovery

---

## Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| World corruption on version change | High | Mandatory snapshot before switch |
| Plugin incompatibility | Medium | Compatibility pre-check + warning |
| Pterodactyl API rate limits | Medium | Throttle API calls, queue operations |
| Egg installation failures | Medium | Retry logic, detailed error logs |
| Long installation times | Medium | Progress tracking, background jobs |
| Multiple concurrent changes | High | Lock server during transitions |

---

## Expansion Ideas

| Feature | Priority |
|---------|----------|
| Auto egg provisioning for new servers | High |
| Drag-and-drop world migration between versions | Medium |
| Server type conversion tool (Paper↔Fabric) | Medium |
| Performance benchmarking between types | Low |
| Scheduled version maintenance windows | Low |
| Multi-server simultaneous version updates | Low |
