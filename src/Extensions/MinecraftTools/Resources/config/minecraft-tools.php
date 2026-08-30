<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Minecraft Tools Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the Minecraft Tools Pterodactyl extension.
    |
    */

    'api' => [
        'spigot' => [
            'base_url' => 'https://api.spiget.org/v2',
            'rate_limit' => 60,
        ],
        'modrinth' => [
            'base_url' => 'https://api.modrinth.com/v2',
            'rate_limit' => 100,
        ],
        'curseforge' => [
            'base_url' => 'https://api.curseforge.com/v1',
            'rate_limit' => 60,
            'api_key' => env('MINECRAFT_TOOLS_CURSEFORGE_API_KEY'),
        ],
        'technic' => [
            'base_url' => 'https://api.technicpack.net',
            'rate_limit' => 30,
        ],
        'ftb' => [
            'base_url' => 'https://api.feed-the-beast.com',
            'rate_limit' => 30,
        ],
        'paper' => [
            'base_url' => 'https://api.papermc.io/v2',
            'rate_limit' => 60,
        ],
        'fabric' => [
            'base_url' => 'https://meta.fabricmc.net',
            'rate_limit' => 60,
        ],
    ],

    'server' => [
        'max_file_size' => 100 * 1024 * 1024, // 100MB
        'allowed_extensions' => ['jar', 'zip', 'mrpack'],
        'backup_before_changes' => true,
        'max_backups' => 10,
    ],

    'icon' => [
        'max_size' => 1024 * 1024, // 1MB
        'dimensions' => [64, 64],
        'allowed_types' => ['image/png', 'image/jpeg'],
        'history_limit' => 20,
    ],

    'config_editor' => [
        'allowed_files' => [
            'server.properties',
            'spigot.yml',
            'bukkit.yml',
            'paper.yml',
            'pufferfish.yml',
            'purpur.yml',
            'fabric-server-launcher.properties',
            'forge-server.toml',
            'neoforge-server.toml',
        ],
        'validation' => true,
        'backup_on_save' => true,
    ],

    'plugins' => [
        'auto_update_check' => true,
        'update_check_interval' => 3600, // 1 hour
        'install_timeout' => 300, // 5 minutes
    ],

    'versions' => [
        'types' => ['vanilla', 'paper', 'spigot', 'purpur', 'fabric', 'forge', 'quilt', 'neoforge'],
        'default_type' => 'paper',
        'cache_versions' => true,
        'cache_ttl' => 3600,
    ],

    'players' => [
        'page_size' => 50,
        'max_log_lines' => 500,
    ],

    'modpacks' => [
        'sources' => ['modrinth', 'curseforge', 'technic', 'ftb'],
        'default_source' => 'modrinth',
        'install_timeout' => 600, // 10 minutes
        'backup_before_switch' => true,
    ],
];