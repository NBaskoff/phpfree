<?php

namespace Commands;

use Core\Path;
use ReflectionClass; // Рефлексия для анализа хелперов

class IdeHelperCommand
{
    public function execute(): void
    {
        $configKeys = $this->scanConfigs(); // Ключи конфигов
        $helpers = $this->scanHelpers(); // Хелперы (имя => класс)

        $metaContent = "<?php\n\nnamespace PHPSTORM_META {\n\n";

        // 1. Подсказки для config()
        $keysString = implode(",\n            ", array_map(fn($k) => "'$k'", $configKeys));
        $metaContent .= "    expectedArguments(\\config(), 0, \n            " . $keysString . "\n    );\n";
        $metaContent .= "    expectedArguments(\\Core\Config::get(), 0, \n            " . $keysString . "\n    );\n\n";
        $metaContent .= "}\n\nnamespace {\n";

        // 2. Генерация заглушек функций vh_* с параметрами
        foreach ($helpers as $name => $class) {
            $paramsStr = $this->getInvokeParameters($class); // Читаем сигнатуру __invoke
            $metaContent .= "    /** @see \\$class::__invoke() */\n";
            $metaContent .= "    function vh_{$name}($paramsStr) { }\n";
        }

        $metaContent .= "}\n";

        file_put_contents(Path::root('.phpstorm.meta.php'), $metaContent); // Сохранение
        echo "Мета-данные обновлены. Обработано " . count($helpers) . " хелперов.\n";
    }

    private function getInvokeParameters(string $class): string
    {
        if (!class_exists($class)) return '...$args'; // Фоллбэк

        try {
            $ref = new ReflectionClass($class);
            if (!$ref->hasMethod('__invoke')) return '...$args';

            $method = $ref->getMethod('__invoke');
            $params = [];

            foreach ($method->getParameters() as $p) {
                $type = $p->hasType() ? (string)$p->getType() . ' ' : ''; // Тип (PHP 8.4)
                $paramStr = $type . '$' . $p->getName(); // Имя переменной

                if ($p->isDefaultValueAvailable()) {
                    $default = var_export($p->getDefaultValue(), true);
                    $paramStr .= " = " . str_replace(["\n", "\r"], '', $default); // Дефолт в одну строку
                }
                $params[] = $paramStr;
            }

            return implode(', ', $params); // Строка параметров
        } catch (\Exception) {
            return '...$args';
        }
    }

    private function scanConfigs(): array
    {
        $keys = [];
        $files = glob(Path::configs('*.php'));

        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (in_array($name, ['view_helpers', 'functions', 'commands'])) continue;

            $data = include $file;
            if (is_array($data)) {
                $keys[] = $name;
                $this->extractKeys($data, $name, $keys);
            }
        }
        return array_unique($keys);
    }

    private function extractKeys(array $array, string $prefix, array &$keys): void
    {
        foreach ($array as $key => $value) {
            $fullKey = "$prefix.$key";
            $keys[] = $fullKey;
            if (is_array($value)) $this->extractKeys($value, $fullKey, $keys);
        }
    }

    private function scanHelpers(): array
    {
        $path = Path::configs('view_helpers.php');
        return (file_exists($path) && is_array($h = include $path)) ? $h : [];
    }
}
