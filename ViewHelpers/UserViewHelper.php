<?php

namespace ViewHelpers;

use Core\Contract;
use Contracts\SessionContract;
use Repositories\UserRepository;
use Models\UserModel;

/**
 * Хелпер для проверки прав доступа в шаблонах
 */
class UserViewHelper
{
    /**
     * Проверяет роль у конкретного пользователя или у текущего из сессии
     *
     * @param mixed $userOrRole Объект UserModel или строка-роль (если проверяем текущего)
     * @param string|null $role Строка-роль (если первым аргументом передан объект)
     * @return bool
     */
    public function __invoke(mixed $userOrRole, ?string $role = null): bool
    {
        // Сценарий 1: Передан объект и роль -> vh_has_role($user, 'admin')
        if ($userOrRole instanceof UserModel && $role !== null) {
            return $userOrRole->hasRole($role);
        }

        // Сценарий 2: Передана только роль -> vh_has_role('admin')
        // Проверяем текущего пользователя из сессии
        $session = Contract::make(SessionContract::class);
        $currentUserId = $session->get('user_id');

        if (!$currentUserId) {
            return false;
        }

        $userRepo = Contract::make(UserRepository::class);

        // Используем метод репозитория для быстрой проверки в базе без загрузки всей модели
        return $userRepo->hasRole((int)$currentUserId, (string)$userOrRole);
    }
}
