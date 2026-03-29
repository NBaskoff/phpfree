<?php

namespace Contracts;

interface SessionContract
{
    public function set(string $key, mixed $value): void;

    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    public function remove(string $key): void;

    public function flash(string $key, mixed $value): void; // Сообщение на один запрос
}