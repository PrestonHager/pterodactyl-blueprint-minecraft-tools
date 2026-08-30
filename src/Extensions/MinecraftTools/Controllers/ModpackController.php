<?php

namespace App\Extensions\MinecraftTools\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class ModpackController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $server = $this->getServer($request);
        $modpacks = $this->getInstalledModpacks($server);

        return response()->json([
            'modpacks' => $modpacks,
            'current' => $this->getCurrentModpack($server),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'version' => 'nullable|string',
            'source' => 'required|string|in:modrinth,curseforge,technic,ftb,local',
            'source_id' => 'nullable|string',
        ]);

        $server = $this->getServer($request);
        $modpack = $this->installModpack($server, $request->all());

        return response()->json([
            'status' => 'success',
            'modpack' => $modpack,
        ], 201);
    }

    public function show(Request $request, string $modpack): JsonResponse
    {
        $server = $this->getServer($request);
        $modpackData = $this->getModpackDetails($server, $modpack);

        return response()->json(['modpack' => $modpackData]);
    }

    public function update(Request $request, string $modpack): JsonResponse
    {
        $request->validate([
            'enabled' => 'boolean',
            'config' => 'nullable|array',
        ]);

        $server = $this->getServer($request);
        $updated = $this->updateModpack($server, $modpack, $request->all());

        return response()->json([
            'status' => 'success',
            'modpack' => $updated,
        ]);
    }

    public function destroy(Request $request, string $modpack): JsonResponse
    {
        $server = $this->getServer($request);
        $this->uninstallModpack($server, $modpack);

        return response()->json(['status' => 'uninstalled']);
    }

    public function install(Request $request, string $modpack): JsonResponse
    {
        $server = $this->getServer($request);
        $result = $this->installModpack($server, ['name' => $modpack]);

        return response()->json([
            'status' => 'installing',
            'modpack' => $result,
        ]);
    }

    public function uninstall(Request $request, string $modpack): JsonResponse
    {
        $server = $this->getServer($request);
        $this->uninstallModpack($server, $modpack);

        return response()->json(['status' => 'uninstalled']);
    }

    public function updateModpack(Request $request, string $modpack): JsonResponse
    {
        $server = $this->getServer($request);
        $result = $this->updateModpackVersion($server, $modpack);

        return response()->json([
            'status' => 'updating',
            'modpack' => $result,
        ]);
    }

    public function versions(Request $request, string $modpack): JsonResponse
    {
        $source = $request->query('source', 'modrinth');
        $versions = $this->getModpackVersions($modpack, $source);

        return response()->json(['versions' => $versions]);
    }

    public function switchVersion(Request $request, string $modpack): JsonResponse
    {
        $request->validate([
            'version' => 'required|string',
        ]);

        $server = $this->getServer($request);
        $result = $this->switchModpackVersion($server, $modpack, $request->input('version'));

        return response()->json([
            'status' => 'switching',
            'modpack' => $result,
        ]);
    }

    public function getConfig(Request $request, string $modpack): JsonResponse
    {
        $server = $this->getServer($request);
        $config = $this->getModpackConfig($server, $modpack);

        return response()->json(['config' => $config]);
    }

    public function updateConfig(Request $request, string $modpack): JsonResponse
    {
        $request->validate(['config' => 'required|array']);
        $server = $this->getServer($request);
        $config = $this->updateModpackConfig($server, $modpack, $request->input('config'));

        return response()->json([
            'status' => 'updated',
            'config' => $config,
        ]);
    }

    public function available(Request $request): JsonResponse
    {
        $source = $request->query('source', 'modrinth');
        $category = $request->query('category');
        $modpacks = $this->getAvailableModpacks($source, $category, $request->query('search'));

        return response()->json(['modpacks' => $modpacks]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string',
            'source' => 'nullable|string|in:modrinth,curseforge,technic,ftb',
        ]);

        $results = $this->searchModpacks($request->input('query'), $request->input('source'));

        return response()->json(['results' => $results]);
    }

    public function categories(Request $request): JsonResponse
    {
        $source = $request->query('source', 'modrinth');
        $categories = $this->getCategories($source);

        return response()->json(['categories' => $categories]);
    }

    public function files(Request $request, string $modpack): JsonResponse
    {
        $server = $this->getServer($request);
        $files = $this->getModpackFiles($server, $modpack);

        return response()->json(['files' => $files]);
    }

    public function backup(Request $request, string $modpack): JsonResponse
    {
        $server = $this->getServer($request);
        $backup = $this->createModpackBackup($server, $modpack);

        return response()->json([
            'status' => 'backing_up',
            'backup' => $backup,
        ]);
    }

    public function restore(Request $request, string $modpack): JsonResponse
    {
        $request->validate([
            'backup_id' => 'required|string',
        ]);

        $server = $this->getServer($request);
        $result = $this->restoreModpackBackup($server, $modpack, $request->input('backup_id'));

        return response()->json([
            'status' => 'restoring',
            'result' => $result,
        ]);
    }

    protected function getServer(Request $request)
    {
        return $request->user()->servers()->findOrFail($request->route('server'));
    }

    protected function getInstalledModpacks($server): array
    {
        return [];
    }

    protected function getCurrentModpack($server): ?string
    {
        return null;
    }

    protected function getModpackDetails($server, string $modpack): array
    {
        return ['name' => $modpack];
    }

    protected function installModpack($server, array $data): array
    {
        return $data;
    }

    protected function uninstallModpack($server, string $modpack): void
    {
    }

    protected function updateModpack($server, string $modpack, array $data): array
    {
        return array_merge(['name' => $modpack], $data);
    }

    protected function updateModpackVersion($server, string $modpack): array
    {
        return ['name' => $modpack, 'status' => 'updating'];
    }

    protected function getModpackVersions(string $modpack, string $source): array
    {
        return [];
    }

    protected function switchModpackVersion($server, string $modpack, string $version): array
    {
        return ['name' => $modpack, 'version' => $version];
    }

    protected function getModpackConfig($server, string $modpack): array
    {
        return [];
    }

    protected function updateModpackConfig($server, string $modpack, array $config): array
    {
        return $config;
    }

    protected function getAvailableModpacks(string $source, ?string $category, ?string $search): array
    {
        return [];
    }

    protected function searchModpacks(string $query, ?string $source): array
    {
        return [];
    }

    protected function getCategories(string $source): array
    {
        return [];
    }

    protected function getModpackFiles($server, string $modpack): array
    {
        return [];
    }

    protected function createModpackBackup($server, string $modpack): array
    {
        return ['id' => uniqid(), 'modpack' => $modpack];
    }

    protected function restoreModpackBackup($server, string $modpack, string $backupId): array
    {
        return ['backup_id' => $backupId];
    }
}