<?php

namespace ViewHelpers;

use Core\Path;

class MixViewHelper
{
    private static ?array $manifest = null;

    /**
     * Подключает файл из public по манифесту или с меткой версии
     */
    public function __invoke(string $path): string
    {
        $path = '/' . ltrim($path, '/');

        // 1. Пытаемся найти путь в manifest.json
        $manifestPath = $this->getManifestPath($path);
        if ($manifestPath) {
            return $manifestPath;
        }

        // 2. Фолбэк: если манифеста нет, используем метку времени (v=timestamp)
        $fullPath = Path::public($path);
        if (file_exists($fullPath)) {
            return $path . '?v=' . filemtime($fullPath);
        }

        return $path;
    }

    /**
     * Загружает manifest.json и ищет в нем соответствие
     */
    private function getManifestPath(string $path): ?string
    {
        if (self::$manifest === null) {
            $manifestFile = Path::public('mix-manifest.json');

            if (file_exists($manifestFile)) {
                self::$manifest = json_decode(file_get_contents($manifestFile), true) ?: [];
            } else {
                self::$manifest = [];
            }
        }

        return self::$manifest[$path] ?? null;
    }
}
