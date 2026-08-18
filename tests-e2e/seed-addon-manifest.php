<?php

/**
 * Statamic's addon Manifest (Statamic\Addons\Manifest) only recognizes
 * packages listed in vendor/composer/installed.json — which never includes
 * the root package itself. When this addon is being served as the
 * Testbench workbench's own root package (rather than as an installed
 * dependency of some other app, like a real consumer would have it), it's
 * structurally invisible to Statamic's own addon discovery, so its
 * fieldtype/modifier/vite registration never boots.
 *
 * This seeds the cached manifest file directly, matching the shape
 * Statamic\Addons\Manifest::formatPackage() produces, so the addon is
 * recognized without needing to fake an actual composer install.
 */

$root = dirname(__DIR__);
$composerJson = json_decode(file_get_contents($root.'/composer.json'), true);

$provider = $composerJson['extra']['laravel']['providers'][0];
$namespace = implode('\\', array_slice(explode('\\', $provider), 0, -1));
$autoload = $composerJson['autoload']['psr-4'][$namespace.'\\'];
$statamic = $composerJson['extra']['statamic'] ?? [];
$author = $composerJson['authors'][0] ?? null;

$manifest = [
    $composerJson['name'] => [
        'id' => $composerJson['name'],
        'slug' => $statamic['slug'] ?? null,
        'editions' => $statamic['editions'] ?? [],
        'version' => 'dev-workbench',
        'raw_version' => 'dev-workbench',
        'namespace' => $namespace,
        'autoload' => $autoload,
        'provider' => $provider,
        'name' => $statamic['name'] ?? null,
        'url' => $statamic['url'] ?? null,
        'description' => $statamic['description'] ?? $composerJson['description'] ?? null,
        'developer' => $statamic['developer'] ?? $author['name'] ?? null,
        'developerUrl' => $statamic['developer-url'] ?? $author['homepage'] ?? null,
        'email' => $composerJson['support']['email'] ?? null,
    ],
];

$target = $root.'/vendor/orchestra/testbench-core/laravel/bootstrap/cache/addons.php';
file_put_contents($target, "<?php return ".var_export($manifest, true).";\n");

echo "Seeded addon manifest at {$target}\n";
