# Admin Panel Planning Document

## Overview

The admin panel provides server administrators with comprehensive control over all Minecraft Tools extension functionality. It serves as the central management interface for server configuration, player management, plugin/modpack installation, and system-wide settings.

---

## Admin Panel Views

### 1. Main Dashboard (`view.blade.php`)

**Purpose**: Overview of all extension features with quick access to each section.

**Components**:
- Feature cards (Plugins, Versions, Players, Modpacks, Config, Icon)
- Quick stats (total servers managed, active players, installed plugins)
- Recent activity feed
- System health indicators

**Deliverables**:
- [ ] Dashboard layout with Bootstrap grid
- [ ] Feature cards with icons and descriptions
- [ ] Stats widgets with real-time data
- [ ] Activity feed component
- [ ] Mobile responsive design

---

### 2. Plugin Management (`plugins.blade.php`)

**Purpose**: Install, configure, enable/disable, and remove server plugins.

**Features**:

#### 2.1 Plugin Discovery
- Search plugins across multiple sources (Modrinth, CurseForge, SpigotMC)
- Filter by category, popularity, compatibility
- Plugin details modal (description, author, versions, dependencies)

#### 2.2 Installed Plugins
- List all installed plugins with status (enabled/disabled)
- Quick toggle enable/disable
- View/edit plugin configuration
- Update available indicator

#### 2.3 Plugin Actions
- Install from search results
- Install from local file upload
- Enable/disable toggle
- Uninstall with confirmation
- View plugin logs (if available)
- Check for updates

**Components**:
| Component | Description | Priority |
|-----------|-------------|----------|
| PluginSearch | Search input with source selector | High |
| PluginList | Table of installed plugins | High |
| PluginCard | Card for discovered plugins | High |
| PluginDetails | Modal with full plugin info | Medium |
| PluginConfig | Config editor for plugin files | Medium |
| PluginLogs | Log viewer for plugin output | Low |
| PluginBulkActions | Multi-select actions | Low |

**API Endpoints**:
```
GET    /plugins                    # List installed plugins
POST   /plugins                    # Install plugin
GET    /plugins/{id}               # Get plugin details
PUT    /plugins/{id}               # Update plugin settings
DELETE /plugins/{id}               # Uninstall plugin
POST   /plugins/{id}/enable        # Enable plugin
POST   /plugins/{id}/disable       # Disable plugin
GET    /plugins/{id}/config        # Get plugin config
PUT    /plugins/{id}/config        # Update plugin config
GET    /plugins/{id}/logs          # Get plugin logs
POST   /plugins/search             # Search upstream sources
POST   /plugins/check-updates      # Check for updates
```

**Deliverables**:
- [ ] Plugin search with multi-source support
- [ ] Plugin install/uninstall workflow
- [ ] Plugin enable/disable toggle
- [ ] Plugin configuration editor
- [ ] Plugin update notifications
- [ ] Bulk plugin operations
- [ ] Plugin dependency resolution display

---

### 3. Version Management (`versions.blade.php`)

**Purpose**: Manage Minecraft server versions and server types (Paper, Fabric, Forge, etc.).

**Features**:

#### 3.1 Version Browser
- List available versions by server type
- Filter by Minecraft version (1.20, 1.21, etc.)
- Show compatible builds for each version

#### 3.2 Current Version Display
- Show currently installed version and type
- Display build number and installation date
- Show available updates

#### 3.3 Version Operations
- Install new version (triggers egg reinstall)
- Switch between server types
- Reinstall current version
- Remove cached versions

**Components**:
| Component | Description | Priority |
|-----------|-------------|----------|
| CurrentVersion | Current version display card | High |
| VersionList | Table of available versions | High |
| VersionSearch | Search/filter versions | High |
| VersionTypeSelector | Server type filter | High |
| VersionInstall | Install modal with options | High |
| VersionSwitch | Switch confirmation modal | Medium |
| UpdateChecker | Check for updates panel | Medium |

**API Endpoints**:
```
GET    /versions                   # List installed versions
POST   /versions                   # Install version
GET    /versions/{version}         # Get version details
DELETE /versions/{version}         # Remove version
POST   /versions/{version}/reinstall  # Reinstall version
GET    /versions/available         # Get available versions
GET    /versions/current           # Get current version
POST   /versions/switch            # Switch version
GET    /versions/builds/{version}  # Get available builds
POST   /versions/check-updates     # Check for updates
```

**Deliverables**:
- [ ] Version browser with type filtering
- [ ] Current version display
- [ ] Version installation workflow
- [ ] Version switching with confirmation
- [ ] Update checking and notifications
- [ ] Build selection for Paper/Spigot
- [ ] Client-side reinstall integration

---

### 4. Player Management (`players.blade.php`)

**Purpose**: Manage server players, whitelist, bans, and operator status.

**Features**:

#### 4.1 Player Listing
- Search players by username/UUID
- Filter by status (online, whitelisted, banned, OP)
- Bulk selection for mass actions

#### 4.2 Player Details
- View player UUID, join date, last seen
- View/play inventory and enderchest
- View player logs and history
- Add notes to player profile

#### 4.3 Player Actions
- Whitelist add/remove
- Ban/unban with reason and expiry
- Kick with reason
- OP/deop
- View inventory (read-only)
- View enderchest (read-only)

**Components**:
| Component | Description | Priority |
|-----------|-------------|----------|
| PlayerList | Searchable player table | High |
| PlayerFilters | Status filter buttons | High |
| PlayerDetails | Player detail modal | High |
| PlayerActions | Action dropdown menu | High |
| InventoryViewer | Grid-based inventory display | Medium |
| EnderchestViewer | Grid-based enderchest display | Medium |
| PlayerLogs | Log history viewer | Medium |
| PlayerNotes | Notes textarea | Low |
| BulkActions | Multi-select operations | Low |

**API Endpoints**:
```
GET    /players                    # List players
POST   /players                    # Perform player action
GET    /players/{player}           # Get player details
PUT    /players/{player}           # Update player notes
DELETE /players/{player}           # Remove player data
POST   /players/{player}/ban       # Ban player
POST   /players/{player}/unban     # Unban player
POST   /players/{player}/kick      # Kick player
POST   /players/{player}/whitelist # Whitelist player
POST   /players/{player}/unwhitelist  # Remove from whitelist
POST   /players/{player}/op        # Give OP
POST   /players/{player}/deop      # Remove OP
GET    /players/{player}/logs      # Get player logs
GET    /players/{player}/inventory # Get inventory
GET    /players/{player}/enderchest # Get enderchest
GET    /players/online             # Get online players
GET    /players/banned             # Get banned players
GET    /players/whitelisted        # Get whitelisted players
GET    /players/ops                # Get operators
```

**Deliverables**:
- [ ] Player search and filtering
- [ ] Player detail modal with all info
- [ ] Whitelist management (add/remove/bulk)
- [ ] Ban management with reason/expiry
- [ ] OP management
- [ ] Inventory viewer (grid layout)
- [ ] Enderchest viewer
- [ ] Player log viewer
- [ ] Player notes system
- [ ] Bulk player operations

---

### 5. Modpack Management (`modpacks.blade.php`)

**Purpose**: Install, configure, and manage Minecraft modpacks.

**Features**:

#### 5.1 Modpack Discovery
- Search modpacks across Modrinth, CurseForge, Technic, FTB
- Filter by category, mod loader, Minecraft version
- View modpack details (mods list, description, screenshots)

#### 5.2 Installed Modpacks
- List installed modpacks with version info
- Current active modpack indicator
- Mod list for installed modpacks

#### 5.3 Modpack Operations
- Install modpack from search
- Switch between modpacks
- Update modpack to latest version
- Backup/restore modpack configuration
- View modpack files

**Components**:
| Component | Description | Priority |
|-----------|-------------|----------|
| ModpackSearch | Search with source selector | High |
| ModpackList | Installed modpacks table | High |
| ModpackCard | Discovered modpack cards | High |
| ModpackDetails | Full modpack info modal | High |
| ModpackMods | Mod list for modpack | Medium |
| ModpackFiles | File browser | Medium |
| ModpackBackup | Backup/restore UI | Low |

**API Endpoints**:
```
GET    /modpacks                   # List installed modpacks
POST   /modpacks                   # Install modpack
GET    /modpacks/{modpack}         # Get modpack details
PUT    /modpacks/{modpack}         # Update modpack settings
DELETE /modpacks/{modpack}         # Uninstall modpack
POST   /modpacks/{modpack}/install # Install specific version
POST   /modpacks/{modpack}/uninstall  # Uninstall modpack
POST   /modpacks/{modpack}/update  # Update to latest
GET    /modpacks/{modpack}/versions # Get available versions
POST   /modpacks/{modpack}/switch-version  # Switch version
GET    /modpacks/{modpack}/config  # Get modpack config
PUT    /modpacks/{modpack}/config  # Update modpack config
GET    /modpacks/available         # Get available modpacks
POST   /modpacks/search            # Search modpacks
GET    /modpacks/categories        # Get categories
GET    /modpacks/{modpack}/files   # Get modpack files
POST   /modpacks/{modpack}/backup  # Create backup
POST   /modpacks/{modpack}/restore # Restore backup
```

**Deliverables**:
- [ ] Modpack search with multi-source support
- [ ] Modpack install/uninstall workflow
- [ ] Modpack version switching
- [ ] Modpack update notifications
- [ ] Mod list viewer
- [ ] Modpack backup/restore
- [ ] Category filtering

---

### 6. Config Editor (`config.blade.php`)

**Purpose**: Edit server configuration files with syntax highlighting and validation.

**Features**:

#### 6.1 File Browser
- List all config files (server.properties, spigot.yml, etc.)
- Show file type and last modified date
- Quick search for files

#### 6.2 Editor
- Syntax-highlighted editor (YAML, JSON, TOML, Properties)
- Real-time validation
- Auto-save drafts
- Undo/redo support

#### 6.3 Config Operations
- Save config with validation
- Backup current config
- Restore from backup
- Apply templates
- Export/import configs

**Components**:
| Component | Description | Priority |
|-----------|-------------|----------|
| FileList | Config file browser sidebar | High |
| CodeEditor | Ace/Monaco editor component | High |
| ValidationPanel | Validation errors display | High |
| BackupList | Backup history modal | Medium |
| TemplateSelector | Template picker modal | Medium |
| ConfigToolbar | Save/validate/backup buttons | High |

**API Endpoints**:
```
GET    /config                     # List all configs
POST   /config                     # Update config
GET    /config/{file}              # Get config file
PUT    /config/{file}              # Update config file
GET    /config/{file}/raw          # Get raw content
PUT    /config/{file}/raw          # Update raw content
GET    /config/{file}/backup       # Get backup history
POST   /config/{file}/restore      # Restore from backup
GET    /config/files               # List config files
POST   /config/validate            # Validate config
GET    /config/templates           # Get templates
POST   /config/templates/{template}/apply  # Apply template
```

**Deliverables**:
- [ ] Config file browser with type indicators
- [ ] Syntax-highlighted code editor
- [ ] Real-time validation
- [ ] Config backup/restore
- [ ] Template system
- [ ] Config export/import
- [ ] Auto-save functionality

---

### 7. Server Icon (`icon.blade.php`)

**Purpose**: Manage server icon (MOTD image).

**Features**:

#### 7.1 Current Icon Display
- Show current icon or default placeholder
- Display icon dimensions and file size

#### 7.2 Icon Operations
- Upload custom icon (64x64 PNG/JPEG)
- Generate icon from text with custom colors
- Choose from pre-made templates
- Delete icon (revert to default)

#### 7.3 Icon History
- View previously used icons
- Restore from history

**Components**:
| Component | Description | Priority |
|-----------|-------------|----------|
| CurrentIcon | Current icon preview | High |
| UploadModal | File upload with preview | High |
| GenerateModal | Text-to-icon generator | Medium |
| TemplateGrid | Template selection grid | Medium |
| HistoryGrid | Icon history with restore | Low |

**API Endpoints**:
```
GET    /icon                       # Get current icon
POST   /icon                       # Upload icon
DELETE /icon                       # Delete icon
GET    /icon/preview               # Preview uploaded icon
POST   /icon/generate              # Generate icon
GET    /icon/templates             # Get templates
POST   /icon/templates/{template}/apply  # Apply template
GET    /icon/history               # Get icon history
POST   /icon/history/{id}/restore  # Restore from history
```

**Deliverables**:
- [ ] Current icon display
- [ ] Icon upload with validation
- [ ] Icon generator with text/colors
- [ ] Template selection grid
- [ ] Icon history and restore

---

## Admin Panel Layout

```
+------------------------------------------+
|  HEADER: Minecraft Tools | User Menu    |
+------------------------------------------+
|  SIDEBAR       |  CONTENT AREA           |
|  - Dashboard   |                         |
|  - Plugins     |  [Feature-specific      |
|  - Versions    |   content here]         |
|  - Players     |                         |
|  - Modpacks    |                         |
|  - Config      |                         |
|  - Icon        |                         |
|  - Settings    |                         |
+------------------------------------------+
|  FOOTER: Version | Links                 |
+------------------------------------------+
```

---

## Milestones

| Milestone | Features | Target | Dependencies |
|-----------|----------|--------|--------------|
| M1.2 | Dashboard layout, navigation | Week 1 | M1.1 (scaffold) |
| M1.3 | Version management | Week 2 | M1.2 |
| M1.4 | Basic config editor | Week 3 | M1.2 |
| M2.1-2.3 | Plugin search & install | Week 5 | M1.2, API integrations |
| M3.1-3.2 | Modpack search & install | Week 7 | M1.2, API integrations |
| M4.1-4.4 | Player management | Week 9 | M1.2 |
| M5.1-5.2 | Whitelist management | Week 11 | M4.1 |
| M6.1-6.3 | Egg integration | Week 13 | M1.3 |

---

## Technical Notes

### Frontend Architecture
- Use Vue 3 components for reactive UI
- Bootstrap 5 for layout and styling
- Ace Editor for config editing
- Custom components for inventory/grid displays

### State Management
- Local component state for UI
- API calls for data persistence
- Consider Pinia for complex state (plugin configs, player lists)

### Performance
- Paginate large lists (players, plugins)
- Cache API responses (Redis)
- Lazy load modals and heavy components
- Debounce search inputs
