<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Configuration des administrateurs back-office.
 * Les codes sont définis via la variable d'environnement ADMIN_CODES (.env).
 */
class Admin extends BaseConfig
{
    /**
     * Codes des utilisateurs ayant les droits administrateur.
     * Format .env : ADMIN_CODES=ba7fd5f5,autre_code (séparés par des virgules)
     *
     * @var list<string>
     */
    public array $adminCodes = [];

    public function __construct()
    {
        parent::__construct();

        $codes = env('ADMIN_CODES', 'ba7fd5f5');
        $this->adminCodes = array_values(array_filter(array_map('trim', explode(',', (string) $codes))));
    }
}
