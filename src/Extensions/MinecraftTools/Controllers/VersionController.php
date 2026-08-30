<?php

namespace App\Extensions\MinecraftTools\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class VersionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $server = $this->getServer($request);
        $versions = $this->getInstalledVersions($server);

        return response()->json([
            'versions' => $versions,
            'current' => $this->getCurrentVersion($server),
        ]);
    }

    public function install(Request $request): JsonResponse
    {
        $request->validate([
            'version' => 'required|string',
            'type' => 'required|string|in:vanilla,paper,spigot,purpur,fabric,forge,quilt,neoforge',
            'build' => 'nullable|string',
        ]);

        $server = $this->getServer($request);
        $result = $this->installVersion($server, $request->all());

        return response()->json([
            'status' => 'installing',
            'version' => $result,
        ], 201);
    }

    public function show(Request $request, string $version): JsonResponse
    {
        $server = $this->getServer($request);
        $versionData = $this->getVersionDetails($server, $version);

        return response()->json(['version' => $versionData]);
    }

    public function destroy(Request $request, string $version): JsonResponse
    {
        $server = $this->getServer($request);
        $this->removeVersion($server, $version);

        return response()->json(['status' => 'removed']);
    }

    public function reinstall(Request $request, string $version): JsonResponse
    {
        $server = $this->getServer($request);
        $result = $this->reinstallVersion($server, $version);

        return response()->json([
            'status' => 'reinstalling',
            'version' => $result,
        ]);
    }

    public function available(Request $request): JsonResponse
    {
        $type = $request->query('type', 'paper');
        $versions = $this->getAvailableVersions($type);

        return response()->json(['versions' => $versions]);
    }

    public function current(Request $request): JsonResponse
    {
        $server = $this->getServer($request);
        $current = $this->getCurrentVersion($server);

        return response()->json(['current' => $current]);
    }

    public function switch(Request $request): JsonResponse
    {
        $request->validate([
            'version' => 'required|string',
            'type' => 'required|string',
        ]);

        $server = $this->getServer($request);
        $result = $this->switchVersion($server, $request->input('version'), $request->input('type'));

        return response()->json([
            'status' => 'switching',
            'version' => $result,
        ]);
    }

    public function builds(Request $request, string $version): JsonResponse
    {
        $type = $request->query('type', 'paper');
        $builds = $this->getBuilds($version, $type);

        return response()->json(['builds' => $builds]);
    }

    public function checkUpdates(Request $request): JsonResponse
    {
        $server = $this->getServer($request);
        $updates = $this->checkForUpdates($server);

        return response()->json(['updates' => $updates]);
    }

    protected function getServer(Request $request)
    {
        return $request->user()->servers()->findOrFail($request->route('server'));
    }

    protected function getInstalledVersions($server): array
    {
        return [];
    }

    protected function getCurrentVersion($server): ?string
    {
        return null;
    }

    protected function getVersionDetails($server, string $version): array
    {
        return ['version' => $version];
    }

    protected function installVersion($server, array $data): array
    {
        return $data;
    }

    protected function removeVersion($server, string $version): void
    {
    }

    protected function reinstallVersion($server, string $version): array
    {
        return ['version' => $version, 'status' => 'reinstalling'];
    }

    protected function getAvailableVersions(string $type): array
    {
        return [];
    }

    protected function switchVersion($server, string $version, string $type): array
    {
        return ['version' => $version, 'type' => $type];
    }

    protected function getBuilds(string $version, string $type): array
    {
        return [];
    }

    protected function checkForUpdates($server): array
    {
        return [];
    }
}