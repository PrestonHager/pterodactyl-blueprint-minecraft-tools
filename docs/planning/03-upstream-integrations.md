# Upstream Integrations Planning Document

## Overview

Minecraft Tools integrates with multiple external APIs to provide plugin, mod, and modpack discovery and installation. This document details each integration, their API specifications, rate limits, authentication requirements, and implementation strategy.

---

## Integration Architecture

```
+------------------------+     +--------------------------+
|                        |     |                         |
|  Minecraft Tools       |     |  External APIs           |
|  Extension             |     |  - Modrinth API v2       |
|  |-------------------| |     |  - CurseForge API v1    |
|  | API Client Layer  | |---->|  - Spiget API           |
|  | (Http + Cache)    | |     |  - PaperMC API          |
|  |-------------------| |     |  - Fabric Meta          |
|  | Service Layer     | |     |  - Technic API          |
|  | (Business Logic)  | |     |  - FTB API              |
|  |-------------------| |     |                         |
|  | Repository Layer  | |     |                         |
|  | (DB Persistence)  | |     |                         |
|  |-------------------| |     |                         |
+------------------------+     +--------------------------+
         |                              |
         v                              v
   User Interface               Cached Responses (Redis)
```

### Core Components

| Component | File | Purpose |
|-----------|------|---------|
| API Client | `Services/ExternalApiClient.php` | HTTP client base with rate limiting |
| Response Cache | `Services/ApiCacheManager.php` | Redis-backed caching |
| Rate Limiter | `Services/RateLimiter.php` | Token bucket rate limiting |
| Queue Worker | `Jobs/InstallJob.php` | Background installation jobs |
| Preview Generator | `Services/PreviewGenerator.php` | Icon/mod preview generation |

---

## Integration: Modrinth

### Overview

Modrinth is a modern modding platform focused on open-source Minecraft mods and plugins. Supports CurseForge API compatibility in some aspects.

### API Details

| Item | Value |
|------|-------|
| Base URL | `https://api.modrinth.com/v2` |
| Rate Limit | 100 requests/second (generous) |
| Auth | Public (no key required for search) |
| API Docs | https://docs.modrinth.com |

### Features Used

| Feature | Endpoint | Purpose |
|---------|----------|---------|
| Search | `GET /search` | Search mods, plugins, modpacks |
| Project details | `GET /project/{id}` | Full mod/modpack details |
| Project versions | `GET /project/{id}/version` | Available versions |
| Version details | `GET /version/{id}` | Specific version info |
| Team members | `GET /project/{id}/members` | Author info |
| Tag categories | `GET /tag/category` | Filter categories |
| Tag loaders | `GET /tag/loader` | Loader types |
| Tag game versions | `GET /tag/game_version` | Minecraft versions |
| Download | `GET /version/{id}/download` | Download jar/tarball |

### Implementation

```php
// Services/Integrations/ModrinthClient.php
class ModrinthClient
{
    protected array $config;
    
    public function search(array $filters): array
    {
        // /search?query=&facets=[["project_type:mod"], ...]
        $cacheKey = 'modrinth.search.' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 1800, function () use ($filters) {
            // Implement HTTP request
        });
    }
    
    public function getProject(string $id): ?array
    {
        return Cache::remember("modrinth.project.{$id}", 3600, function () use ($id) {
            // Implement
        });
    }
}
```

### Data Models

```php
class ModrinthProject
{
    public string $id;
    public string $slug;
    public string $title;
    public string $description;
    public string $project_type; // mod, modpack, plugin, datapack
    public array $categories;
    public string $client_side;
    public string $server_side;
    public array $downloads;
    public array $tags;
    public string $icon_url;
}
```

---

## Integration: CurseForge

### Overview

CurseForge is the largest modding platform, hosting mods, plugins, and modpacks for many games including Minecraft.

### API Details

| Item | Value |
|------|-------|
| Base URL | `https://api.curseforge.com/v1` |
| Rate Limit | 60 requests/minute |
| Auth | API Key required (header: `x-api-key`) |
| API Docs | https://docs.curseforge.com |

### Features Used

| Feature | Endpoint | Purpose |
|---------|----------|---------|
| Mod search | `POST /mods/search` | Search mods |
| Mod details | `GET /mods/{modId}` | Mod information |
| Mod files | `GET /mods/{modId}/files` | Available files |
| File details | `GET /mods/{modId}/files/{fileId}` | File info |
| Mod description | `GET /mods/{modId}/description` | Description |
| Minecraft version | `GET /minecraft/version` | List versions |
| Mod pack search | `POST /mods/search?gameId=432&classId=4471` | Modpacks |

### Implementation

```php
// Services/Integrations/CurseForgeClient.php
class CurseForgeClient
{
    protected string $apiKey;
    
    public function search(array $filters): array
    {
        // Cache results
        return $this->request('POST', '/mods/search', $filters);
    }
    
    private function request(string $method, string $path, array $data = []): array
    {
        // Attach API key header
        // Handle rate limiting
        // Cache responses
    }
}
```

### Authentication

```env
MINECRAFT_TOOLS_CURSEFORGE_API_KEY=your_api_key_here
```

---

## Integration: SpigotMC (Spiget)

### Overview

Spiget is a community API for the SpigotMC plugin repository. Provides access to Spigot plugin resources.

### API Details

| Item | Value |
|------|-------|
| Base URL | `https://api.spiget.org/v2` |
| Rate Limit | ~60 requests/minute |
| Auth | None required |
| API Docs | https://api.spiget.org |

### Features Used

| Feature | Endpoint | Purpose |
|---------|----------|---------|
| Resource search | `GET /search/resources/{query}` | Search plugins |
| Resource details | `GET /resources/{id}` | Plugin info |
| Resource versions | `GET /resources/{id}/versions` | Version list |
| Resource updates | `GET /resources/{id}/updates` | Updates |
| Author info | `GET /resources/{id}/author` | Author |
| Categories | `GET /categories` | Categories |

### Implementation

```php
// Services/Integrations/SpigetClient.php
class SpigetClient
{
    public function search(string $query, array $options = []): array
    {
        // /search/resources/{query}
        // Cache for 30 minutes
    }
}
```

### Notes

- SpigotMC downloads often have reCAPTCHA protection
- May need to use direct download URLs or mirror services
- Caching is essential due to connection reliability

---

## Integration: PaperMC

### Overview

PaperMC provides build artifacts for the Paper, Folia, and Velocity server software.

### API Details

| Item | Value |
|------|-------|
| Base URL | `https://api.papermc.io/v2` |
| Rate Limit | Unknown, be conservative |
| Auth | None required |
| API Docs | https://papermc.io/api/docs/swagger-ui/index.html |

### Features Used

| Feature | Endpoint | Purpose |
|---------|----------|---------|
| Project info | `GET /projects/{project}` | Available versions |
| Version builds | `GET /projects/{project}/versions/{version}` | Build list |
| Build details | `GET /projects/{project}/versions/{version}/builds/{build}` | Build info |
| Download | `GET /projects/{project}/versions/{version}/builds/{build}/downloads/{download}` | Download jar |

### Supported Projects

| Project | ID |
|---------|-----|
| Paper | `paper` |
| Folia | `folia` |
| Velocity | `velocity` |
| Waterfall | `waterfall` |

### Implementation

```php
// Services/Integrations/PaperMcClient.php
class PaperMcClient
{
    public function getVersions(string $project): array
    {
        return Cache::remember("papermc.{$project}.versions", 3600, function () {
            // Implement
        });
    }
    
    public function getBuilds(string $project, string $version): array
    {
        return Cache::remember("papermc.{$project}.{$version}.builds", 1800, function () {
            // Implement
        });
    }
    
    public function getDownloadUrl(string $project, string $version, int $build): string
    {
        // Build download URL
    }
}
```

---

## Integration: Fabric Meta

### Overview

Fabric provides meta services for Fabric API, loaders, and installer versions.

### API Details

| Item | Value |
|------|-------|
| Base URL | `https://meta.fabricmc.net` |
| Rate Limit | Unknown, be conservative |
| Auth | None required |
| API Docs | https://meta.fabricmc.net |

### Features Used

| Feature | Endpoint | Purpose |
|---------|----------|---------|
| Game versions | `GET /v2/versions/game` | Minecraft versions |
| Loader versions | `GET /v2/versions/loader` | Fabric loaders |
| Installer versions | `GET /v2/versions/installer` | Installer |
| Intermediary mappings | `GET /v2/versions/intermediary/{version}` | Mappings |
| Full version | `GET /v2/versions/loader/{game}/{loader}` | Combined info |

### Implementation

```php
// Services/Integrations/FabricMetaClient.php
class FabricMetaClient
{
    public function getGameVersions(): array
    {
        return Cache::remember('fabric.game_versions', 3600, function () {
            // Implement
        });
    }
}
```

---

## Integration: Technic

### Overview

Technic provides modpack distribution through their platform.

### API Details

| Item | Value |
|------|-------|
| Base URL | `https://api.technicpack.net` |
| Rate Limit | ~30 requests/minute |
| Auth | None required |
| API Docs | Community maintained |

### Features Used

| Feature | Endpoint | Purpose |
|---------|----------|---------|
| Modpack list | `GET /modpack` | Available modpacks |
| Modpack details | `GET /modpack/{slug}` | Modpack info |
| Modpack versions | Included in details | Version list |

---

## Integration: FTB (Feed The Beast)

### Overview

FTB provides official modpacks through their API.

### API Details

| Item | Value |
|------|-------|
| Base URL | `https://api.feed-the-beast.com` |
| Rate Limit | ~30 requests/minute |
| Auth | None required |
| API Docs | https://docs.ftb.team |

### Features Used

| Feature | Endpoint | Purpose |
|---------|----------|---------|
| Modpack list | `GET /v1/modpacks` | Available modpacks |
| Modpack details | `GET /v1/modpacks/{id}` | Modpack info |
| Modpack versions | `GET /v1/modpacks/{id}/versions` | Version list |

---

## Integration: Vanilla Mojang

### Overview

For vanilla Minecraft, use Mojang's version manifest.

### API Details

| Item | Value |
|------|-------|
| Base URL | `https://launchermeta.mojang.com/mc/game/version_manifest_v2.json` |
| Rate Limit | Low (static manifest) |
| Auth | None required |

### Features Used

| Feature | Endpoint | Purpose |
|---------|----------|---------|
| Version manifest | `GET /version_manifest_v2.json` | All versions |

---

## Unified Search Service

### Search Abstraction

Provide a unified search interface across all sources:

```php
// Services/SearchService.php
class SearchService
{
    public function searchAll(string $query, array $filters = []): array
    {
        $results = [];
        
        if (in_array('modrinth', $filters['sources'] ?? ['all'])) {
            $results['modrinth'] = $this->modrinth->search($query, $filters);
        }
        
        if (in_array('curseforge', $filters['sources'] ?? ['all'])) {
            $results['curseforge'] = $this->curseforge->search($query, $filters);
        }
        
        if (in_array('spigot', $filters['sources'] ?? ['all'])) {
            $results['spigot'] = $this->spiget->search($query, $filters);
        }
        
        return $results;
    }
}
```

### Search Result Normalization

Normalize results from all sources into a common format:

```php
class SearchResult
{
    public string $source;          // modrinth, curseforge, spigot
    public string $external_id;     // ID in the source system
    public string $name;
    public string $description;
    public string $author;
    public string $category;
    public array $versions;
    public string $download_url;
    public string $icon_url;
    public array $metadata;         // Source-specific data
}
```

---

## Caching Strategy

### Cache Layers

| Layer | Key Pattern | TTL | Purpose |
|-------|-------------|-----|---------|
| Search results | `search.{query}.{filters}` | 30 min | Search API responses |
| Project details | `project.{source}.{id}` | 60 min | Single project info |
| Versions | `versions.{source}.{project_id}` | 30 min | Version lists |
| Downloads | `download.{project_id}` | 60 min | Download URLs |
| Static manifests | `manifest.{type}` | 24 hr | Vanilla/Fabric manifests |

### Cache Invalidation

- Manual invalidation on plugin/modpack install
- Automatic expiry (TTL)
- Version bumps that change metadata

---

## Job Queue Architecture

### Installation Jobs

```php
// Jobs/InstallPluginJob.php
class InstallPluginJob implements ShouldQueue
{
    public function handle(): void
    {
        // 1. Download jar from source
        // 2. Upload to server via Pterodactyl API
        // 3. Update plugin config
        // 4. Notify user
    }
}
```

### Queue Priorities

| Priority | Jobs | Queue |
|----------|------|-------|
| High | Version switches, restarts | `minecraft-tools-high` |
| Normal | Plugin installs, modpack installs | `minecraft-tools` |
| Low | Update checks, backup cleanup | `minecraft-tools-low` |

---

## Milestones

| Milestone | Integration | Target | Deliverables |
|-----------|-------------|--------|--------------|
| M3.1 | Modrinth | Week 4 | Search, project details, install |
| M3.2 | PaperMC | Week 5 | Version/build listing, download |
| M3.3 | CurseForge | Week 6 | Search, auth, install |
| M3.4 | SpigotMC | Week 7 | Search, install |
| M3.5 | Fabric + Vanilla | Week 8 | Version management |
| M3.6 | Technic + FTB | Week 9 | Modpack support |
| M3.7 | Unified search | Week 10 | Multi-source search |

---

## Configuration

### Config File Extensions

```php
// config/minecraft-tools.php
'api' => [
    'modrinth' => [
        'base_url' => 'https://api.modrinth.com/v2',
        'rate_limit' => 100,
        'cache_ttl' => 1800,
    ],
    'curseforge' => [
        'base_url' => 'https://api.curseforge.com/v1',
        'api_key' => env('MINECRAFT_TOOLS_CURSEFORGE_API_KEY'),
        'rate_limit' => 60,
        'cache_ttl' => 1800,
    ],
    'spigot' => [
        'base_url' => 'https://api.spiget.org/v2',
        'rate_limit' => 60,
        'cache_ttl' => 1800,
    ],
    'papermc' => [
        'base_url' => 'https://api.papermc.io/v2',
        'rate_limit' => 60,
        'cache_ttl' => 3600,
    ],
    'fabric' => [
        'base_url' => 'https://meta.fabricmc.net',
        'rate_limit' => 60,
        'cache_ttl' => 3600,
    ],
    'technic' => [
        'base_url' => 'https://api.technicpack.net',
        'rate_limit' => 30,
        'cache_ttl' => 3600,
    ],
    'ftb' => [
        'base_url' => 'https://api.feed-the-beast.com',
        'rate_limit' => 30,
        'cache_ttl' => 3600,
    ],
],
```

---

## Error Handling

### HTTP Error Mapping

| HTTP Code | Action |
|-----------|--------|
| 200-299 | Process normally |
| 400 | Invalid request, log and return error |
| 401 | Auth required, refresh credentials |
| 403 | Forbidden, alert admin |
| 404 | Not found, suggest alternatives |
| 429 | Rate limited, exponential backoff |
| 5xx | Retry with exponential backoff |

### Retry Logic

```
1st attempt: 0s delay
2nd attempt: 5s delay  
3rd attempt: 30s delay
4th attempt: 60s delay
Give up: Move to dead-letter queue
```

---

## Testing

### Unit Tests

- API response parsing (each integration)
- Search normalization
- URL construction
- Cache behavior
- Rate limiting

### Integration Tests

- Mock API responses (use fixtures)
- Verify JSON schemas match
- Test installation workflows
- Test multi-source search

### Test Fixtures

Store sample API responses in `tests/fixtures/`:
```
tests/fixtures/
├── modrinth/
│   ├── search.json
│   └── project.json
├── curseforge/
│   ├── search.json
│   └── mod.json
├── spigot/
│   ├── search.json
│   └── resource.json
└── papermc/
    └── versions.json
```
