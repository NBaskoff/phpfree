<?php

namespace Core;

/**
 * Класс для работы с HTTP-запросом
 */
class Request
{
    /** @var string Путь запроса */
    public readonly string $uri;

    /** @var string Метод запроса */
    public readonly string $method;

    /** @var array Данные POST */
    public readonly array $post;

    /** @var array Данные GET */
    public readonly array $query;

    /**
     * @param string|null $uri
     * @param string|null $method
     */
    public function __construct(?string $uri = null, ?string $method = null)
    {
        $this->uri = parse_url($uri ?? $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $this->method = strtoupper($method ?? $_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->post = $_POST;
        $this->query = $_GET;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->post[$key]) || isset($this->query[$key]);
    }
}
