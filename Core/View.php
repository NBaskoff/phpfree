<?php

namespace Core;

use Exception;
use ReflectionClass;
use ReflectionException;

class View
{
    private static bool $helpersLoaded = false;
    private static ?string $layout = null;
    private static array $sections = [];
    private static array $sectionStack = [];

    // Настройки путей по умолчанию (относительно корня проекта)
    private static string $templatesDir = 'assets/templates';
    private static string $configPath = 'config/view_helpers.php';

    /**
     * Указывает папку с шаблонами (например, 'assets/templates')
     */
    public static function setTemplatesDir(string $path): void
    {
        self::$templatesDir = trim($path, '/');
    }

    /**
     * Указывает путь к файлу конфигурации хелперов
     */
    public static function setConfigPath(string $path): void
    {
        self::$configPath = trim($path, '/');
    }

    /**
     * Загружает хелперы из конфига, создает vh_* функции и файл подсказок для IDE
     */
    private static function loadHelpers(): void
    {
        if (self::$helpersLoaded) {
            return;
        }

        $fullConfigPath = Path::root(self::$configPath);
        $metaFile = Path::root('ViewHelpers/.ide_helper.php');
        $metaContent = "<?php\n/** @noinspection ALL */\n\n";

        if (!file_exists($fullConfigPath)) {
            throw new Exception("Файл конфигурации хелперов не найден: $fullConfigPath");
        }

        $helpers = include $fullConfigPath;

        if (!is_array($helpers)) {
            throw new Exception("Конфигурация хелперов должна возвращать массив.");
        }

        foreach ($helpers as $funcName => $fullClass) {
            // Нормализация имени класса для корректной работы eval и Reflection
            $normalizedClass = '\\' . ltrim($fullClass, '\\');

            if (!class_exists($normalizedClass)) {
                throw new Exception("Ошибка хелпера '{$funcName}': Класс '{$normalizedClass}' не найден.");
            }

            if (!method_exists($normalizedClass, '__invoke')) {
                throw new Exception("Ошибка хелпера '{$funcName}': В классе '{$normalizedClass}' отсутствует обязательный метод __invoke().");
            }

            $functionName = 'vh_' . $funcName;

            // Динамическая регистрация глобальной функции
            if (!function_exists($functionName)) {
                eval("function $functionName(...\$args) { 
                    static \$inst; 
                    if (!\$inst) \$inst = new $normalizedClass();
                    return \$inst(...\$args); 
                }");
            }

            // Сбор метаданных для автоподсказок в PhpStorm
            try {
                $ref = new ReflectionClass($normalizedClass);
                $method = $ref->getMethod('__invoke');
                $params = [];
                foreach ($method->getParameters() as $p) {
                    $type = $p->hasType() ? $p->getType() . ' ' : '';
                    $paramStr = $type . '$' . $p->getName();
                    if ($p->isDefaultValueAvailable()) {
                        $default = var_export($p->getDefaultValue(), true);
                        $paramStr .= " = " . str_replace("\n", "", $default);
                    }
                    $params[] = $paramStr;
                }
                $metaContent .= "function $functionName(" . implode(', ', $params) . ") { }\n";
            } catch (ReflectionException $e) {}
        }

        // Пытаемся создать папку ViewHelpers, если её нет, для файла подсказок
        $metaDir = dirname($metaFile);
        if (!is_dir($metaDir)) {
            mkdir($metaDir, 0755, true);
        }

        file_put_contents($metaFile, $metaContent);
        self::$helpersLoaded = true;
    }

    // --- Управление секциями (вызываются через хелперы) ---

    public static function sectionStart(string $name): void
    {
        ob_start();
        self::$sectionStack[] = $name;
    }

    public static function sectionEnd(): void
    {
        if (empty(self::$sectionStack)) {
            return;
        }
        $name = array_pop(self::$sectionStack);
        self::$sections[$name] = (self::$sections[$name] ?? '') . ob_get_clean();
    }

    public static function sectionGet(string $name): string
    {
        return self::$sections[$name] ?? '';
    }

    // --- Управление макетами (вызывается через хелпер) ---

    public static function layout(string $name): void
    {
        self::$layout = $name;
    }

    /**
     * Основной метод рендеринга шаблона
     */
    public static function render(string $template, array $data = []): string
    {
        self::loadHelpers();

        // Сброс состояния перед каждым новым вызовом render
        self::$layout = null;
        self::$sections = [];

        // Функция-обертка для изоляции переменных шаблона
        $renderFunc = function($path, $vars) {
            extract($vars);
            ob_start();
            try {
                include $path;
            } catch (Exception $e) {
                ob_end_clean();
                throw $e;
            }
            return ob_get_clean();
        };

        // Путь к основному шаблону через Core\Path
        $templatePath = Path::root(self::$templatesDir . DIRECTORY_SEPARATOR . $template . ".php");

        if (!file_exists($templatePath)) {
            throw new Exception("Шаблон не найден по пути: $templatePath");
        }

        $content = $renderFunc($templatePath, $data);

        // Если в шаблоне был задан макет через vh_layout()
        if (self::$layout) {
            $layoutPath = Path::root(self::$templatesDir . DIRECTORY_SEPARATOR . self::$layout . ".php");

            if (!file_exists($layoutPath)) {
                throw new Exception("Макет (layout) не найден по пути: $layoutPath");
            }

            // Оборачиваем контент в макет через переменную $content
            $data['content'] = $content;
            return $renderFunc($layoutPath, $data);
        }

        return $content;
    }
}
