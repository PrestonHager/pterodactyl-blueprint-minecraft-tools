<?php

namespace App\Extensions\MinecraftTools\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class PluginController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $server = $this->getServer($request);
        $plugins = $this->getInstalledPlugins($server);
        
        return response()->json([
            'plugins' => $plugins,
            'server' => $server->uuid,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'version' => 'nullable|string',
            'source' => 'required|string|in:spigot,modrinth,curseforge,local',
            'source_id' => 'nullable|string',
        ]);

        $server = $this->getServer($request);
        $plugin = $this->installPlugin($server, $request->all());

        return response()->json([
            'status' => 'success',
            'plugin' => $plugin,
        ], 201);
    }

    public function show(Request $request, string $plugin): JsonResponse
    {
        $server = $this->getServer($request);
        $pluginData = $this->getPluginDetails($server, $plugin);

        return response()->json(['plugin' => $pluginData]);
    }

    public function update(Request $request, string $plugin): JsonResponse
    {
        $request->validate([
            'enabled' => 'boolean',
            'config' => 'nullable|array',
        ]);

        $server = $this->getServer($request);
        $updated = $this->updatePlugin($server, $plugin, $request->all());

        return response()->json([
            'status' => 'success',
            'plugin' => $updated,
        ]);
    }

    public function destroy(Request $request, string $plugin): JsonResponse
    {
        $server = $this->getServer($request);
        $this->uninstallPlugin($server, $plugin);

        return response()->json(['status' => 'success']);
    }

    public function install(Request $request, string $plugin): JsonResponse
    {
        $server = $this->getServer($request);
        $result = $this->installPlugin($server, ['name' => $plugin]);

        return response()->json([
            'status' => 'installing',
            'plugin' => $result,
        ]);
    }

    public function uninstall(Request $request, string $plugin): JsonResponse
    {
        $server = $this->getServer($request);
        $this->uninstallPlugin($server, $plugin);

        return response()->json(['status' => 'uninstalled']);
    }

    public function enable(Request $request, string $plugin): JsonResponse
    {
        $server = $this->getServer($request);
        $this->togglePlugin($server, $plugin, true);

        return response()->json(['status' => 'enabled']);
    }

    public function disable(Request $request, string $plugin): JsonResponse
    {
        $server = $this->getServer($request);
        $this->togglePlugin($server, $plugin, false);

        return response()->json(['status' => 'disabled']);
    }

    public function getConfig(Request $request, string $plugin): JsonResponse
    {
        $server = $this->getServer($request);
        $config = $this->getPluginConfig($server, $plugin);

        return response()->json(['config' => $config]);
    }

    public function updateConfig(Request $request, string $plugin): JsonResponse
    {
        $request->validate(['config' => 'required|array']);
        $server = $this->getServer($request);
        $config = $this->updatePluginConfig($server, $plugin, $request->input('config'));

        return response()->json([
            'status' => 'updated',
            'config' => $config,
        ]);
    }

    public function available(Request $request): JsonResponse
    {
        $source = $request->query('source', 'spigot');
        $plugins = $this->getAvailablePlugins($source, $request->query('search'));

        return response()->json(['plugins' => $plugins]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string',
            'source' => 'nullable|string|in:spigot,modrinth,curseforge',
        ]);

        $results = $this->searchPlugins($request->input('query'), $request->input('source'));

        return response()->json(['results' => $results]);
    }

    protected function getServer(Request $request)
    {
        return $request->user()->servers()->findOrFail($request->route('server'));
    }

    protected function getInstalledPlugins($server): array
    {
        return [];
    }

    protected function getPluginDetails($server, string $plugin): array
    {
        return ['name' => $plugin];
    }

    protected function installPlugin($server, array $data): array
    {
        return ['name' => $data['name'], 'status' => 'installing'];
    }

    protected function uninstallPlugin($server, string $plugin): void
    {
    }

    protected function togglePlugin($server, string $plugin, bool $enabled): void
    {
    }

    protected function getPluginConfig($server, string $plugin): array
    {
        return [];
    }

    protected function updatePluginConfig($server, string $plugin, array $config): array
    {
        return $config;
    }

    protected function getAvailablePlugins(string $source, ?string $search = null): array
    {
        return [];
    }

    protected function searchPlugins(string $query, ?string $source = null): array
    {
        return [];
    }
}