<?php

namespace ViewHelpers;

use Core\Contract;
use Contracts\SessionContract;
use Repositories\UserRepository;
use Models\UserModel;

/**
 * Хелпер для проверки прав доступа (ролей) в шаблонах
 */
class HasRoleViewHelper
{
    /**
     * Проверяет роль у конкретного объекта или у текущего пользователя из сессии
     *
     * @param mixed $userOrRole Объект UserModel или строка-роль
     * @param string|null $role Строка-роль (если первым передан объект)
     * @return bool
     */
    public function __invoke(mixed $userOrRole, ?string $role = null): bool
    {
        // Если передали модель: vh_has_role($user, 'admin')
        if ($userOrRole instanceof UserModel && $role !== null) {
            return $userOrRole->hasRole($role);
        }

        // Если передали только строку: vh_has_role('admin')
        $session = Contract::make(SessionContract::class);
        $currentUserId = $session->get('user_id');

        if (!$currentUserId) {
            return false;
        }

        $userRepo = new UserRepository();

        return $userRepo->hasRole((int)$currentUserId, (string)$userOrRole);
    }
}
