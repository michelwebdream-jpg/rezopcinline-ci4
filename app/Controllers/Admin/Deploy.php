<?php

namespace App\Controllers\Admin;

use CodeIgniter\HTTP\RedirectResponse;

/**
 * Page admin de déploiement : envoi des fichiers modifiés (branche active vs main)
 * vers le serveur de production en FTP.
 * Disponible uniquement en local (ENVIRONMENT = development).
 */
class Deploy extends BaseAdmin
{
    /** Chemin du script de déploiement (racine du projet). */
    private function getProjectRoot(): string
    {
        $root = realpath(FCPATH . '..');
        return $root ?: rtrim(FCPATH, DIRECTORY_SEPARATOR) . '..';
    }

    private function getScriptPath(): string
    {
        return $this->getProjectRoot() . DIRECTORY_SEPARATOR . 'deploy-branch-changes.sh';
    }

    /**
     * Vérifie que la page déploiement est autorisée (uniquement en local).
     */
    private function ensureLocalOnly(): ?RedirectResponse
    {
        if (ENVIRONMENT !== 'development') {
            return redirect()->to(base_url('admin'))->with('error', 'Cette page n’est disponible qu’en local.');
        }
        return null;
    }

    /**
     * Récupère les infos git (branche, fichiers modifiés) pour le dry-run.
     */
    private function getGitDryRun(): array
    {
        $root = $this->getProjectRoot();
        $refBranch = getenv('REF_BRANCH') ?: 'main';
        $result = [
            'branch'     => '',
            'ref_branch' => $refBranch,
            'base_commit' => '',
            'files'      => [],
            'error'      => '',
        ];

        if (!is_file($root . '/.git/HEAD')) {
            $result['error'] = 'Pas un dépôt git (ou exécution hors racine du projet).';
            return $result;
        }

        $cmdBranch = 'cd ' . escapeshellarg($root) . ' && git branch --show-current 2>/dev/null';
        $branch = @shell_exec($cmdBranch);
        $result['branch'] = $branch ? trim($branch) : '';

        $cmdBase = 'cd ' . escapeshellarg($root) . ' && git merge-base ' . escapeshellarg($refBranch) . ' HEAD 2>/dev/null';
        $base = @shell_exec($cmdBase);
        $result['base_commit'] = $base ? trim($base) : '';

        if ($result['base_commit'] === '') {
            $result['error'] = "Impossible de trouver le point de divergence avec « {$refBranch} ».";
            return $result;
        }

        // Fichiers modifiés (commits depuis la branche) + modifs non commitées (staged + unstaged) + nouveaux (untracked)
        $cmdCommitted = 'cd ' . escapeshellarg($root) . ' && git diff --name-only ' . escapeshellarg($result['base_commit']) . ' 2>/dev/null';
        $cmdUnstaged  = 'cd ' . escapeshellarg($root) . ' && git diff --name-only 2>/dev/null';
        $cmdStaged    = 'cd ' . escapeshellarg($root) . ' && git diff --name-only --cached 2>/dev/null';
        $cmdUntracked = 'cd ' . escapeshellarg($root) . ' && git ls-files --others --exclude-standard 2>/dev/null';
        $out1 = @shell_exec($cmdCommitted);
        $out2 = @shell_exec($cmdUnstaged);
        $out3 = @shell_exec($cmdStaged);
        $out4 = @shell_exec($cmdUntracked);
        $all = array_merge(
            $out1 ? array_filter(array_map('trim', explode("\n", $out1))) : [],
            $out2 ? array_filter(array_map('trim', explode("\n", $out2))) : [],
            $out3 ? array_filter(array_map('trim', explode("\n", $out3))) : [],
            $out4 ? array_filter(array_map('trim', explode("\n", $out4))) : []
        );
        $result['files'] = array_values(array_unique($all));

        return $result;
    }

    /**
     * Page principale : affiche la liste des fichiers (dry-run) et le bouton Déployer.
     * Aucune écriture sur le serveur : git (lecture) et FTP (connexion + PWD uniquement).
     */
    public function index()
    {
        $redirect = $this->ensureAdmin();
        if ($redirect) {
            return $redirect;
        }
        $redirect = $this->ensureLocalOnly();
        if ($redirect) {
            return $redirect;
        }

        $git = $this->getGitDryRun();
        $configOk = $this->isFtpConfigured();
        $remoteBase = $configOk ? rtrim((string) getenv('PRODUCTION_FTP_PATH'), '/') : '';

        $ftpReal = $this->getFtpRealBasePath();
        $remoteBaseReal = $ftpReal['path'];
        $ftpError = $ftpReal['error'];

        $data = [
            'titre'            => 'Déploiement — REZO+ PC Inline',
            'utilisateur'      => $this->session->get('deliverdata'),
            'content'          => 'admin/deploy/index',
            'git'              => $git,
            'config_ok'        => $configOk,
            'remote_base'      => $remoteBase,
            'remote_base_real' => $remoteBaseReal,
            'ftp_error'        => $ftpError,
            'project_root'     => $this->getProjectRoot(),
            'deploy_output'    => $this->session->getFlashdata('deploy_output'),
        ];

        return view('admin/template_admin', $data);
    }

    /**
     * Lance le déploiement (POST) et redirige avec la sortie en flash.
     * Seule cette action envoie des fichiers sur le serveur (script --deploy).
     */
    public function run()
    {
        $redirect = $this->ensureAdmin();
        if ($redirect) {
            return $redirect;
        }
        $redirect = $this->ensureLocalOnly();
        if ($redirect) {
            return $redirect;
        }

        // Aucune écriture FTP en GET : uniquement un POST explicite déclenche l'envoi
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to(base_url('admin/deploy'));
        }

        if (!$this->isFtpConfigured()) {
            return redirect()->to(base_url('admin/deploy'))->with('deploy_output', "Erreur : configurez PRODUCTION_FTP_HOST, PRODUCTION_FTP_USER et PRODUCTION_FTP_PATH dans le fichier .env.\n");
        }
        if (empty(getenv('PRODUCTION_FTP_PASSWORD'))) {
            return redirect()->to(base_url('admin/deploy'))->with('deploy_output', "Erreur : définissez PRODUCTION_FTP_PASSWORD dans le fichier .env pour déployer depuis cette page (pas de saisie interactive).\n");
        }

        $script = $this->getScriptPath();
        if (!is_file($script) || !is_readable($script)) {
            return redirect()->to(base_url('admin/deploy'))->with('deploy_output', "Erreur : script introuvable : {$script}\n");
        }

        $root = $this->getProjectRoot();
        $env = [
            'PRODUCTION_FTP_HOST'     => getenv('PRODUCTION_FTP_HOST') ?: '',
            'PRODUCTION_FTP_USER'     => getenv('PRODUCTION_FTP_USER') ?: '',
            'PRODUCTION_FTP_PATH'     => getenv('PRODUCTION_FTP_PATH') ?: '',
            'PRODUCTION_FTP_PASSWORD' => getenv('PRODUCTION_FTP_PASSWORD') ?: '',
            'REF_BRANCH'              => getenv('REF_BRANCH') ?: 'main',
        ];

        foreach ($env as $k => $v) {
            putenv($k . '=' . $v);
        }

        $cmd = 'cd ' . escapeshellarg($root) . ' && /bin/bash ' . escapeshellarg($script) . ' --deploy 2>&1';
        $output = [];
        exec($cmd, $output);
        $outputStr = implode("\n", $output);

        return redirect()->to(base_url('admin/deploy'))->with('deploy_output', $outputStr);
    }

    /**
     * Déploie un seul fichier depuis le tableau dry-run.
     * Utilise le script bash en lui passant FILES=<fichier>.
     */
    public function runFile()
    {
        $redirect = $this->ensureAdmin();
        if ($redirect) {
            return $redirect;
        }
        $redirect = $this->ensureLocalOnly();
        if ($redirect) {
            return $redirect;
        }

        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to(base_url('admin/deploy'));
        }

        if (!$this->isFtpConfigured()) {
            return redirect()->to(base_url('admin/deploy'))->with('deploy_output', "Erreur : configurez PRODUCTION_FTP_HOST, PRODUCTION_FTP_USER et PRODUCTION_FTP_PATH dans le fichier .env.\n");
        }
        if (empty(getenv('PRODUCTION_FTP_PASSWORD'))) {
            return redirect()->to(base_url('admin/deploy'))->with('deploy_output', "Erreur : définissez PRODUCTION_FTP_PASSWORD dans le fichier .env pour déployer depuis cette page (pas de saisie interactive).\n");
        }

        $file = (string) $this->request->getPost('file');
        $file = ltrim($file, "/\\");
        if ($file === '' || strpos($file, '..') !== false) {
            return redirect()->to(base_url('admin/deploy'))->with('deploy_output', "Erreur : chemin de fichier invalide.\n");
        }

        $script = $this->getScriptPath();
        if (!is_file($script) || !is_readable($script)) {
            return redirect()->to(base_url('admin/deploy'))->with('deploy_output', "Erreur : script introuvable : {$script}\n");
        }

        $root = $this->getProjectRoot();
        if (!is_file($root . DIRECTORY_SEPARATOR . $file)) {
            return redirect()->to(base_url('admin/deploy'))->with('deploy_output', "Erreur : fichier introuvable localement : {$file}\n");
        }

        $env = [
            'PRODUCTION_FTP_HOST'     => getenv('PRODUCTION_FTP_HOST') ?: '',
            'PRODUCTION_FTP_USER'     => getenv('PRODUCTION_FTP_USER') ?: '',
            'PRODUCTION_FTP_PATH'     => getenv('PRODUCTION_FTP_PATH') ?: '',
            'PRODUCTION_FTP_PASSWORD' => getenv('PRODUCTION_FTP_PASSWORD') ?: '',
            'REF_BRANCH'              => getenv('REF_BRANCH') ?: 'main',
            'FILES'                   => $file,
        ];

        foreach ($env as $k => $v) {
            putenv($k . '=' . $v);
        }

        $cmd = 'cd ' . escapeshellarg($root) . ' && /bin/bash ' . escapeshellarg($script) . ' --deploy 2>&1';
        $output = [];
        exec($cmd, $output);
        $outputStr = "Déploiement du fichier : {$file}\n" . implode("\n", $output);

        return redirect()->to(base_url('admin/deploy'))->with('deploy_output', $outputStr);
    }

    private function isFtpConfigured(): bool
    {
        $host = getenv('PRODUCTION_FTP_HOST');
        $user = getenv('PRODUCTION_FTP_USER');
        $path = getenv('PRODUCTION_FTP_PATH');
        return !empty($host) && !empty($user) && !empty($path);
    }

    /**
     * Se connecte au FTP et retourne le chemin distant réel (PWD) du dossier de destination.
     * Inspection seule (connexion, chdir, pwd) : aucune écriture ni envoi de fichier.
     * Retourne ['path' => string, 'error' => string|null].
     */
    private function getFtpRealBasePath(): array
    {
        $host = getenv('PRODUCTION_FTP_HOST');
        $user = getenv('PRODUCTION_FTP_USER');
        $pass = getenv('PRODUCTION_FTP_PASSWORD');
        $path = rtrim((string) getenv('PRODUCTION_FTP_PATH'), '/');
        if (empty($host) || empty($user) || empty($path)) {
            return ['path' => '', 'error' => 'Configuration FTP incomplète.'];
        }
        if (empty($pass)) {
            return ['path' => '', 'error' => 'PRODUCTION_FTP_PASSWORD requis pour inspecter le serveur.']; // fallback: on garde le chemin config
        }

        $useSsl = filter_var(getenv('PRODUCTION_FTP_SSL'), FILTER_VALIDATE_BOOLEAN);
        $port = (int) (getenv('PRODUCTION_FTP_PORT') ?: 21);
        if ($port <= 0) {
            $port = 21;
        }

        $conn = null;
        if ($useSsl && function_exists('ftp_ssl_connect')) {
            $conn = @ftp_ssl_connect($host, $port, 10);
        }
        if ($conn === false || $conn === null) {
            $conn = @ftp_connect($host, $port, 10);
        }
        if ($conn === false) {
            return ['path' => '', 'error' => 'Connexion FTP impossible (vérifiez PRODUCTION_FTP_HOST et le port).'];
        }

        if (!@ftp_login($conn, $user, $pass)) {
            ftp_close($conn);
            return ['path' => '', 'error' => 'Authentification FTP échouée.'];
        }

        ftp_pasv($conn, true);

        if (!@ftp_chdir($conn, $path)) {
            ftp_close($conn);
            return ['path' => '', 'error' => 'Dossier distant introuvable : ' . $path];
        }

        $realPath = @ftp_pwd($conn);
        ftp_close($conn);
        if ($realPath === false || $realPath === '') {
            return ['path' => $path, 'error' => 'PWD non disponible, chemin configuré affiché.'];
        }

        return ['path' => $realPath, 'error' => null];
    }
}
