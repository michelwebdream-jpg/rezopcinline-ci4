<?php

namespace App\Controllers\Admin;

/**
 * Tableau de bord du back-office admin.
 */
class Dashboard extends BaseAdmin
{
    public function index()
    {
        $redirect = $this->ensureAdmin();
        if ($redirect) {
            return $redirect;
        }

        $data = [
            'titre'       => 'Administration — REZO+ PC Inline',
            'utilisateur' => $this->session->get('deliverdata'),
        ];

        return view('admin/template_admin', array_merge($data, ['content' => 'admin/dashboard']));
    }
}
