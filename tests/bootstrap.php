<?php

$root = dirname(__DIR__);

if (!function_exists('env')) {
    function env(?string $key = null, mixed $default = null): mixed
    {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        return $value;
    }
}

$appAutoload = '/app/vendor/autoload.php';
$localAutoload = $root . '/vendor/autoload.php';

if (file_exists($appAutoload)) {
    require $appAutoload;
} elseif (file_exists($localAutoload)) {
    require $localAutoload;
}

if (!function_exists('config')) {
    function config(?string $key = null, mixed $default = null): mixed
    {
        $repo = app('config');
        if ($key === null) {
            return $repo;
        }
        $keys = explode('.', $key);
        $value = $repo->get($keys[0]);
        foreach (array_slice($keys, 1) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value ?? $default;
    }
}

if (!function_exists('app')) {
    function app(?string $abstract = null): mixed
    {
        if ($abstract === null) {
            return \Illuminate\Container\Container::getInstance();
        }
        return \Illuminate\Container\Container::getInstance()->make($abstract);
    }
}

if (!function_exists('data_get')) {
    function data_get(mixed $target, string|int|null $key, mixed $default = null): mixed
    {
        if ($key === null) {
            return $target;
        }
        $keys = explode('.', (string) $key);
        foreach ($keys as $segment) {
            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = $target[$segment];
            } else {
                return $default;
            }
        }
        return $target;
    }
}

spl_autoload_register(function (string $class) use ($root): void {
    $prefix = 'App\\Extensions\\MinecraftTools\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $root . '/src/Extensions/MinecraftTools/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});
