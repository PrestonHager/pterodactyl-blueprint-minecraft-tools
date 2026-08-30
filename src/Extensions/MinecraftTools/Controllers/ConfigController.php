<?php

namespace App\Extensions\MinecraftTools\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class ConfigController extends Controller
{
    protected array $configFiles = [
        'server.properties' => 'Server Properties',
        'spigot.yml' => 'Spigot Config',
        'bukkit.yml' => 'Bukkit Config',
        'paper.yml' => 'Paper Config',
        'pufferfish.yml' => 'Pufferfish Config',
        'purpur.yml' => 'Purpur Config',
        'fabric-server-launcher.properties' => 'Fabric Config',
        'forge-server.toml' => 'Forge Config',
        'neoforge-server.toml' => 'NeoForge Config',
    ];

    public function index(Request $request): JsonResponse
    {
        $server = $this->getServer($request);
        $configs = $this->getAllConfigs($server);

        return response()->json(['configs' => $configs]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|string',
            'config' => 'required|array',
        ]);

        $server = $this->getServer($request);
        $result = $this->updateConfigFile($server, $request->input('file'), $request->input('config'));

        return response()->json([
            'status' => 'updated',
            'config' => $result,
        ]);
    }

    public function show(Request $request, string $file): JsonResponse
    {
        $server = $this->getServer($request);
        $config = $this->getConfigFile($server, $file);

        return response()->json(['config' => $config]);
    }

    public function updateFile(Request $request, string $file): JsonResponse
    {
        $request->validate(['config' => 'required|array']);
        $server = $this->getServer($request);
        $config = $this->updateConfigFile($server, $file, $request->input('config'));

        return response()->json([
            'status' => 'updated',
            'config' => $config,
        ]);
    }

    public function getRaw(Request $request, string $file): JsonResponse
    {
        $server = $this->getServer($request);
        $content = $this->getRawConfig($server, $file);

        return response()->json(['content' => $content]);
    }

    public function updateRaw(Request $request, string $file): JsonResponse
    {
        $request->validate(['content' => 'required|string']);
        $server = $this->getServer($request);
        $this->updateRawConfig($server, $file, $request->input('content'));

        return response()->json(['status' => 'updated']);
    }

    public function backup(Request $request, string $file): JsonResponse
    {
        $server = $this->getServer($request);
        $backup = $this->createConfigBackup($server, $file);

        return response()->json([
            'status' => 'backed_up',
            'backup' => $backup,
        ]);
    }

    public function restore(Request $request, string $file): JsonResponse
    {
        $request->validate([
            'backup_id' => 'required|string',
        ]);

        $server = $this->getServer($request);
        $this->restoreConfigBackup($server, $file, $request->input('backup_id'));

        return response()->json(['status' => 'restored']);
    }

    public function listFiles(Request $request): JsonResponse
    {
        $server = $this->getServer($request);
        $files = $this->listConfigFiles($server);

        return response()->json(['files' => $files]);
    }

    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|string',
            'config' => 'required|array',
        ]);

        $server = $this->getServer($request);
        $errors = $this->validateConfig($server, $request->input('file'), $request->input('config'));

        return response()->json(['valid' => empty($errors), 'errors' => $errors]);
    }

    public function templates(Request $request): JsonResponse
    {
        $type = $request->query('type', 'paper');
        $templates = $this->getTemplates($type);

        return response()->json(['templates' => $templates]);
    }

    public function applyTemplate(Request $request, string $template): JsonResponse
    {
        $server = $this->getServer($request);
        $result = $this->applyTemplate($server, $template, $request->input('file'));

        return response()->json([
            'status' => 'applied',
            'config' => $result,
        ]);
    }

    protected function getServer(Request $request)
    {
        return $request->user()->servers()->findOrFail($request->route('server'));
    }

    protected function getAllConfigs($server): array
    {
        return [];
    }

    protected function getConfigFile($server, string $file): array
    {
        return ['file' => $file, 'config' => []];
    }

    protected function updateConfigFile($server, string $file, array $config): array
    {
        return ['file' => $file, 'config' => $config];
    }

    protected function getRawConfig($server, string $file): string
    {
        return '';
    }

    protected function updateRawConfig($server, string $file, string $content): void
    {
    }

    protected function createConfigBackup($server, string $file): array
    {
        return ['id' => uniqid(), 'file' => $file];
    }

    protected function restoreConfigBackup($server, string $file, string $backupId): void
    {
    }

    protected function listConfigFiles($server): array
    {
        return array_keys($this->configFiles);
    }

    protected function validateConfig($server, string $file, array $config): array
    {
        return [];
    }

    protected function getTemplates(string $type): array
    {
        return [];
    }

    protected function applyTemplate($server, string $template, ?string $file): array
    {
        return ['template' => $template];
    }
}