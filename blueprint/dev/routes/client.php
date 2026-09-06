<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Pterodactyl\Models\Server;
use Pterodactyl\Repositories\Wings\DaemonCommandRepository;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;
use Pterodactyl\Http\Middleware\Api\Client\Server\AuthenticateServerAccess;

/*
|--------------------------------------------------------------------------
| Minecraft Tools per-server client API
|--------------------------------------------------------------------------
|
| Prefix: /api/client/extensions/minecrafttools/servers/{server}
|
| These routes operate directly on the Wings filesystem of the bound server
| (via DaemonFileRepository / DaemonCommandRepository) so the user-facing
| Minecraft Tools pages always reflect the real state of the server files.
|
*/

Route::pattern('file', '[^/]+');

Route::group(['prefix' => '/servers/{server}', 'middleware' => [AuthenticateServerAccess::class]], function () {
    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */
    $wings = fn (Server $server): DaemonFileRepository => app(DaemonFileRepository::class)->setServer($server);
    $console = fn (Server $server): DaemonCommandRepository => app(DaemonCommandRepository::class)->setServer($server);

    $kvGet = function (Server $server, string $key, mixed $default = null): mixed {
        try {
            $raw = DB::table('minecrafttools_keyvalues')->where('key', "server:{$server->uuid}:{$key}")->value('value');
        } catch (\Throwable $e) {
            return $default;
        }
        if (is_null($raw) || $raw === '') {
            return $default;
        }
        $decoded = json_decode($raw, true);

        return $decoded ?? $default;
    };
    $kvSet = function (Server $server, string $key, mixed $value): void {
        DB::table('minecrafttools_keyvalues')->updateOrInsert(
            ['key' => "server:{$server->uuid}:{$key}"],
            ['value' => json_encode($value), 'updated_at' => now()],
        );
    };

    $isSafeFile = static fn (string $name): bool => (bool) preg_match('/^[A-Za-z0-9._-]+$/', $name) && !str_contains($name, '..');

    $entryNames = function (Server $server, string $dir, ?callable $filter = null) use ($wings): array {
        try {
            $entries = $wings($server)->getDirectory($dir);
        } catch (\Throwable $e) {
            return [];
        }
        $names = [];
        foreach ($entries as $entry) {
            if (!is_array($entry) || !is_string($entry['name'] ?? null)) {
                continue;
            }
            if ($filter === null || $filter($entry)) {
                $names[] = $entry['name'];
            }
        }
        sort($names);

        return $names;
    };

    /* Provider configuration (mirrored from the admin settings router). */
    $defaultSection = static fn (): array => [
        'provider' => 'modrinth',
        'base_url' => '',
        'api_key' => '',
        'enabled' => true,
    ];

    $providerConfig = function () use ($defaultSection): array {
        try {
            $raw = DB::table('minecrafttools_keyvalues')->where('key', 'provider_config')->value('value');
        } catch (\Throwable $e) {
            $raw = null;
        }
        $cfg = $raw ? json_decode((string) $raw, true) : null;
        $cfg = is_array($cfg) ? $cfg : [];
        $cfg['plugins'] = array_merge($defaultSection(), is_array($cfg['plugins'] ?? null) ? $cfg['plugins'] : []);
        $cfg['mods'] = array_merge($defaultSection(), is_array($cfg['mods'] ?? null) ? $cfg['mods'] : []);

        return $cfg;
    };

    /* Resolve the direct download for a Modrinth project (latest jar file). */
    $modrinthFile = function (string $base, string $slug, string $apiKey = ''): ?array {
        try {
            $headers = ['Accept' => 'application/json'];
            if ($apiKey !== '') {
                $headers['Authorization'] = $apiKey;
            }
            $url = rtrim($base, '/') . '/project/' . rawurlencode($slug) . '/version';
            $res = Http::timeout(12)->withHeaders($headers)->acceptJson()->get($url, [
                'featured' => 'true',
                'loaders' => '["paper","bukkit","spigot","velocity","bungeecord","fabric","forge","neoforge","quilt"]',
            ]);
            if (!$res->successful()) {
                $res = Http::timeout(12)->withHeaders($headers)->acceptJson()->get($url);
            }
            foreach ($res->json() ?? [] as $version) {
                foreach ($version['files'] ?? [] as $file) {
                    $href = $file['url'] ?? '';
                    if (is_string($href) && Str::endsWith(strtolower($href), '.jar')) {
                        return ['url' => $href, 'filename' => $file['filename'] ?? null];
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return null;
    };

    /* Resolve the latest download for a Hangar project. */
    $hangarFile = function (string $base, string $slug, string $apiKey = ''): ?array {
        try {
            $base = rtrim($base, '/');
            $api = Str::endsWith($base, '/api/v1') ? $base : $base . '/api/v1';
            $headers = ['Accept' => 'application/json'];
            if ($apiKey !== '') {
                $headers['X-Hangar-Api-Key'] = $apiKey;
            }
            $res = Http::timeout(12)->withHeaders($headers)->acceptJson()->get($api . '/projects/' . rawurlencode($slug) . '/versions', ['limit' => 1]);
            foreach ($res->json() ?? [] as $version) {
                $href = $version['downloadUrl'] ?? '';
                if (is_string($href) && $href !== '') {
                    return ['url' => $href, 'filename' => $version['fileName'] ?? null];
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return null;
    };

    /* Resolve a Spigot/Spiget download endpoint. */
    $spigotFile = function (string $base, string $id): ?array {
        try {
            $base = rtrim($base, '/');
            $api = Str::endsWith($base, '/v2') ? $base : $base . '/v2';

            return ['url' => $api . '/resources/' . rawurlencode($id) . '/download', 'filename' => null];
        } catch (\Throwable $e) {
            return null;
        }
    };

    /* Search a provider for plugins or mods. */
    $providerSearch = function (string $kind, string $query, int $limit = 10) use ($providerConfig): array {
        $cfg = $providerConfig();
        $section = $cfg[$kind] ?? $cfg['plugins'];
        $provider = $section['provider'] ?? 'modrinth';
        if (!($section['enabled'] ?? true)) {
            return ['provider' => $provider, 'results' => []];
        }
        $base = trim((string) ($section['base_url'] ?? ''));
        $key = trim((string) ($section['api_key'] ?? ''));
        $results = [];

        try {
            if ($provider === 'modrinth') {
                $base = $base !== '' ? rtrim($base, '/') : 'https://api.modrinth.com/v2';
                $headers = ['Accept' => 'application/json'];
                if ($key !== '') {
                    $headers['Authorization'] = $key;
                }
                $ptype = $kind === 'mods' ? 'mod' : 'plugin';
                $res = Http::timeout(12)->withHeaders($headers)->acceptJson()->get($base . '/search', [
                    'query' => $query,
                    'limit' => $limit,
                    'facets' => json_encode([['project_type:' . $ptype]]),
                ]);
                if ($res->successful()) {
                    foreach ($res->json('hits', []) as $hit) {
                        $results[] = [
                            'id' => (string) ($hit['slug'] ?? ''),
                            'name' => (string) ($hit['title'] ?? $hit['slug'] ?? 'Unknown'),
                            'description' => (string) ($hit['description'] ?? ''),
                            'source' => 'modrinth',
                        ];
                    }
                }
            } elseif ($provider === 'hangar') {
                $base = $base !== '' ? rtrim($base, '/') : 'https://hangar.papermc.io';
                $api = Str::endsWith($base, '/api') ? $base : $base . '/api';
                $headers = ['Accept' => 'application/json'];
                if ($key !== '') {
                    $headers['X-Hangar-Api-Key'] = $key;
                }
                $res = Http::timeout(12)->withHeaders($headers)->acceptJson()->get($api . '/v1/projects', ['limit' => $limit, 'q' => $query]);
                if ($res->successful()) {
                    foreach ($res->json() ?? [] as $item) {
                        $results[] = [
                            'id' => (string) ($item['namespace']['slug'] ?? $item['slug'] ?? ''),
                            'name' => (string) ($item['name'] ?? ''),
                            'description' => (string) ($item['description'] ?? ''),
                            'source' => 'hangar',
                        ];
                    }
                }
            } elseif ($provider === 'spigot') {
                $base = $base !== '' ? rtrim($base, '/') : 'https://api.spiget.org/v2';
                $api = Str::endsWith($base, '/v2') ? $base : $base . '/v2';
                $res = Http::timeout(12)->acceptJson()->get($api . '/search/resources/' . rawurlencode($query), ['size' => $limit, 'field' => 'name']);
                if ($res->successful()) {
                    foreach ($res->json() ?? [] as $item) {
                        $results[] = [
                            'id' => (string) ($item['id'] ?? ''),
                            'name' => (string) ($item['name'] ?? 'Unknown'),
                            'description' => (string) ($item['tag'] ?? ''),
                            'source' => 'spigot',
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            // provider unreachable -> empty results
        }

        return ['provider' => $provider, 'results' => $results];
    };

    $resolveInstall = function (string $kind, string $source, string $id) use ($providerConfig, $modrinthFile, $hangarFile, $spigotFile): ?array {
        $cfg = $providerConfig();
        $section = $cfg[$kind] ?? $cfg['plugins'];
        $base = trim((string) ($section['base_url'] ?? ''));
        $key = trim((string) ($section['api_key'] ?? ''));

        try {
            return match ($source) {
                'modrinth' => $modrinthFile($base !== '' ? $base : 'https://api.modrinth.com/v2', $id, $key),
                'hangar' => $hangarFile($base !== '' ? $base : 'https://hangar.papermc.io', $id, $key),
                'spigot' => $spigotFile($base !== '' ? $base : 'https://api.spiget.org/v2', $id),
                default => null,
            };
        } catch (\Throwable $e) {
            return null;
        }
    };

    $installInto = function (Server $server, string $directory, string $url, ?string $filename) use ($wings): ?string {
        if (!Str::startsWith($url, ['http://', 'https://'])) {
            return 'The download URL must start with http(s)://';
        }
        $name = trim((string) $filename);
        if ($name === '') {
            $name = basename((string) parse_url($url, PHP_URL_PATH)) ?: 'download.jar';
        }
        $name = basename($name);
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $name)) {
            return 'The file name may only contain letters, numbers, dots, dashes and underscores.';
        }
        try {
            $wings($server)->pull($url, $directory, ['filename' => $name]);
        } catch (\Throwable $e) {
            return 'Failed to start the download on Wings: ' . $e->getMessage();
        }

        return null;
    };

    $listJars = function (Server $server, string $directory) use ($wings): array {
        $jars = [];
        try {
            foreach ($wings($server)->getDirectory($directory) as $entry) {
                if (!is_array($entry) || !is_string($entry['name'] ?? null) || ($entry['directory'] ?? false)) {
                    continue;
                }
                if (Str::endsWith(strtolower($entry['name']), '.jar')) {
                    $jars[] = ['name' => $entry['name'], 'size' => (int) ($entry['size'] ?? 0)];
                }
            }
        } catch (\Throwable $e) {
            // folder missing
        }
        usort($jars, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $jars;
    };

    /* Malicious command protection for console actions. */
    $allowedCommand = static function (string $command): bool {
        $command = trim($command);
        if ($command === '' || strlen($command) > 400) {
            return false;
        }
        $first = strtolower(explode(' ', $command)[0] ?? '');

        return in_array($first, ['whitelist', 'op', 'deop', 'ban', 'pardon', 'kick', 'list', 'tps', 'players'], true);
    };

    /* ------------------------------------------------------------------ */
    /* Overview                                                            */
    /* ------------------------------------------------------------------ */
    Route::get('/', function (Server $server) use ($wings, $listJars) {
        $folders = [];
        foreach (['plugins', 'mods', 'config', 'logs', 'world'] as $folder) {
            try {
                $wings($server)->getDirectory('/' . $folder);
                $folders[$folder] = true;
            } catch (\Throwable $e) {
                $folders[$folder] = false;
            }
        }
        $files = [];
        try {
            foreach ($wings($server)->getDirectory('/') as $entry) {
                if (is_array($entry) && is_string($entry['name'] ?? null) && !($entry['directory'] ?? false)) {
                    $files[] = $entry['name'];
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return response()->json([
            'server' => ['uuid' => $server->uuid, 'name' => $server->name, 'egg' => $server->egg->name],
            'folders' => $folders,
            'files' => array_values(array_filter($files, fn ($f) => preg_match('/^[A-Za-z0-9._-]+$/', (string) $f))),
            'jars' => $listJars($server, '/'),
        ]);
    });

    /* ------------------------------------------------------------------ */
    /* Players (whitelist / ops / bans files)                              */
    /* ------------------------------------------------------------------ */
    Route::get('/players', function (Server $server) use ($wings) {
        $read = function (string $file) use ($wings, $server): array {
            try {
                $content = $wings($server)->getContent('/' . $file, 1_000_000);
                $decoded = json_decode($content, true);

                return is_array($decoded) ? $decoded : [];
            } catch (\Throwable $e) {
                return [];
            }
        };
        $whitelistEnabled = false;
        try {
            $props = $wings($server)->getContent('/server.properties', 1_000_000);
            foreach (explode("\n", $props) as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                if (preg_match('/^white-list=(.*)$/i', $line, $m)) {
                    $whitelistEnabled = ($m[1] === 'true');
                }
            }
        } catch (\Throwable $e) {
            // no server.properties
        }

        return response()->json([
            'whitelist' => $read('whitelist.json'),
            'ops' => $read('ops.json'),
            'banned_players' => $read('banned-players.json'),
            'banned_ips' => $read('banned-ips.json'),
            'whitelist_enabled' => $whitelistEnabled,
        ]);
    });

    Route::put('/players', function (Request $request, Server $server) use ($wings) {
        $data = $request->json()->all();
        $targets = [
            'whitelist' => 'whitelist.json',
            'ops' => 'ops.json',
            'banned_players' => 'banned-players.json',
            'banned_ips' => 'banned-ips.json',
        ];
        $written = [];
        foreach ($targets as $key => $file) {
            if (array_key_exists($key, $data)) {
                if (!is_array($data[$key])) {
                    return response()->json(['status' => 'error', 'message' => "{$key} must be an array"], 422);
                }
                $content = json_encode($data[$key], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                try {
                    $wings($server)->putContent('/' . $file, $content);
                    $written[] = $file;
                } catch (\Throwable $e) {
                    return response()->json(['status' => 'error', 'message' => "Failed to write {$file}"], 500);
                }
            }
        }

        return response()->json(['status' => 'success', 'written' => $written]);
    });

    Route::post('/players/command', function (Request $request, Server $server) use ($console, $allowedCommand) {
        $command = trim((string) $request->json('command', ''));
        if (!$allowedCommand($command)) {
            return response()->json(['status' => 'error', 'message' => 'Command is not in the allowed list'], 422);
        }
        try {
            $console($server)->send($command);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to send command: ' . $e->getMessage()], 500);
        }

        return response()->json(['status' => 'success']);
    });

    /* ------------------------------------------------------------------ */
    /* Plugins (server plugins/ folder)                                    */
    /* ------------------------------------------------------------------ */
    Route::get('/plugins', function (Server $server) use ($wings) {
        $plugins = [];
        $hasFolder = true;
        try {
            foreach ($wings($server)->getDirectory('/plugins') as $entry) {
                if (!is_array($entry) || !is_string($entry['name'] ?? null) || ($entry['directory'] ?? false)) {
                    continue;
                }
                $name = $entry['name'];
                $plugins[] = [
                    'name' => $name,
                    'size' => (int) ($entry['size'] ?? 0),
                    'enabled' => !Str::endsWith(strtolower($name), '.disabled'),
                ];
            }
        } catch (\Throwable $e) {
            $hasFolder = false;
        }
        usort($plugins, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return response()->json([
            'plugins' => $plugins,
            'has_folder' => $hasFolder || count($plugins) > 0,
        ]);
    });

    Route::post('/plugins/search', function (Request $request) use ($providerSearch) {
        $query = trim((string) $request->json('query', ''));
        if ($query === '') {
            return response()->json(['provider' => 'modrinth', 'results' => []]);
        }

        return response()->json($providerSearch('plugins', $query));
    });

    Route::post('/plugins', function (Request $request, Server $server) use ($installInto) {
        $url = trim((string) $request->json('url', ''));
        $filename = trim((string) $request->json('filename', ''));
        $error = $installInto($server, '/plugins', $url, $filename);
        if ($error) {
            return response()->json(['status' => 'error', 'message' => $error], 422);
        }

        return response()->json(['status' => 'success']);
    });

    Route::post('/plugins/install', function (Request $request, Server $server) use ($resolveInstall, $installInto) {
        $source = strtolower(trim((string) $request->json('source', 'modrinth')));
        $id = trim((string) $request->json('id', ''));
        if ($id === '') {
            return response()->json(['status' => 'error', 'message' => 'No project id provided'], 422);
        }
        $download = $resolveInstall('plugins', $source, $id);
        if (!$download || !str_starts_with($download['url'] ?? '', 'http')) {
            return response()->json(['status' => 'error', 'message' => 'Could not resolve a download link from ' . ucfirst($source)], 422);
        }
        $filename = $download['filename'] ?? ($id . '.jar');
        $error = $installInto($server, '/plugins', $download['url'], $filename);
        if ($error) {
            return response()->json(['status' => 'error', 'message' => $error], 422);
        }

        return response()->json(['status' => 'success']);
    });

    Route::put('/plugins/{file}/enabled', function (Request $request, Server $server, string $file) use ($wings, $isSafeFile) {
        if (!$isSafeFile($file)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid file name'], 422);
        }
        $enabled = (bool) $request->json('enabled');
        $from = $file;
        $to = $file;
        if ($enabled && Str::endsWith(strtolower($from), '.disabled')) {
            $to = preg_replace('/\.disabled$/i', '', $from);
        } elseif (!$enabled && !Str::endsWith(strtolower($from), '.disabled')) {
            $to = $from . '.disabled';
        }
        if ($from !== $to) {
            try {
                $wings($server)->renameFiles('/plugins', [['from' => $from, 'to' => $to]]);
            } catch (\Throwable $e) {
                return response()->json(['status' => 'error', 'message' => 'Failed to rename plugin file'], 500);
            }
        }

        return response()->json(['status' => 'success']);
    });

    Route::delete('/plugins/{file}', function (Server $server, string $file) use ($wings, $isSafeFile) {
        if (!$isSafeFile($file)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid file name'], 422);
        }
        try {
            $wings($server)->deleteFiles('/plugins', [$file]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to delete plugin file'], 500);
        }

        return response()->json(['status' => 'success']);
    });

    /* ------------------------------------------------------------------ */
    /* Mods (server mods/ folder)                                          */
    /* ------------------------------------------------------------------ */
    Route::get('/mods', function (Server $server) use ($wings) {
        $mods = [];
        try {
            foreach ($wings($server)->getDirectory('/mods') as $entry) {
                if (!is_array($entry) || !is_string($entry['name'] ?? null) || ($entry['directory'] ?? false)) {
                    continue;
                }
                $name = $entry['name'];
                if (Str::endsWith(strtolower($name), '.jar') || Str::endsWith(strtolower($name), '.disabled')) {
                    $mods[] = ['name' => $name, 'size' => (int) ($entry['size'] ?? 0), 'enabled' => !Str::endsWith(strtolower($name), '.disabled')];
                }
            }
        } catch (\Throwable $e) {
            // folder missing
        }
        usort($mods, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return response()->json(['mods' => $mods]);
    });

    Route::post('/mods/search', function (Request $request) use ($providerSearch) {
        $query = trim((string) $request->json('query', ''));
        if ($query === '') {
            return response()->json(['provider' => 'modrinth', 'results' => []]);
        }

        return response()->json($providerSearch('mods', $query));
    });

    Route::post('/mods', function (Request $request, Server $server) use ($installInto) {
        $url = trim((string) $request->json('url', ''));
        $filename = trim((string) $request->json('filename', ''));
        $error = $installInto($server, '/mods', $url, $filename);
        if ($error) {
            return response()->json(['status' => 'error', 'message' => $error], 422);
        }

        return response()->json(['status' => 'success']);
    });

    Route::post('/mods/install', function (Request $request, Server $server) use ($resolveInstall, $installInto) {
        $source = strtolower(trim((string) $request->json('source', 'modrinth')));
        $id = trim((string) $request->json('id', ''));
        if ($id === '') {
            return response()->json(['status' => 'error', 'message' => 'No project id provided'], 422);
        }
        $download = $resolveInstall('mods', $source, $id);
        if (!$download || !str_starts_with($download['url'] ?? '', 'http')) {
            return response()->json(['status' => 'error', 'message' => 'Could not resolve a download link from ' . ucfirst($source)], 422);
        }
        $filename = $download['filename'] ?? ($id . '.jar');
        $error = $installInto($server, '/mods', $download['url'], $filename);
        if ($error) {
            return response()->json(['status' => 'error', 'message' => $error], 422);
        }

        return response()->json(['status' => 'success']);
    });

    Route::delete('/mods/{file}', function (Server $server, string $file) use ($wings, $isSafeFile) {
        if (!$isSafeFile($file)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid file name'], 422);
        }
        try {
            $wings($server)->deleteFiles('/mods', [$file]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to delete mod file'], 500);
        }

        return response()->json(['status' => 'success']);
    });

    /* ------------------------------------------------------------------ */
    /* Versions (server jar management)                                    */
    /* ------------------------------------------------------------------ */
    Route::get('/versions', function (Server $server) use ($listJars) {
        return response()->json(['jars' => $listJars($server, '/'), 'target' => 'server.jar']);
    });

    $versionLists = static function (string $type): array {
        $agent = ['User-Agent' => 'MinecraftTools/1.0.0'];
        try {
            return match ($type) {
                'paper' => (array) (Http::timeout(12)->withHeaders($agent)->acceptJson()->get('https://api.papermc.io/v2/projects/paper')->json('versions', []) ?? []),
                'purpur' => (array) (Http::timeout(12)->acceptJson()->get('https://api.purpurmc.org/v2/purpur')->json('versions', []) ?? []),
                'fabric' => array_column(Http::timeout(12)->acceptJson()->get('https://meta.fabricmc.net/v2/versions/game')->json() ?? [], 'version'),
                'vanilla' => array_column(Http::timeout(12)->acceptJson()->get('https://launchermeta.mojang.com/mc/game/version_manifest_v2.json')->json('versions', []) ?? [], 'id'),
                default => ['1.21.5', '1.21.4', '1.21.1', '1.20.6', '1.20.4', '1.20.1', '1.19.4', '1.18.2', '1.17.1'],
            };
        } catch (\Throwable $e) {
            return [];
        }
    };

    Route::post('/versions/list', function (Request $request) use ($versionLists) {
        $type = trim((string) $request->json('type', 'paper'));
        $versions = array_values(array_unique(array_filter(array_map('strval', $versionLists($type)))));

        return response()->json(['versions' => $versions]);
    });

    $buildLists = static function (string $type, string $version): array {
        $agent = ['User-Agent' => 'MinecraftTools/1.0.0'];
        try {
            if ($type === 'paper') {
                $res = Http::timeout(12)->withHeaders($agent)->acceptJson()->get('https://api.papermc.io/v2/projects/paper/versions/' . rawurlencode($version) . '/builds');
                $out = [];
                foreach ($res->json('builds', []) ?? [] as $build) {
                    $out[] = ['build' => (string) ($build['id'] ?? ''), 'channel' => (string) ($build['channel'] ?? '')];
                }

                return $out;
            }
            if ($type === 'purpur') {
                $res = Http::timeout(12)->acceptJson()->get('https://api.purpurmc.org/v2/purpur/' . rawurlencode($version));
                $all = (array) ($res->json('builds.all', []) ?? []);
                $latest = $res->json('builds.latest');
                $out = [];
                if ($latest !== null) {
                    $out[] = ['build' => (string) $latest, 'channel' => 'latest'];
                }
                foreach ($all as $build) {
                    $out[] = ['build' => (string) $build, 'channel' => null];
                }

                return $out;
            }
            if ($type === 'fabric') {
                $res = Http::timeout(12)->acceptJson()->get('https://meta.fabricmc.net/v2/versions/loader/' . rawurlencode($version));
                $out = [];
                foreach ($res->json() ?? [] as $entry) {
                    $loader = $entry['loader']['version'] ?? null;
                    $installer = $entry['installer']['version'] ?? null;
                    if ($loader !== null) {
                        $out[] = ['build' => (string) $loader, 'installer' => $installer !== null ? (string) $installer : null];
                    }
                }

                return $out;
            }

            return [['build' => 'latest', 'channel' => null]];
        } catch (\Throwable $e) {
            return [];
        }
    };

    Route::post('/versions/builds', function (Request $request) use ($buildLists) {
        $type = trim((string) $request->json('type', 'paper'));
        $version = trim((string) $request->json('version', ''));

        return response()->json(['builds' => $buildLists($type, $version)]);
    });

    $resolveVersionDownload = function (string $type, string $version, ?string $build, ?string $installer) use ($buildLists): ?array {
        try {
            if ($type === 'paper') {
                if (!$build || $build === 'latest') {
                    $list = $buildLists('paper', $version);
                    $build = count($list) > 0 ? $list[count($list) - 1]['build'] : null;
                }
                if (!$build) {
                    return null;
                }

                return ['url' => "https://api.papermc.io/v2/projects/paper/versions/{$version}/builds/{$build}/downloads/paper-{$version}-{$build}.jar", 'filename' => "paper-{$version}-{$build}.jar"];
            }
            if ($type === 'purpur') {
                if (!$build || $build === 'latest') {
                    $res = Http::timeout(12)->acceptJson()->get('https://api.purpurmc.org/v2/purpur/' . rawurlencode($version));
                    $latest = $res->successful() ? $res->json('builds.latest') : null;
                    $build = $latest !== null ? (string) $latest : null;
                }
                if (!$build) {
                    return null;
                }

                return ['url' => "https://api.purpurmc.org/v2/purpur/{$version}/{$build}/download", 'filename' => "purpur-{$version}-{$build}.jar"];
            }
            if ($type === 'fabric') {
                $loader = $build ?: null;
                $inst = $installer ?: null;
                if (!$loader) {
                    $list = $buildLists('fabric', $version);
                    if (count($list) > 0) {
                        $loader = $list[0]['build'];
                        $inst = $list[0]['installer'] ?? null;
                    }
                }
                if (!$loader) {
                    return null;
                }
                if (!$inst) {
                    $res = Http::timeout(12)->acceptJson()->get('https://meta.fabricmc.net/v2/versions/installer');
                    $inst = $res->successful() ? ($res->json()[0]['version'] ?? null) : null;
                }
                if (!$inst) {
                    return null;
                }

                return ['url' => "https://meta.fabricmc.net/v2/versions/loader/{$version}/{$loader}/{$inst}/server/jar", 'filename' => "fabric-server-mc.{$version}-loader.{$loader}-launch.{$inst}.jar"];
            }
            if ($type === 'vanilla') {
                $res = Http::timeout(12)->acceptJson()->get('https://launchermeta.mojang.com/mc/game/version_manifest_v2.json');
                foreach ($res->json('versions', []) ?? [] as $entry) {
                    if (($entry['id'] ?? '') !== $version) {
                        continue;
                    }
                    $detail = Http::timeout(12)->acceptJson()->get($entry['url']);
                    $server = $detail->successful() ? ($detail->json('downloads')['server'] ?? null) : null;
                    if (is_array($server) && is_string($server['url'] ?? null)) {
                        return ['url' => $server['url'], 'filename' => "server-{$version}.jar"];
                    }
                }

                return null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    };

    Route::post('/versions/install', function (Request $request, Server $server) use ($resolveVersionDownload, $wings) {
        $type = trim((string) $request->json('type', 'paper'));
        $version = trim((string) $request->json('version', ''));
        $build = trim((string) $request->json('build', ''));
        $installer = trim((string) $request->json('installer', ''));
        $target = trim((string) $request->json('target', 'server.jar'));
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $target)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid target file name'], 422);
        }

        $url = trim((string) $request->json('url', ''));
        if ($url === '') {
            $download = $resolveVersionDownload($type, $version, $build !== '' ? $build : null, $installer !== '' ? $installer : null);
            if (!$download) {
                return response()->json(['status' => 'error', 'message' => 'Unable to resolve a download for the selected version.'], 422);
            }
            $url = $download['url'];
        }
        if (!Str::startsWith($url, ['http://', 'https://'])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid download URL'], 422);
        }

        // Move the current jar aside (only keep a single previous copy).
        $currentExists = false;
        try {
            foreach ($wings($server)->getDirectory('/') as $entry) {
                if (is_array($entry) && ($entry['name'] ?? '') === $target && !($entry['directory'] ?? false)) {
                    $currentExists = true;
                    break;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
        if ($currentExists) {
            $previous = $target . '.previous';
            try {
                foreach ($wings($server)->getDirectory('/') as $entry) {
                    if (is_array($entry) && ($entry['name'] ?? '') === $previous && !($entry['directory'] ?? false)) {
                        $wings($server)->deleteFiles('/', [$previous]);
                        break;
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
            try {
                $wings($server)->renameFiles('/', [['from' => $target, 'to' => $previous]]);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        try {
            $wings($server)->pull($url, '/', ['filename' => $target]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to start the download on Wings: ' . $e->getMessage()], 500);
        }

        return response()->json(['status' => 'success', 'target' => $target]);
    });

    /* ------------------------------------------------------------------ */
    /* Server icon (server-icon.png)                                       */
    /* ------------------------------------------------------------------ */
    Route::get('/icon', function (Server $server) use ($wings) {
        try {
            $bytes = $wings($server)->getContent('/server-icon.png', 2_000_000);
            $mime = function_exists('imagecreatefromstring') && @getimagesizefromstring($bytes) ? 'image/png' : 'image/png';

            return response()->json(['icon' => 'data:' . $mime . ';base64,' . base64_encode($bytes)]);
        } catch (\Throwable $e) {
            return response()->json(['icon' => null]);
        }
    });

    Route::post('/icon/generate', function (Request $request, Server $server) use ($wings) {
        $text = strtoupper(Str::substr(trim((string) $request->json('text', 'MC')), 0, 4)) ?: 'MC';
        $background = ltrim((string) $request->json('background', '#2E86C1'), '#');
        $fontColor = ltrim((string) $request->json('font_color', '#FFFFFF'), '#');
        $background = preg_match('/^[0-9a-fA-F]{6}$/', $background) ? $background : '2E86C1';
        $fontColor = preg_match('/^[0-9a-fA-F]{6}$/', $fontColor) ? $fontColor : 'FFFFFF';
        if (!function_exists('imagecreatetruecolor')) {
            return response()->json(['icon' => null, 'message' => 'GD extension missing, icon cannot be generated'], 500);
        }
        try {
            $size = 64;
            $bgHex = hexdec($background);
            $fgHex = hexdec($fontColor);
            $bgAlloc = [($bgHex >> 16) & 0xFF, ($bgHex >> 8) & 0xFF, $bgHex & 0xFF];
            $fgAlloc = [($fgHex >> 16) & 0xFF, ($fgHex >> 8) & 0xFF, $fgHex & 0xFF];

            $fw = imagefontwidth(5);
            $fh = imagefontheight(5);
            $tw = $fw * strlen($text);
            $scale = 4;
            while ($tw * $scale > $size - 8 && $scale > 1) {
                $scale--;
            }
            $img = imagecreatetruecolor($size, $size);
            $bg = imagecolorallocate($img, $bgAlloc[0], $bgAlloc[1], $bgAlloc[2]);
            imagefill($img, 0, 0, $bg);

            $tmp = imagecreate($tw, $fh);
            $tbg = imagecolorallocate($tmp, $bgAlloc[0], $bgAlloc[1], $bgAlloc[2]);
            $tfg = imagecolorallocate($tmp, $fgAlloc[0], $fgAlloc[1], $fgAlloc[2]);
            imagefill($tmp, 0, 0, $tbg);
            imagestring($tmp, 5, 0, 0, $text, $tfg);

            $dx = (int) (($size - $tw * $scale) / 2);
            $dy = (int) (($size - $fh * $scale) / 2);
            imagecopyresized($img, $tmp, $dx, $dy, 0, 0, $tw * $scale, $fh * $scale, $tw, $fh);
            imagedestroy($tmp);

            ob_start();
            imagepng($img);
            $png = ob_get_clean();
            imagedestroy($img);

            $wings($server)->putContent('/server-icon.png', $png);
            $data = 'data:image/png;base64,' . base64_encode($png);

            return response()->json(['icon' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['icon' => null, 'message' => 'Icon generation failed'], 500);
        }
    });

    Route::post('/icon/upload', function (Request $request, Server $server) use ($wings) {
        $url = trim((string) $request->json('image_url', ''));
        if ($url === '' || !Str::startsWith($url, ['http://', 'https://'])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid image URL'], 422);
        }
        try {
            $res = Http::timeout(15)->get($url);
            if (!$res->successful()) {
                return response()->json(['status' => 'error', 'message' => 'Failed to download image'], 422);
            }
            $mime = $res->header('Content-Type');
            if (!is_string($mime) || !Str::startsWith($mime, 'image/')) {
                return response()->json(['status' => 'error', 'message' => 'URL did not return an image'], 422);
            }
            $body = $res->body();
            if (strlen($body) > 2 * 1024 * 1024) {
                return response()->json(['status' => 'error', 'message' => 'Image is too large (max 2MB)'], 422);
            }
            $wings($server)->putContent('/server-icon.png', $body);

            return response()->json(['status' => 'success', 'icon' => 'data:' . $mime . ';base64,' . base64_encode($body)]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Download failed'], 422);
        }
    });

    Route::delete('/icon', function (Server $server) use ($wings) {
        try {
            $exists = false;
            foreach ($wings($server)->getDirectory('/') as $entry) {
                if (is_array($entry) && ($entry['name'] ?? '') === 'server-icon.png') {
                    $exists = true;
                    break;
                }
            }
            if ($exists) {
                $wings($server)->deleteFiles('/', ['server-icon.png']);
            }
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to remove icon'], 500);
        }

        return response()->json(['status' => 'success']);
    });

    /* ------------------------------------------------------------------ */
    /* Config editor (root-level text files)                               */
    /* ------------------------------------------------------------------ */
    Route::get('/config', function (Server $server) use ($wings) {
        $files = [];
        try {
            foreach ($wings($server)->getDirectory('/') as $entry) {
                if (!is_array($entry) || !is_string($entry['name'] ?? null) || ($entry['directory'] ?? false)) {
                    continue;
                }
                $name = $entry['name'];
                if (preg_match('/^[A-Za-z0-9._-]+$/', $name) && !Str::startsWith($name, '.')) {
                    $files[] = ['name' => $name, 'size' => (int) ($entry['size'] ?? 0)];
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
        usort($files, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return response()->json(['files' => $files]);
    });

    Route::get('/config/{file}/raw', function (Server $server, string $file) use ($wings, $isSafeFile) {
        if (!$isSafeFile($file)) {
            return response()->json(['error' => 'Invalid file name'], 422);
        }
        try {
            $content = $wings($server)->getContent('/' . $file, 5_000_000);

            return response()->json(['file' => $file, 'content' => $content]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Failed to read file on Wings'], 500);
        }
    });

    Route::put('/config/{file}/raw', function (Request $request, Server $server, string $file) use ($wings, $isSafeFile) {
        if (!$isSafeFile($file)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid file name'], 422);
        }
        $content = (string) $request->json('content', '');

        try {
            $current = $wings($server)->getContent('/' . $file, 5_000_000);
            DB::table('minecrafttools_backups')->insert([
                'server_uuid' => $server->uuid,
                'file' => $file,
                'content' => $current,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $keep = DB::table('minecrafttools_backups')
                ->where('server_uuid', $server->uuid)
                ->where('file', $file)
                ->orderByDesc('id')
                ->limit(50)
                ->pluck('id');
            DB::table('minecrafttools_backups')
                ->where('server_uuid', $server->uuid)
                ->where('file', $file)
                ->whereNotIn('id', $keep)
                ->delete();
        } catch (\Throwable $e) {
            // no backup when the file cannot be read (e.g. does not exist yet)
        }

        try {
            $wings($server)->putContent('/' . $file, $content);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to save file on Wings'], 500);
        }

        return response()->json(['status' => 'success']);
    });

    Route::get('/config/{file}/backups', function (Server $server, string $file) use ($isSafeFile) {
        if (!$isSafeFile($file)) {
            return response()->json(['backups' => []], 422);
        }
        $backups = DB::table('minecrafttools_backups')
            ->where('server_uuid', $server->uuid)
            ->where('file', $file)
            ->orderByDesc('id')
            ->limit(50)
            ->get(['id', 'file', 'created_at'])
            ->map(fn ($row) => (array) $row)
            ->values();

        return response()->json(['backups' => $backups]);
    });

    Route::post('/config/{file}/restore/{backupId}', function (Server $server, string $file, int $backupId) use ($wings, $isSafeFile) {
        if (!$isSafeFile($file)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid file name'], 422);
        }
        $backup = DB::table('minecrafttools_backups')
            ->where('id', $backupId)
            ->where('server_uuid', $server->uuid)
            ->where('file', $file)
            ->first();
        if (!$backup) {
            return response()->json(['status' => 'error', 'message' => 'Backup not found'], 404);
        }
        try {
            $wings($server)->putContent('/' . $file, (string) $backup->content);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to restore backup on Wings'], 500);
        }

        return response()->json(['status' => 'success']);
    });
});