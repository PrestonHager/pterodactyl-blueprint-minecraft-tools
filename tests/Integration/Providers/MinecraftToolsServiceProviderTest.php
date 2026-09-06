<?php

namespace App\Extensions\MinecraftTools\Tests\Integration\Providers;

use App\Extensions\MinecraftTools\MinecraftToolsServiceProvider;
use App\Extensions\MinecraftTools\Services\MinecraftService;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;

class MinecraftToolsServiceProviderTest extends TestCase
{
    public function test_it_registers_the_service_binding(): void
    {
        $app = new Container();
        Container::setInstance($app);

        $app->instance('config', new Repository([
            'minecraft-tools.api' => [
                'paper' => ['base_url' => 'https://example.test'],
            ],
        ]));

        $provider = new MinecraftToolsServiceProvider($app);
        $provider->register();

        $this->assertTrue($app->bound(MinecraftService::class));
        $this->assertInstanceOf(MinecraftService::class, $app->make(MinecraftService::class));
    }
}
