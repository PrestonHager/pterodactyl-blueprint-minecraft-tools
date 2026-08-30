<?php

namespace App\Extensions\MinecraftTools\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class IconController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $server = $this->getServer($request);
        $icon = $this->getCurrentIcon($server);

        return response()->json(['icon' => $icon]);
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:1024|dimensions:min_width=64,min_height=64,max_width=64,max_height=64',
        ]);

        $server = $this->getServer($request);
        $icon = $this->uploadIcon($server, $request->file('image'));

        return response()->json([
            'status' => 'uploaded',
            'icon' => $icon,
        ]);
    }

    public function delete(Request $request): JsonResponse
    {
        $server = $this->getServer($request);
        $this->deleteIcon($server);

        return response()->json(['status' => 'deleted']);
    }

    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:1024',
        ]);

        $preview = $this->generatePreview($request->file('image'));

        return response()->json(['preview' => $preview]);
    }

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'nullable|string|max:20',
            'background' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'font_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'font_size' => 'nullable|integer|min:10|max:30',
        ]);

        $icon = $this->generateIcon(
            $request->input('text', 'MC'),
            $request->input('background', '#2E86C1'),
            $request->input('font_color', '#FFFFFF'),
            $request->input('font_size', 24)
        );

        return response()->json(['icon' => $icon]);
    }

    public function templates(Request $request): JsonResponse
    {
        $templates = $this->getTemplates();

        return response()->json(['templates' => $templates]);
    }

    public function applyTemplate(Request $request, string $template): JsonResponse
    {
        $server = $this->getServer($request);
        $icon = $this->applyTemplate($server, $template);

        return response()->json([
            'status' => 'applied',
            'icon' => $icon,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $server = $this->getServer($request);
        $history = $this->getIconHistory($server);

        return response()->json(['history' => $history]);
    }

    public function restore(Request $request, string $id): JsonResponse
    {
        $server = $this->getServer($request);
        $icon = $this->restoreIcon($server, $id);

        return response()->json([
            'status' => 'restored',
            'icon' => $icon,
        ]);
    }

    protected function getServer(Request $request)
    {
        return $request->user()->servers()->findOrFail($request->route('server'));
    }

    protected function getCurrentIcon($server): string
    {
        return '/placeholder-icon.png';
    }

    protected function uploadIcon($server, $file): string
    {
        return '/uploads/icon.png';
    }

    protected function deleteIcon($server): void
    {
    }

    protected function generatePreview($file): string
    {
        return '/preview/icon.png';
    }

    protected function generateIcon(string $text, string $background, string $fontColor, int $fontSize): string
    {
        return '/generated/icon.png';
    }

    protected function getTemplates(): array
    {
        return [
            ['id' => 'default', 'name' => 'Default Minecraft', 'preview' => '/templates/default.png'],
            ['id' => 'survival', 'name' => 'Survival', 'preview' => '/templates/survival.png'],
            ['id' => 'creative', 'name' => 'Creative', 'preview' => '/templates/creative.png'],
            ['id' => 'minigames', 'name' => 'Minigames', 'preview' => '/templates/minigames.png'],
            ['id' => 'factions', 'name' => 'Factions', 'preview' => '/templates/factions.png'],
            ['id' => 'skyblock', 'name' => 'Skyblock', 'preview' => '/templates/skyblock.png'],
        ];
    }

    protected function applyTemplate($server, string $template): string
    {
        return "/templates/{$template}.png";
    }

    protected function getIconHistory($server): array
    {
        return [];
    }

    protected function restoreIcon($server, string $id): string
    {
        return "/history/{$id}.png";
    }
}