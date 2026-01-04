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
        
        $data = [
            'repertoire_a_ouvrir' => $this->request->getGet('repertoire') ?? '',
            'titre' => 'REZO+ PC INLINE | Mes documents',
            'heading' => 'Bienvenue dans REZO+ PC InLine',
            'footing' => 'copyright@2019 <a href ="https://www.web-dream.fr" target="_blank">Web-Dream</a>',
            'utilisateur' => $this->session->get('deliverdata')
        ];
        
        return view('mes_documents', $data);
    }
}

