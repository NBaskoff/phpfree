<?php

namespace Models;

use ReflectionClass;
use ReflectionNamedType;

/**
 * Базовая модель с автоматическим маппингом через конструктор
 */
abstract class BaseModel
{
    /**
     * Создает экземпляр модели из массива данных
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $reflection = new ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            $instance = new static();
            foreach ($data as $key => $value) {
                if (property_exists($instance, $key)) {
                    $instance->{$key} = $value;
                }
            }
            return $instance;
        }

        $args = [];
        foreach ($constructor->getParameters() as $param) {
            $name = $param->getName();
            $value = $data[$name] ?? ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null);

            $type = $param->getType();
            if ($type instanceof ReflectionNamedType) {
                $args[] = self::cast($value, $type);
            } else {
                $args[] = $value;
            }
        }

        return new static(...$args);
    }

    /**
     * Приведение типов на основе Reflection
     *
     * @param mixed $value
     * @param ReflectionNamedType $type
     * @return mixed
     */
    private static function cast(mixed $value, ReflectionNamedType $type): mixed
    {
        if ($value === null && $type->allowsNull()) {
            return null;
        }

        return match ($type->getName()) {
            'int'    => (int)$value,
            'float'  => (float)$value,
            'bool'   => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'string' => (string)$value,
            default  => $value,
        };
    }

    /**
     * Преобразует модель в массив
     *
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
