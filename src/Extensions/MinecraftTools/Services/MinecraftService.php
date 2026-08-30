<?php

namespace App\Extensions\MinecraftTools\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class MinecraftService
{
    protected array $apiConfig;

    public function __construct(array $config = [])
    {
        $this->apiConfig = config('minecraft-tools.api', []);
    }

    public function getSpigotPlugin(string $id): ?array
    {
        return Cache::remember("spigot:plugin:{$id}", 3600, function () use ($id) {
            $response = Http::timeout(10)->get("{$this->apiConfig['spigot']['base_url']}/resources/{$id}");
            return $response->successful() ? $response->json() : null;
        });
    }

    public function searchSpigotPlugins(string $query, int $size = 20): array
    {
        $cacheKey = "spigot:search:" . md5($query . $size);
        
        return Cache::remember($cacheKey, 1800, function () use ($query, $size) {
            $response = Http::timeout(10)->get("{$this->apiConfig['spigot']['base_url']}/search/resources/{$query}", [
                'size' => $size,
            ]);
            return $response->successful() ? $response->json() : [];
        });
    }

    public function getModrinthProject(string $id): ?array
    {
        return Cache::remember("modrinth:project:{$id}", 3600, function () use ($id) {
            $response = Http::timeout(10)->get("{$this->apiConfig['modrinth']['base_url']}/project/{$id}");
            return $response->successful() ? $response->json() : null;
        });
    }

    public function searchModrinth(string $query, array $filters = []): array
    {
        $cacheKey = "modrinth:search:" . md5($query . serialize($filters));
        
        return Cache::remember($cacheKey, 1800, function () use ($query, $filters) {
            $params = array_merge(['query' => $query], $filters);
            $response = Http::timeout(10)->get("{$this->apiConfig['modrinth']['base_url']}/search", $params);
            return $response->successful() ? $response->json() : ['hits' => []];
        });
    }

    public function getCurseforgeMod(int $id): ?array
    {
        $apiKey = $this->apiConfig['curseforge']['api_key'] ?? null;
        
        if (!$apiKey) {
            return null;
        }

        return Cache::remember("curseforge:mod:{$id}", 3600, function () use ($id, $apiKey) {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout(10)->get("{$this->apiConfig['curseforge']['base_url']}/mods/{$id}");
            
            return $response->successful() ? $response->json()['data'] ?? null : null;
        });
    }

    public function searchCurseforge(string $query, int $gameId = 432, int $pageSize = 20): array
    {
        $apiKey = $this->apiConfig['curseforge']['api_key'] ?? null;
        
        if (!$apiKey) {
            return ['data' => []];
        }

        $cacheKey = "curseforge:search:" . md5($query . $gameId . $pageSize);
        
        return Cache::remember($cacheKey, 1800, function () use ($query, $gameId, $pageSize, $apiKey) {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout(10)->post("{$this->apiConfig['curseforge']['base_url']}/mods/search", [
                'gameId' => $gameId,
                'searchFilter' => $query,
                'pageSize' => $pageSize,
                'sortField' => 2,
                'sortOrder' => 'desc',
            ]);
            
            return $response->successful() ? $response->json() : ['data' => []];
        });
    }

    public function getPaperVersions(): array
    {
        return Cache::remember('paper:versions', 3600, function () {
            $response = Http::timeout(10)->get("{$this->apiConfig['paper']['base_url']}/projects/paper");
            return $response->successful() ? $response->json()['versions'] ?? [] : [];
        });
    }

    public function getPaperBuilds(string $version): array
    {
        return Cache::remember("paper:builds:{$version}", 1800, function () use ($version) {
            $response = Http::timeout(10)->get("{$this->apiConfig['paper']['base_url']}/projects/paper/versions/{$version}/builds");
            return $response->successful() ? $response->json()['builds'] ?? [] : [];
        });
    }

    public function getFabricVersions(): array
    {
        return Cache::remember('fabric:versions', 3600, function () {
            $response = Http::timeout(10)->get("{$this->apiConfig['fabric']['base_url']}/v2/versions/game");
            return $response->successful() ? $response->json() : [];
        });
    }

    public function getFabricLoaders(): array
    {
        return Cache::remember('fabric:loaders', 3600, function () {
            $response = Http::timeout(10)->get("{$this->apiConfig['fabric']['base_url']}/v2/versions/loader");
            return $response->successful() ? $response->json() : [];
        });
    }

    public function downloadFile(string $url, string $path): bool
    {
        try {
            $response = Http::withOptions([
                'stream' => true,
            ])->timeout(300)->get($url);

            if (!$response->successful()) {
                return false;
            }

            $body = $response->getBody();
            $stream = $body->detach();
            
            Storage::disk('local')->writeStream($path, $stream);
            fclose($stream);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function generateIcon(string $text, string $background, string $fontColor, int $fontSize): string
    {
        $width = 64;
        $height = 64;
        
        $image = imagecreatetruecolor($width, $height);
        
        $bgColor = $this->hexToRgb($background);
        $fgColor = $this->hexToRgb($fontColor);
        
        $bg = imagecolorallocate($image, $bgColor[0], $bgColor[1], $bgColor[2]);
        $fg = imagecolorallocate($image, $fgColor[0], $fgColor[1], $fgColor[2]);
        
        imagefill($image, 0, 0, $bg);
        
        $fontFile = $this->getFontFile();
        
        if ($fontFile && file_exists($fontFile)) {
            $bbox = imagettfbbox($fontSize, 0, $fontFile, $text);
            $textWidth = $bbox[2] - $bbox[0];
            $textHeight = $bbox[1] - $bbox[7];
            
            $x = ($width - $textWidth) / 2;
            $y = ($height + $textHeight) / 2;
            
            imagettftext($image, $fontSize, 0, $x, $y, $fg, $fontFile, $text);
        } else {
            $fontWidth = imagefontwidth(5) * strlen($text);
            $fontHeight = imagefontheight(5);
            
            $x = ($width - $fontWidth) / 2;
            $y = ($height - $fontHeight) / 2;
            
            imagestring($image, 5, $x, $y, $text, $fg);
        }
        
        ob_start();
        imagepng($image);
        $pngData = ob_get_clean();
        imagedestroy($image);
        
        return 'data:image/png;base64,' . base64_encode($pngData);
    }

    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        return [
            hexdec($hex[0] . $hex[1]),
            hexdec($hex[2] . $hex[3]),
            hexdec($hex[4] . $hex[5]),
        ];
    }

    protected function getFontFile(): ?string
    {
        $fonts = [
            base_path('resources/fonts/DejaVuSans-Bold.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            '/Library/Fonts/Arial Bold.ttf',
        ];
        
        foreach ($fonts as $font) {
            if (file_exists($font)) {
                return $font;
            }
        }
        
        return null;
    }
}