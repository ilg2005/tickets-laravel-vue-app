<?php

namespace App\Constants;

class Permissions
{
    // Роли в системе
    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';

    // Tickets
    const VIEW_TICKETS = 'view tickets';
    const VIEW_ANY_TICKETS = 'view any tickets';
    const CREATE_TICKETS = 'create tickets';
    const UPDATE_TICKETS = 'update tickets';
    const DELETE_TICKETS = 'delete tickets';

    // Followups
    const VIEW_FOLLOWUPS = 'view followups';
    const CREATE_FOLLOWUPS = 'create followups';
    const CREATE_COMMENT_FOLLOWUPS = 'create comment followups';
    const CREATE_SOLUTION_FOLLOWUPS = 'create solution followups';
    const UPDATE_FOLLOWUPS = 'update followups';
    const DELETE_FOLLOWUPS = 'delete followups';


    // Типы Followup
    const FOLLOWUP_TYPE_COMMENT = 'comment';
    const FOLLOWUP_TYPE_SOLUTION = 'solution';


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

            self::VIEW_TICKETS,
            self::VIEW_ANY_TICKETS,
            self::CREATE_TICKETS,
            self::UPDATE_TICKETS,
            self::DELETE_TICKETS,
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
            self::VIEW_TICKETS,
            self::CREATE_TICKETS,
            self::UPDATE_TICKETS,
            self::DELETE_TICKETS,

            self::VIEW_FOLLOWUPS,
            self::CREATE_FOLLOWUPS,
            self::CREATE_COMMENT_FOLLOWUPS,            
            self::UPDATE_FOLLOWUPS,
            self::DELETE_FOLLOWUPS,
        ];
    }
}