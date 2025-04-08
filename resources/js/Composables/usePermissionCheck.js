import { usePage } from "@inertiajs/vue3";
import { PERMISSIONS, ROLES } from "../Constants/permissions";

/**
 * Composable для работы с разрешениями и ролями пользователя
 * 
 * @returns {Object} Объект с методами и константами для проверки разрешений и ролей
 */
export function usePermissionCheck() {
  /**
   * Проверяет наличие роли у пользователя
   * 
   * @param {string} name - Название роли
   * @returns {boolean}
   */
  const hasRole = (name) => usePage().props.auth.user.roles.includes(name);
  
  /**
   * Проверяет наличие разрешения у пользователя
   * 
   * @param {string} name - Название разрешения
   * @returns {boolean}
   */
  const hasPermission = (name) =>
    usePage().props.auth.user.permissions.includes(name);
  
  /**
   * Проверяет наличие хотя бы одной из указанных ролей у пользователя
   * 
   * @param {Array<string>} names - Массив названий ролей
   * @returns {boolean}
   */
  const hasRoles = (names) => usePage().props.auth.user.roles.some(name => names.includes(name));

  return {
    hasRole,
    hasPermission,
    hasRoles,
    PERMISSIONS,
    ROLES
  };
} 