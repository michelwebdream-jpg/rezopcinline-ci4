<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Mes_documents extends BaseController
{
    protected $session;
    
    public function __construct()
    {
        $this->session = \Config\Services::session();
    }
    
    public function index()
    {
        if (!$this->session->get('login') || !$this->session->get('logged')) {
            return redirect()->to('/signup/login');
        }
        
        // Détecter si on est en local
        $hostname = $this->request->getServer('HTTP_HOST') ?? '';
        $local_indicators = ['localhost', '127.0.0.1', '::1', 'local', '.local', '.dev'];
        $is_local = false;
        foreach ($local_indicators as $indicator) {
            if (stripos($hostname, $indicator) !== false) {
                $is_local = true;
                break;
            }
        }
        if (!$is_local && filter_var($hostname, FILTER_VALIDATE_IP) !== false) {
            $is_local = true;
        }
        
        // Détecter automatiquement APP_SERVER_URL selon l'environnement
        // - Local: chaîne vide (utilise les proxies locaux)
        // - Test (rezoci4.web-dream.fr): https://rezoci4.web-dream.fr
        // - Production (web-dream.fr/rezopcinline): https://www.web-dream.fr
        if ($is_local) {
            $app_server_url = ''; // Utilise les proxies locaux pour éviter CORS
        } else {
            // IMPORTANT: Ignorer la valeur du .env sur les serveurs de test et production
            // Détecter d'abord le serveur de test
            if (strpos($hostname, 'rezoci4.web-dream.fr') !== false) {
                $app_server_url = 'https://rezoci4.web-dream.fr';
            } else {
                // Production : inclure /rezopcinline pour que /dev/... soit servi depuis rezopcinline/public/dev/
                $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
                $requestUri = $_SERVER['REQUEST_URI'] ?? '';
                $app_server_url = (strpos($scriptName, '/rezopcinline/') !== false || strpos($requestUri, '/rezopcinline') === 0)
                    ? 'https://www.web-dream.fr/rezopcinline'
                    : 'https://www.web-dream.fr';
            }
        }
        
        $data = [
            'repertoire_a_ouvrir' => $this->request->getGet('repertoire') ?? '',
            'titre' => 'REZO+ PC INLINE | Mes documents',
            'heading' => 'Bienvenue dans REZO+ PC InLine',
            'footing' => footer_html(),
            'utilisateur' => $this->session->get('deliverdata'),
            'APP_SERVER_URL' => $app_server_url,
            // URLs vers /dev/... (le .htaccess réécrit vers public/dev/)
            'GET_DIRECTORY_TREE_JSON_URI' => '/dev/rezo_galerie/get_directory_tree_json.php',
            'EFFACE_REPERTOIRE_URI' => '/dev/rezo_code/efface_repertoire.php',
            'SUPPRIME_PHOTO_GALERIE_URI' => '/dev/rezo_code/supprime_photo_galerie.php'
        ];
        
        return view('mes_documents', $data);
    }
}

