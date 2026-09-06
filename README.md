# Minecraft Tools - Pterodactyl Extension

A comprehensive Pterodactyl panel extension for managing Minecraft server utilities.

## Features

- **Plugin Management** - Install, update, enable/disable plugins from Spigot, Modrinth, and CurseForge
- **Game Version Management** - Install and switch between Minecraft versions (Vanilla, Paper, Spigot, Purpur, Fabric, Forge, Quilt, NeoForge)
- **Player Management** - Manage players, whitelist, bans, operators, view inventories, enderchests, and logs
- **Modpack Management** - Install and manage modpacks from Modrinth, CurseForge, Technic, and FTB
- **Config Editor** - Edit server configuration files with syntax highlighting and validation
- **Server Icon** - Upload, generate, or choose from templates for your server icon

## Requirements

- Pterodactyl Panel 1.11+
- PHP 8.1+
- Blueprint Extensions 1.0+

## Installation

### Via Composer

```bash
composer require pterodactyl/minecraft-tools
```

### Manual Installation

1. Copy the `src/Extensions/MinecraftTools` directory to your Pterodactyl installation at `app/Extensions/MinecraftTools`
2. Run migrations:
   ```bash
   php artisan migrate --path=vendor/pterodactyl/minecraft-tools/src/Extensions/MinecraftTools/Database/Migrations
   ```
3. Publish assets:
   ```bash
   php artisan vendor:publish --tag=minecraft-tools-assets
   ```
4. Clear cache:
   ```bash
   php artisan view:clear
   php artisan config:clear
   ```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=minecraft-tools-config
```

Edit `config/minecraft-tools.php` to configure API keys and settings:

```php
'api' => [
    'curseforge' => [
        'api_key' => env('MINECRAFT_TOOLS_CURSEFORGE_API_KEY'),
    ],
],
```

## Development

### Frontend

```bash
# Install dependencies
npm install

# Development server
npm run dev

# Production build
npm run build
```

### Backend

```bash
# Run tests locally (requires a local PHP runtime)
composer test
composer test:unit
composer test:integration

# Run tests in Docker
bash tools/test-docker.sh unit
bash tools/test-docker.sh integration
bash tools/test-docker.sh shell

# Lint code
composer lint
```

### Local test panel

The repository includes a disposable Blueprint/Pterodactyl test panel backed by Docker Desktop or Docker Engine. All panel data, containers, generated extension files, and logs are stored under `.test-panel/`, which is ignored by Git.

On Windows PowerShell:

```powershell
npm run panel:setup
```

On macOS/Linux or Git Bash:

```bash
./tools/test-panel.sh setup
```

The setup command starts MariaDB, Valkey, and the Blueprint panel, stages the current repository as the `minecraft-tools` development extension, and runs Blueprint's `-build` command. Open `http://localhost:8080` after setup. Create the first local administrator with:

```bash
docker compose -p minecraft-tools-test-panel --project-directory . -f tools/test-panel/docker-compose.yml exec panel php artisan p:user:make
```

Use `panel:refresh` or `./tools/test-panel.sh refresh` after source changes. `start`, `stop`, `logs`, and `reset` control the local instance; `reset` removes its database volume and generated files.

## API Endpoints

### Plugins
- `GET /api/extensions/minecraft-tools/plugins` - List plugins
- `POST /api/extensions/minecraft-tools/plugins` - Install plugin
- `GET /api/extensions/minecraft-tools/plugins/{plugin}` - Get plugin details
- `PUT /api/extensions/minecraft-tools/plugins/{plugin}` - Update plugin
- `DELETE /api/extensions/minecraft-tools/plugins/{plugin}` - Uninstall plugin
- `POST /api/extensions/minecraft-tools/plugins/{plugin}/install` - Install plugin
- `POST /api/extensions/minecraft-tools/plugins/{plugin}/enable` - Enable plugin
- `POST /api/extensions/minecraft-tools/plugins/{plugin}/disable` - Disable plugin
- `GET /api/extensions/minecraft-tools/plugins/{plugin}/config` - Get plugin config
- `PUT /api/extensions/minecraft-tools/plugins/{plugin}/config` - Update plugin config
- `GET /api/extensions/minecraft-tools/plugins/available` - Get available plugins
- `POST /api/extensions/minecraft-tools/plugins/search` - Search plugins

### Versions
- `GET /api/extensions/minecraft-tools/versions` - List versions
- `POST /api/extensions/minecraft-tools/versions` - Install version
- `GET /api/extensions/minecraft-tools/versions/{version}` - Get version details
- `DELETE /api/extensions/minecraft-tools/versions/{version}` - Remove version
- `POST /api/extensions/minecraft-tools/versions/{version}/reinstall` - Reinstall version
- `GET /api/extensions/minecraft-tools/versions/available` - Get available versions
- `GET /api/extensions/minecraft-tools/versions/current` - Get current version
- `POST /api/extensions/minecraft-tools/versions/switch` - Switch version
- `GET /api/extensions/minecraft-tools/versions/builds/{version}` - Get builds
- `POST /api/extensions/minecraft-tools/versions/check-updates` - Check for updates

### Players
- `GET /api/extensions/minecraft-tools/players` - List players
- `POST /api/extensions/minecraft-tools/players` - Perform player action
- `GET /api/extensions/minecraft-tools/players/{player}` - Get player details
- `PUT /api/extensions/minecraft-tools/players/{player}` - Update player
- `DELETE /api/extensions/minecraft-tools/players/{player}` - Remove player data
- `POST /api/extensions/minecraft-tools/players/{player}/ban` - Ban player
- `POST /api/extensions/minecraft-tools/players/{player}/unban` - Unban player
- `POST /api/extensions/minecraft-tools/players/{player}/kick` - Kick player
- `POST /api/extensions/minecraft-tools/players/{player}/whitelist` - Whitelist player
- `POST /api/extensions/minecraft-tools/players/{player}/unwhitelist` - Remove from whitelist
- `POST /api/extensions/minecraft-tools/players/{player}/op` - Give OP
- `POST /api/extensions/minecraft-tools/players/{player}/deop` - Remove OP
- `GET /api/extensions/minecraft-tools/players/{player}/logs` - Get player logs
- `GET /api/extensions/minecraft-tools/players/{player}/inventory` - Get inventory
- `GET /api/extensions/minecraft-tools/players/{player}/enderchest` - Get enderchest
- `GET /api/extensions/minecraft-tools/players/online` - Get online players
- `GET /api/extensions/minecraft-tools/players/banned` - Get banned players
- `GET /api/extensions/minecraft-tools/players/whitelisted` - Get whitelisted players
- `GET /api/extensions/minecraft-tools/players/ops` - Get operators

### Modpacks
- `GET /api/extensions/minecraft-tools/modpacks` - List modpacks
- `POST /api/extensions/minecraft-tools/modpacks` - Install modpack
- `GET /api/extensions/minecraft-tools/modpacks/{modpack}` - Get modpack details
- `PUT /api/extensions/minecraft-tools/modpacks/{modpack}` - Update modpack
- `DELETE /api/extensions/minecraft-tools/modpacks/{modpack}` - Uninstall modpack
- `POST /api/extensions/minecraft-tools/modpacks/{modpack}/install` - Install modpack
- `POST /api/extensions/minecraft-tools/modpacks/{modpack}/uninstall` - Uninstall modpack
- `POST /api/extensions/minecraft-tools/modpacks/{modpack}/update` - Update modpack
- `GET /api/extensions/minecraft-tools/modpacks/{modpack}/versions` - Get versions
- `POST /api/extensions/minecraft-tools/modpacks/{modpack}/switch-version` - Switch version
- `GET /api/extensions/minecraft-tools/modpacks/{modpack}/config` - Get config
- `PUT /api/extensions/minecraft-tools/modpacks/{modpack}/config` - Update config
- `GET /api/extensions/minecraft-tools/modpacks/available` - Get available modpacks
- `POST /api/extensions/minecraft-tools/modpacks/search` - Search modpacks
- `GET /api/extensions/minecraft-tools/modpacks/categories` - Get categories
- `GET /api/extensions/minecraft-tools/modpacks/{modpack}/files` - Get files
- `POST /api/extensions/minecraft-tools/modpacks/{modpack}/backup` - Create backup
- `POST /api/extensions/minecraft-tools/modpacks/{modpack}/restore` - Restore backup

### Config
- `GET /api/extensions/minecraft-tools/config` - List configs
- `POST /api/extensions/minecraft-tools/config` - Update config
- `GET /api/extensions/minecraft-tools/config/{file}` - Get config file
- `PUT /api/extensions/minecraft-tools/config/{file}` - Update config file
- `GET /api/extensions/minecraft-tools/config/{file}/raw` - Get raw config
- `PUT /api/extensions/minecraft-tools/config/{file}/raw` - Update raw config
- `GET /api/extensions/minecraft-tools/config/{file}/backup` - Create backup
- `POST /api/extensions/minecraft-tools/config/{file}/restore` - Restore backup
- `GET /api/extensions/minecraft-tools/config/files` - List config files
- `POST /api/extensions/minecraft-tools/config/validate` - Validate config
- `GET /api/extensions/minecraft-tools/config/templates` - Get templates
- `POST /api/extensions/minecraft-tools/config/templates/{template}/apply` - Apply template

### Icon
- `GET /api/extensions/minecraft-tools/icon` - Get current icon
- `POST /api/extensions/minecraft-tools/icon` - Upload icon
- `DELETE /api/extensions/minecraft-tools/icon` - Delete icon
- `GET /api/extensions/minecraft-tools/icon/preview` - Preview icon
- `POST /api/extensions/minecraft-tools/icon/generate` - Generate icon
- `GET /api/extensions/minecraft-tools/icon/templates` - Get templates
- `POST /api/extensions/minecraft-tools/icon/templates/{template}/apply` - Apply template
- `GET /api/extensions/minecraft-tools/icon/history` - Get history
- `POST /api/extensions/minecraft-tools/icon/history/{id}/restore` - Restore from history

## Permissions

The extension uses the following permissions:
- `admin.extensions.view` - View extension
- `admin.extensions.manage` - Manage extension settings

## License

MIT License - see LICENSE file for details.