<?php

namespace App\Extensions\MinecraftTools\Tests\Unit\Services;

use App\Extensions\MinecraftTools\Services\MinecraftService;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\TestCase;

class FakeCache
{
    private array $store = [];

    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        if (array_key_exists($key, $this->store)) {
            return $this->store[$key];
        }
        return $this->store[$key] = $callback();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store[$key] ?? $default;
    }

    public function put(string $key, mixed $value, int $ttl = 0): bool
    {
        $this->store[$key] = $value;
        return true;
    }
}

class MinecraftServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $app = new Container();
        Container::setInstance($app);
        $app->instance('config', new Repository([
            'minecraft-tools.api' => [
                'paper' => ['base_url' => 'https://fallback.example.test'],
            ],
        ]));

        $app->bind('events', fn () => new Dispatcher($app));

        $fakeCache = new FakeCache();
        $app->instance('cache', $fakeCache);
        $app->instance('cache.store', $fakeCache);

        Facade::setFacadeApplication($app);
        Cache::swap($fakeCache);
        Http::swap(new HttpFactory());
    }

    public function test_it_uses_the_constructor_config_for_upstream_calls(): void
    {
        Http::fake([
            'https://constructor.example.test/projects/paper' => Http::response(['versions' => ['1.21.1']]),
        ]);

        $service = new MinecraftService([
            'paper' => ['base_url' => 'https://constructor.example.test'],
        ]);

        $this->assertSame(['1.21.1'], $service->getPaperVersions());
        Http::assertSent(fn ($request) => $request->url() === 'https://constructor.example.test/projects/paper');
    }
}
