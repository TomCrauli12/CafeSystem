<?php

require_once __DIR__ . '/userMeddleware.php';
require_once __DIR__ . '/../core/Router.php';

class RoleMiddleware{

    private static function getRoleIndexes(): array{

        return [
            1=>'/Cafe/app/Views/users/index.php',
            2=>'/Cafe/app/Views/cooks/index.php',
            3=>'/Cafe/app/Views/waiters/index.php',
            4=>'/Cafe/app/Views/managers/index.php',
            5=>'/Cafe/app/Views/admins/index.php'
        ];
    }

    public static function getRoleIndex(): string{

        $roleIndexes = self::getRoleIndexes();

        return $roleIndexes[(int)session::get('role_id')] ?? '/Cafe/public/index.php';
    }

    public static function hasValidRole(): bool{

        return array_key_exists((int)session::get('role_id'), self::getRoleIndexes());
    }

    private static function redirectInvalidRole(): void{

        HistoryService::log('invalid_role', 'user', (int)session::get('user_id'), 'У пользователя отсутствует корректная роль', ['roleId'=>session::get('role_id')]);

        session::destroy();

        Router::redirect('/Cafe/app/Views/auth/login.php?roleError=1');
    }

    public static function redirectToRoleIndex(): void{

        AuthMiddleware::requireAuth();

        if(!self::hasValidRole()){

            self::redirectInvalidRole();
        }

        Router::redirect(self::getRoleIndex());
    }

    public static function hasRole(int $roleId): bool{

        return self::hasAnyRole([$roleId]);
    }

    public static function hasAnyRole(array $roleIds): bool{

        if(!AuthMiddleware::isAuthenticated()){

            return false;
        }

        return in_array((int)session::get('role_id'), $roleIds, true);
    }

    public static function requireRole(int $roleId, string $redirectTo = '/Cafe/public/index.php'): void{

        self::requireAnyRole([$roleId], $redirectTo);
    }

    public static function requireAnyRole(array $roleIds, string $redirectTo = '/Cafe/public/index.php'): void{

        AuthMiddleware::requireAuth();

        if(!self::hasValidRole()){

            self::redirectInvalidRole();
        }

        if(!self::hasAnyRole($roleIds)){

            HistoryService::log('role_access_denied', 'page', null, 'Недостаточно прав для страницы', ['requiredRoles'=>$roleIds, 'uri'=>$_SERVER['REQUEST_URI'] ?? '']);

            if($redirectTo === '/Cafe/public/index.php'){

                $redirectTo = self::getRoleIndex();
            }

            Router::redirect($redirectTo);
        }
    }
}

?>
