# User Panel Planning Document

## Overview

The user panel provides server owners and authorized users with per-server management tools accessible directly from the Pterodactyl server dashboard. Unlike the admin panel (which manages all servers), the user panel focuses on individual server management with appropriate permission controls.

---

## Access Model

### Permission Levels

| Level | Name | Description |
|-------|------|-------------|
| 0 | Guest | Can view server info, request access |
| 1 | User | Basic server management (plugins, config) |
| 2 | Moderator | Player management, whitelist |
| 3 | Admin | Full server control, version switching |
| 4 | Owner | Server settings, egg changes, billing |

### Permission Matrix

| Feature | Guest | User | Moderator | Admin | Owner |
|---------|-------|------|-----------|-------|-------|
| View server info | ✅ | ✅ | ✅ | ✅ | ✅ |
| Install plugins | ❌ | ✅ | ✅ | ✅ | ✅ |
| Enable/disable plugins | ❌ | ✅ | ✅ | ✅ | ✅ |
| Edit plugin config | ❌ | ✅ | ✅ | ✅ | ✅ |
| View players | ❌ | ❌ | ✅ | ✅ | ✅ |
| Whitelist players | ❌ | ❌ | ✅ | ✅ | ✅ |
| Ban players | ❌ | ❌ | ✅ | ✅ | ✅ |
| Switch versions | ❌ | ❌ | ❌ | ✅ | ✅ |
| Change egg | ❌ | ❌ | ❌ | ❌ | ✅ |
| Manage server settings | ❌ | ❌ | ❌ | ❌ | ✅ |

---

## User Panel Views

### 1. Server Dashboard Widget

**Purpose**: Quick overview added to existing Pterodactyl server dashboard.

**Location**: Injected into Pterodactyl's server page via Blueprint component.

**Components**:
| Component | Description | Priority |
|-----------|-------------|----------|
| QuickStats | Online players, plugins count, version | High |
| QuickActions | Common action buttons | High |
| RecentActivity | Latest server events | Medium |

**Deliverables**:
- [ ] Dashboard widget component
- [ ] Quick stats display
- [ ] Quick action buttons
- [ ] Recent activity feed

---

### 2. Plugin Manager (User View)

**Purpose**: Simplified plugin management for server owners.

**Features**:

#### 2.1 Plugin List
- Show installed plugins with status
- Quick enable/disable toggle
- One-click uninstall

#### 2.2 Install Plugins
- Search Modrinth/CurseForge
- One-click install
- Install from URL or file upload

#### 2.3 Plugin Settings
- Basic config editor
- Common settings presets

**Components**:
| Component | Description | Priority |
|-----------|-------------|----------|
| UserPluginList | Simplified plugin table | High |
| UserPluginInstall | Quick install modal | High |
| UserPluginToggle | Enable/disable switch | High |
| UserPluginConfig | Basic config editor | Medium |

**API Endpoints**:
```
GET    /api/user/servers/{server}/plugins           # List plugins
POST   /api/user/servers/{server}/plugins           # Install plugin
DELETE /api/user/servers/{server}/plugins/{plugin}  # Uninstall plugin
POST   /api/user/servers/{server}/plugins/{plugin}/toggle  # Toggle plugin
GET    /api/user/servers/{server}/plugins/{plugin}/config  # Get config
PUT    /api/user/servers/{server}/plugins/{plugin}/config  # Update config
```

**Deliverables**:
- [ ] Simplified plugin list
- [ ] Quick install workflow
- [ ] Enable/disable toggle
- [ ] Basic config editor
- [ ] Update notifications

---

### 3. Version Manager (User View)

**Purpose**: View current version and request version changes.

**Features**:

#### 3.1 Current Version Display
- Show installed version and type
- Display available updates

#### 3.2 Version Request
- Request version change (admin approval)
- View pending requests
- Cancel pending requests

**Components**:
| Component | Description | Priority |
|-----------|-------------|----------|
| CurrentVersionDisplay | Version info card | High |
| UpdateAvailable | Update notification | Medium |
| VersionRequestForm | Request change form | Medium |
| RequestStatus | Pending request status | Low |

**API Endpoints**:
```
GET    /api/user/servers/{server}/versions/current   # Get current version
GET    /api/user/servers/{server}/versions/available  # Get available
POST   /api/user/servers/{server}/versions/request    # Request change
GET    /api/user/servers/{server}/versions/requests   # View requests
DELETE /api/user/servers/{server}/versions/requests/{id}  # Cancel request
```

**Deliverables**:
- [ ] Current version display
- [ ] Update notification
- [ ] Version change request form
- [ ] Request status tracking

---

### 4. Player Manager (User View)

**Purpose**: Manage server players for authorized users.

**Features**:

#### 4.1 Player List
- View all players
- Search by username
- Filter by status

#### 4.2 Player Actions
- Whitelist/unwhitelist
- Ban/unban (with reason)
- Kick players

#### 4.3 Player Details
- View join date, last seen
- Basic inventory view

**Components**:
| Component | Description | Priority |
|-----------|-------------|----------|
| UserPlayerList | Player table with search | High |
| UserPlayerActions | Action dropdown | High |
| UserPlayerDetail | Player info modal | Medium |
| UserInventoryView | Basic inventory view | Low |

**API Endpoints**:
```
GET    /api/user/servers/{server}/players              # List players
POST   /api/user/servers/{server}/players/{player}/whitelist  # Whitelist
POST   /api/user/servers/{server}/players/{player}/unwhitelist  # Unwhitelist
POST   /api/user/servers/{server}/players/{player}/ban  # Ban
POST   /api/user/servers/{server}/players/{player}/unban  # Unban
POST   /api/user/servers/{server}/players/{player}/kick  # Kick
GET    /api/user/servers/{server}/players/{player}     # Get details
```

**Deliverables**:
- [ ] Player list with search
- [ ] Whitelist management
- [ ] Ban management
- [ ] Kick functionality
- [ ] Player details view

---

### 5. Config Editor (User View)

**Purpose**: Edit common server configuration files.

**Features**:

#### 5.1 Safe Config Files
- Only expose safe config files (server.properties, bukkit.yml)
- Hide sensitive files (rcon passwords, etc.)

#### 5.2 Simplified Editor
- Form-based editor for common settings
- Raw editor for advanced users
- Validation before save

**Components**:
| Component | Description | Priority |
|-----------|-------------|----------|
| UserConfigList | Safe config file list | High |
| UserConfigForm | Form-based editor | High |
| UserConfigRaw | Raw text editor | Medium |
| UserConfigSave | Save with validation | High |

**API Endpoints**:
```
GET    /api/user/servers/{server}/config              # List safe configs
GET    /api/user/servers/{server}/config/{file}        # Get config
PUT    /api/user/servers/{server}/config/{file}        # Update config
POST   /api/user/servers/{server}/config/{file}/validate  # Validate
```

**Deliverables**:
- [ ] Safe config file list
- [ ] Form-based config editor
- [ ] Raw editor option
- [ ] Config validation
- [ ] Backup before save

---

### 6. Server Icon (User View)

**Purpose**: Manage server icon for authorized users.

**Features**:

#### 6.1 Current Icon
- Display current icon
- Show upload requirements

#### 6.2 Icon Operations
- Upload new icon
- Use templates
- Delete icon

**Components**:
| Component | Description | Priority |
|-----------|-------------|----------|
| UserIconPreview | Current icon display | High |
| UserIconUpload | Upload with preview | High |
| UserIconTemplates | Template selector | Medium |

**API Endpoints**:
```
GET    /api/user/servers/{server}/icon                # Get current icon
POST   /api/user/servers/{server}/icon                # Upload icon
DELETE /api/user/servers/{server}/icon                # Delete icon
GET    /api/user/servers/{server}/icon/templates      # Get templates
POST   /api/user/servers/{server}/icon/templates/{id}/apply  # Apply template
```

**Deliverables**:
- [ ] Icon preview display
- [ ] Icon upload
- [ ] Template selection
- [ ] Icon deletion

---

### 7. Modpack Manager (User View)

**Purpose**: Manage modpacks for authorized users.

**Features**:

#### 7.1 Current Modpack
- Show installed modpack and version
- Display mod list

#### 7.2 Modpack Operations
- Search and install modpacks
- Switch modpacks
- Update modpack

**Components**:
| Component | Description | Priority |
|-----------|-------------|----------|
| UserModpackInfo | Current modpack display | High |
| UserModpackSearch | Search modpacks | High |
| UserModpackInstall | Install workflow | Medium |
| UserModpackModList | Mod list viewer | Low |

**API Endpoints**:
```
GET    /api/user/servers/{server}/modpacks/current    # Get current modpack
GET    /api/user/servers/{server}/modpacks             # List modpacks
POST   /api/user/servers/{server}/modpacks             # Install modpack
POST   /api/user/servers/{server}/modpacks/{modpack}/switch  # Switch modpack
GET    /api/user/servers/{server}/modpacks/{modpack}/mods  # Get mod list
```

**Deliverables**:
- [ ] Current modpack display
- [ ] Modpack search and install
- [ ] Modpack switching
- [ ] Mod list viewer

---

## User Panel Layout (Injected into Pterodactyl)

```
+------------------------------------------+
|  PTERODACTYL HEADER                      |
+------------------------------------------+
|  SERVER TABS: Console | Files | ... | Minecraft Tools |
+------------------------------------------+
|                                          |
|  MINECRAFT TOOLS CONTENT:                |
|  +------------------------------------+  |
|  | Quick Stats | Version | Plugins   |  |
|  +------------------------------------+  |
|  | [Feature-specific content]         |  |
|  |                                    |  |
|  |                                    |  |
|  +------------------------------------+  |
|                                          |
+------------------------------------------+
|  PTERODACTYL FOOTER                      |
+------------------------------------------+
```

---

## Integration with Pterodactyl

### Blueprint Component Registration

```javascript
// dashboard/components/MinecraftToolsSection.tsx
export default {
  name: 'MinecraftToolsSection',
  props: {
    server: Object,
    user: Object,
  },
  // Component definition
};
```

### Route Prefix

All user panel routes use:
```
/api/user/servers/{server}/minecraft-tools/...
```

### Middleware

```php
Route::middleware(['auth', 'minecraft-tools.server'])->group(function () {
    // User panel routes
});
```

---

## Milestones

| Milestone | Features | Target | Dependencies |
|-----------|----------|--------|--------------|
| M2.1 | Dashboard widget, quick stats | Week 4 | M1.1 |
| M2.2 | Plugin manager (user view) | Week 5 | M2.1 |
| M2.3 | Version manager (user view) | Week 6 | M2.1 |
| M2.4 | Player manager (user view) | Week 7 | M2.1 |
| M2.5 | Config editor (user view) | Week 8 | M2.1 |
| M2.6 | Icon manager (user view) | Week 9 | M2.1 |
| M2.7 | Modpack manager (user view) | Week 10 | M2.1 |

---

## Technical Notes

### Security Considerations

1. **Input Validation**: All user inputs must be sanitized
2. **Permission Checks**: Verify user has required permission before each action
3. **Rate Limiting**: Prevent abuse of search/install endpoints
4. **Audit Logging**: Log all user actions for accountability
5. **File Upload Validation**: Validate icon uploads (size, dimensions, type)

### Performance

1. **Cache Search Results**: Cache API responses for 5-10 minutes
2. **Paginate Lists**: Use cursor-based pagination for large lists
3. **Debounce Search**: Debounce search inputs (300ms)
4. **Lazy Load Modals**: Load modal content on demand
5. **Optimistic Updates**: Update UI immediately, sync in background

### Error Handling

1. **Graceful Degradation**: Handle API failures gracefully
2. **User Feedback**: Show loading states and error messages
3. **Retry Logic**: Implement retry for transient failures
4. **Offline Support**: Cache last known state for offline viewing
