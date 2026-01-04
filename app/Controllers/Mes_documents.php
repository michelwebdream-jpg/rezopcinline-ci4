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
        
        // Pour les fichiers de galerie, utiliser le serveur local qui fait proxy vers la production
        // car cela évite les problèmes CORS
        $app_server_url = $is_local ? '' : (getenv('APP_SERVER_URL') ?: 'https://www.web-dream.fr');
        
        $data = [
            'repertoire_a_ouvrir' => $this->request->getGet('repertoire') ?? '',
            'titre' => 'REZO+ PC INLINE | Mes documents',
            'heading' => 'Bienvenue dans REZO+ PC InLine',
            'footing' => 'copyright@2019 <a href ="https://www.web-dream.fr" target="_blank">Web-Dream</a>',
            'utilisateur' => $this->session->get('deliverdata'),
            'APP_SERVER_URL' => $app_server_url,
            'GET_DIRECTORY_TREE_JSON_URI' => '/dev/rezo_galerie/get_directory_tree_json.php',
            'EFFACE_REPERTOIRE_URI' => '/dev/rezo_code/efface_repertoire.php',
            'SUPPRIME_PHOTO_GALERIE_URI' => '/dev/rezo_code/supprime_photo_galerie.php'
        ];
        
        return view('mes_documents', $data);
    }
}

