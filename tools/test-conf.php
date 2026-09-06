<?php
$path = base_path('.blueprint/extensions/minecraft-tools/private/.store/conf.yml');
echo "Path: $path\n";
echo "Exists: " . (file_exists($path) ? 'yes' : 'no') . "\n";
if (file_exists($path)) {
    $content = file_get_contents($path);
    echo "Content: $content\n";
    $parsed = \Symfony\Component\Yaml\Yaml::parse($content);
    var_dump($parsed);
}
