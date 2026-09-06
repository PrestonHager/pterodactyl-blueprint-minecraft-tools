<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Pterodactyl\Models\Server;

/*
|--------------------------------------------------------------------------
| Minecraft Tools admin (configuration) routes
|--------------------------------------------------------------------------
|
| The admin area no longer manages server files. That responsibility moved
| to the per-server client API (routes/client.php) which is accessible from
| the user-facing server pages. This router only provides the extension
| settings screen and the provider configuration backing searches done on
| the per-server plugin / mod pages.
|
*/

Route::middleware(['auth', \Pterodactyl\Http\Middleware\AdminAuthenticate::class])->group(function () {
    Route::match(['get'], '/', function () {
        return view('{viewcontext}.admin.view');
    })->name('mt.index');

    Route::match(['get'], '/settings', function () {
        return view('{viewcontext}.admin.view');
    })->name('mt.settings');
});

Route::middleware(['auth', \Pterodactyl\Http\Middleware\AdminAuthenticate::class])
    ->prefix('/api')
    ->group(function () {
        $kvGet = function (string $key, mixed $default = null): mixed {
            try {
                $raw = DB::table('minecrafttools_keyvalues')->where('key', $key)->value('value');
            } catch (\Throwable $e) {
                return $default;
            }
            if (is_null($raw) || $raw === '') {
                return $default;
            }
            $decoded = json_decode($raw, true);

            return $decoded ?? $default;
        };
        $kvSet = function (string $key, mixed $value): void {
            DB::table('minecrafttools_keyvalues')->updateOrInsert(
                ['key' => $key],
                ['value' => json_encode($value), 'updated_at' => now()],
            );
        };

        $defaultSection = static fn (): array => [
            'provider' => 'modrinth',
            'base_url' => '',
            'api_key' => '',
            'enabled' => true,
        ];

        $providerConfig = function () use ($kvGet, $defaultSection): array {
            $cfg = $kvGet('provider_config', []);
            $cfg = is_array($cfg) ? $cfg : [];
            $cfg['plugins'] = array_merge($defaultSection(), is_array($cfg['plugins'] ?? null) ? $cfg['plugins'] : []);
            $cfg['mods'] = array_merge($defaultSection(), is_array($cfg['mods'] ?? null) ? $cfg['mods'] : []);

            return $cfg;
        };

        Route::get('/settings', function () use ($providerConfig) {
            return response()->json(['config' => $providerConfig()]);
        });

        Route::put('/settings', function (Request $request) use ($kvSet, $providerConfig, $defaultSection) {
            $current = $providerConfig();
            $input = $request->json('config');
            if (!is_array($input)) {
                return response()->json(['status' => 'error', 'message' => 'Invalid configuration payload'], 422);
            }

            foreach (['plugins', 'mods'] as $kind) {
                $section = is_array($input[$kind] ?? null) ? $input[$kind] : $defaultSection();
                $provider = strtolower((string) ($section['provider'] ?? 'modrinth'));
                if (!in_array($provider, ['modrinth', 'hangar', 'spigot'], true)) {
                    $provider = 'modrinth';
                }
                $baseUrl = trim((string) ($section['base_url'] ?? ''));
                if ($baseUrl !== '' && !preg_match('#^https?://#i', $baseUrl)) {
                    return response()->json(['status' => 'error', 'message' => "Base URL for {$kind} must start with http(s)://"], 422);
                }
                $current[$kind] = [
                    'provider' => $provider,
                    'base_url' => $baseUrl,
                    'api_key' => trim((string) ($section['api_key'] ?? '')),
                    'enabled' => (bool) ($section['enabled'] ?? true),
                ];
            }

            $kvSet('provider_config', $current);

            return response()->json(['status' => 'success', 'config' => $current]);
        });

        Route::get('/status', function () {
            $servers = 0;
            $first = null;
            try {
                $servers = Server::query()->count();
                $first = Server::query()->orderBy('id')->value('name');
            } catch (\Throwable $e) {
                // ignore
            }

            return response()->json(['servers' => $servers, 'first_server' => $first]);
        });
    });