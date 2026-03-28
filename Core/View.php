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

    // Имя файла конфигурации хелперов (путь к папке берется из Path::config())
    private static string $helpersFileName = 'view_helpers.php';

    /**
     * Позволяет изменить имя файла конфигурации хелперов
     */
    public static function setHelpersFile(string $fileName): void
    {
        self::$helpersFileName = ltrim($fileName, '/\\');
    }

    /**
     * Загружает хелперы, создает vh_* функции и файл подсказок для IDE
     */
    private static function loadHelpers(): void
    {
        if (self::$helpersLoaded) {
            return;
        }

        // Получаем путь к конфигу через центральный класс Path
        $fullConfigPath = Path::config(self::$helpersFileName);
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
            $normalizedClass = '\\' . ltrim($fullClass, '\\');

            if (!class_exists($normalizedClass)) {
                throw new Exception("Ошибка хелпера '{$funcName}': Класс '{$normalizedClass}' не найден.");
            }

            if (!method_exists($normalizedClass, '__invoke')) {
                throw new Exception("Ошибка хелпера '{$funcName}': В классе '{$normalizedClass}' отсутствует метод __invoke().");
            }

            $functionName = 'vh_' . $funcName;

            if (!function_exists($functionName)) {
                eval("function $functionName(...\$args) { 
                    static \$inst; 
                    if (!\$inst) \$inst = new $normalizedClass();
                    return \$inst(...\$args); 
                }");
            }

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

        file_put_contents($metaFile, $metaContent);
        self::$helpersLoaded = true;
    }

    // --- Управление секциями ---

    public static function sectionStart(string $name): void
    {
        ob_start();
        self::$sectionStack[] = $name;
    }

    public static function sectionEnd(): void
    {
        if (empty(self::$sectionStack)) return;
        $name = array_pop(self::$sectionStack);
        self::$sections[$name] = (self::$sections[$name] ?? '') . ob_get_clean();
    }

    public static function sectionGet(string $name): string
    {
        return self::$sections[$name] ?? '';
    }

    // --- Управление макетами ---

    public static function layout(string $name): void
    {
        self::$layout = $name;
    }

    /**
     * Основной метод рендеринга
     */
    public static function render(string $template, array $data = []): string
    {
        self::loadHelpers();

        self::$layout = null;
        self::$sections = [];

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

        // Путь к шаблону запрашиваем у Path::templates()
        $templatePath = Path::templates($template . ".php");

        if (!file_exists($templatePath)) {
            throw new Exception("Шаблон не найден по пути: $templatePath");
        }

        $content = $renderFunc($templatePath, $data);

        if (self::$layout) {
            // Путь к макету также через Path::templates()
            $layoutPath = Path::templates(self::$layout . ".php");

            if (!file_exists($layoutPath)) {
                throw new Exception("Макет (layout) не найден по пути: $layoutPath");
            }

            $data['content'] = $content;
            return $renderFunc($layoutPath, $data);
        }

        return $content;
    }
}
