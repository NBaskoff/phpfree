<?php

namespace Actions\User;

use Repositories\UserRepository;

/**
 * Экшен получения списка пользователей
 */
class ListUsersAction
{
    public function __construct(
        private UserRepository $userRepo
    ) {}

    /**
     * Позволяет вызывать класс как функцию: $action()
     *
     * @return array Данные для передачи в View
     */
    public function __invoke(): array
    {
        return [
            'users' => $this->userRepo->all(),
            'title' => 'Список пользователей'
        ];
    }
}
