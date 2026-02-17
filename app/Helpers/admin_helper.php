<?php

/**
 * Vérifie si l'utilisateur a les droits administrateur back-office.
 *
 * @param array|null $user Utilisateur (session ou delivery data) avec au minimum 'code_administrateur'
 * @return bool
 */
function is_admin(?array $user): bool
{
    if (empty($user) || ! isset($user['code_administrateur'])) {
        return false;
    }

    $code = (string) $user['code_administrateur'];
    $config = config('Admin');

    return in_array($code, $config->adminCodes ?? [], true);
}
