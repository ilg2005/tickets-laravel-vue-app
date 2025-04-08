<?php

namespace App\Constants;

class Permissions 
{
    // Followups
    const VIEW_FOLLOWUPS = 'view followups';
    const CREATE_FOLLOWUPS = 'create followups';
    const CREATE_COMMENT_FOLLOWUPS = 'create comment followups';
    const CREATE_SOLUTION_FOLLOWUPS = 'create solution followups';
    const UPDATE_FOLLOWUPS = 'update followups';
    const DELETE_FOLLOWUPS = 'delete followups';
    
    // Роли в системе
    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';
    
    /**
     * Получение всех разрешений
     * 
     * @return array
     */
    public static function all(): array
    {
        return [
            self::VIEW_FOLLOWUPS,
            self::CREATE_FOLLOWUPS,
            self::CREATE_COMMENT_FOLLOWUPS, 
            self::CREATE_SOLUTION_FOLLOWUPS,
            self::UPDATE_FOLLOWUPS,
            self::DELETE_FOLLOWUPS,
        ];
    }
    
    /**
     * Получение разрешений для админа
     * 
     * @return array
     */
    public static function adminPermissions(): array
    {
        return self::all();
    }
    
    /**
     * Получение разрешений для обычного пользователя
     * 
     * @return array
     */
    public static function userPermissions(): array
    {
        return [
            self::VIEW_FOLLOWUPS,
            self::CREATE_FOLLOWUPS,
            self::CREATE_COMMENT_FOLLOWUPS,
            self::UPDATE_FOLLOWUPS,
            self::DELETE_FOLLOWUPS,
        ];
    }
} 