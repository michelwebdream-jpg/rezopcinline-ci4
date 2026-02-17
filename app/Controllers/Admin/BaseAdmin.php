<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Contrôleur de base pour le back-office admin.
 * Vérifie que l'utilisateur est connecté et a les droits administrateur.
 */
abstract class BaseAdmin extends BaseController
{
    protected $session;

    public function __construct()
    {
        $this->session = \Config\Services::session();
    }

    /**
     * Vérifie l'accès admin. Redirige vers login ou 403 si non autorisé.
     */
    protected function ensureAdmin(): ?RedirectResponse
    {
        if (!$this->session->get('login') || !$this->session->get('logged')) {
            return redirect()->to('/signup/login');
        }

        $user = $this->session->get('deliverdata');
        if (!is_admin($user)) {
            return redirect()->to('/signup/membres')->with('error', 'Accès refusé.');
        }

        return null;
    }
}
