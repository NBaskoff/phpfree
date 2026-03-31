<?php

namespace Commands;

use Core\Path; // Пути проекта
use ReflectionClass; // Рефлексия для параметров
use Exception; // Обработка ошибок

class IdeHelperCommand extends BaseCommand
{
    private string $lastHash = ''; // Хеш состояния файлов

    public function execute(array $args): void
    {
        $watchMode = in_array('--watch', $args); // Проверка флага наблюдения

        if ($watchMode) {
            $this->info("Режим наблюдения активирован. Слежу за изменениями в /config и /ViewHelpers"); // Сообщение
            $this->loop(); // Бесконечный цикл
        } else {
            $this->generate(); // Разовый запуск
            $this->success("Мета-данные успешно обновлены."); // Успех
        }
    }

    private function loop(): void
    {
        while (true) {
            $currentHash = $this->calculateStateHash(); // Считаем хеш папок

            if ($currentHash !== $this->lastHash) {
                $this->generate(); // Перегенерация мета-файла
                $this->lastHash = $currentHash; // Запоминаем хеш
                $this->success("Изменения обнаружены. Мета-данные обновлены [" . date('H:i:s') . "]");
            }

            sleep(2); // Пауза 2 секунды для разгрузки CPU
        }
    }

    private function calculateStateHash(): string
    {
        $configFiles = glob(Path::configs('*.php')) ?: []; // Файлы конфигов
        $helperFiles = glob(Path::root('ViewHelpers' . DIRECTORY_SEPARATOR . '*.php')) ?: []; // Файлы хелперов

        $files = array_merge($configFiles, $helperFiles); // Слияние списков
        $data = '';

        foreach ($files as $f) {
            if (file_exists($f)) $data .= filemtime($f) . $f; // Время изменения + путь
        }

        return md5($data); // Итоговый хеш состояния
    }

    private function generate(): void
    {
        $configKeys = $this->scanConfigs(); // Сбор ключей конфигов
        $helpers = $this->scanHelpers(); // Сбор хелперов (имя => класс)

        $meta = "<?php\n\nnamespace PHPSTORM_META {\n\n";

        $keysString = implode(",\n            ", array_map(fn($k) => "'$k'", $configKeys));

        $meta .= "    expectedArguments(\\config(), 0, \n            " . $keysString . "\n    );\n";
        $meta .= "    expectedArguments(\\Core\Config::get(), 0, \n            " . $keysString . "\n    );\n";
        $meta .= "}\n\nnamespace {\n";

        foreach ($helpers as $name => $class) {
            $params = $this->getInvokeParameters($class); // Анализ параметров __invoke
            $meta .= "    /** @see \\$class::__invoke() */\n";
            $metaContent = "    function vh_{$name}($params) { }\n";
            $meta .= $metaContent;
        }

        $meta .= "}\n";

        file_put_contents(Path::root('.phpstorm.meta.php'), $meta); // Запись в корень
    }

    private function getInvokeParameters(string $class): string
    {
        if (!class_exists($class)) return '...$args'; // Если класс не найден

        try {
            $ref = new ReflectionClass($class); // Рефлексия
            if (!$ref->hasMethod('__invoke')) return '...$args';

            $method = $ref->getMethod('__invoke'); // Метод вызова
            $params = [];

            foreach ($method->getParameters() as $p) {
                $type = $p->hasType() ? (string)$p->getType() . ' ' : ''; // Тип (PHP 8.4)
                $paramStr = $type . '$' . $p->getName(); // Имя аргумента

                if ($p->isDefaultValueAvailable()) {
                    $default = var_export($p->getDefaultValue(), true); // Дефолтное значение
                    $paramStr .= " = " . str_replace(["\n", "\r"], '', $default);
                }
                $params[] = $paramStr;
            }

            return implode(', ', $params); // Строка аргументов
        } catch (Exception) {
            return '...$args';
        }
    }

    private function scanConfigs(): array
    {
        $keys = [];
        $files = glob(Path::configs('*.php')) ?: []; // Список конфигов

        foreach ($files as $file) {
            $name = basename($file, '.php'); // Имя файла (ключ первого уровня)
            if (in_array($name, ['functions', 'commands'])) continue; // Пропуск системных

            $data = include $file; // Загрузка массива
            if (is_array($data)) {
                $keys[] = $name;
                $this->extractKeys($data, $name, $keys); // Рекурсивный сбор ключей
            }
        }
        return array_unique($keys); // Только уникальные значения
    }

    private function extractKeys(array $array, string $prefix, array &$keys): void
    {
        foreach ($array as $key => $value) {
            $fullKey = "$prefix.$key"; // Собираем dot.notation
            $keys[] = $fullKey;
            if (is_array($value)) $this->extractKeys($value, $fullKey, $keys); // Проваливаемся глубже
        }
    }

    private function scanHelpers(): array
    {
        $path = Path::configs('view_helpers.php'); // Файл хелперов
        return (file_exists($path) && is_array($h = include $path)) ? $h : [];
    }
}
