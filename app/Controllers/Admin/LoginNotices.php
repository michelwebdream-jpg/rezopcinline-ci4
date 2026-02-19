<?php

namespace App\Controllers\Admin;

use App\Models\LoginNoticeModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Administration des annonces (bloc d'info) de la page login.
 */
class LoginNotices extends BaseAdmin
{
    protected LoginNoticeModel $loginNoticeModel;

    public function __construct()
    {
        parent::__construct();
        $this->loginNoticeModel = model(LoginNoticeModel::class);
    }

    public function index(): string|RedirectResponse
    {
        $redirect = $this->ensureAdmin();
        if ($redirect) {
            return $redirect;
        }

        $notices = $this->loginNoticeModel->orderBy('sort_order', 'ASC')->findAll();
        $duration = $this->loginNoticeModel->getDisplayDurationSeconds();

        $data = [
            'titre'           => 'Annonces page de connexion',
            'content'         => 'admin/login_notices/index',
            'notices'         => $notices,
            'display_duration_seconds' => $duration,
            'utilisateur'     => $this->session->get('deliverdata'),
        ];

        return view('admin/template_admin', $data);
    }

    public function add(): string|RedirectResponse
    {
        $redirect = $this->ensureAdmin();
        if ($redirect) {
            return $redirect;
        }

        $data = [
            'titre'       => 'Ajouter une annonce',
            'content'     => 'admin/login_notices/form',
            'notice'      => null,
            'utilisateur' => $this->session->get('deliverdata'),
        ];

        return view('admin/template_admin', $data);
    }

    public function edit(int $id): string|RedirectResponse
    {
        $redirect = $this->ensureAdmin();
        if ($redirect) {
            return $redirect;
        }

        $notice = $this->loginNoticeModel->find($id);
        if (!$notice) {
            return redirect()->to(base_url('admin/login-notices'))->with('error', 'Annonce introuvable.');
        }

        $data = [
            'titre'       => 'Modifier l\'annonce',
            'content'     => 'admin/login_notices/form',
            'notice'      => $notice,
            'utilisateur' => $this->session->get('deliverdata'),
        ];

        return view('admin/template_admin', $data);
    }

    public function save(): RedirectResponse
    {
        $redirect = $this->ensureAdmin();
        if ($redirect) {
            return $redirect;
        }

        $id = (int) $this->request->getPost('id');
        $title = trim((string) $this->request->getPost('title'));
        $content = (string) $this->request->getPost('content');
        $sortOrder = (int) $this->request->getPost('sort_order');

        if ($title === '') {
            return redirect()->back()->withInput()->with('error', 'Le titre est obligatoire.');
        }

        $data = [
            'title'      => $title,
            'content'    => $content,
            'sort_order' => max(0, $sortOrder),
        ];

        if ($id > 0) {
            $notice = $this->loginNoticeModel->find($id);
            if (!$notice) {
                return redirect()->to(base_url('admin/login-notices'))->with('error', 'Annonce introuvable.');
            }
            $this->loginNoticeModel->update($id, $data);
            return redirect()->to(base_url('admin/login-notices'))->with('success', 'Annonce enregistrée.');
        }

        $this->loginNoticeModel->insert($data);
        return redirect()->to(base_url('admin/login-notices'))->with('success', 'Annonce ajoutée.');
    }

    public function delete(int $id): RedirectResponse
    {
        $redirect = $this->ensureAdmin();
        if ($redirect) {
            return $redirect;
        }

        $notice = $this->loginNoticeModel->find($id);
        if (!$notice) {
            return redirect()->to(base_url('admin/login-notices'))->with('error', 'Annonce introuvable.');
        }

        $this->loginNoticeModel->delete($id);
        return redirect()->to(base_url('admin/login-notices'))->with('success', 'Annonce supprimée.');
    }

    public function saveConfig(): RedirectResponse
    {
        $redirect = $this->ensureAdmin();
        if ($redirect) {
            return $redirect;
        }

        $seconds = (int) $this->request->getPost('display_duration_seconds');
        $this->loginNoticeModel->setDisplayDurationSeconds($seconds);
        return redirect()->to(base_url('admin/login-notices'))->with('success', 'Durée d\'affichage enregistrée.');
    }
}
