<?php

namespace App\Controllers;

class Home extends BaseController
{
    protected $session;
    
    public function __construct()
    {
        $this->session = \Config\Services::session();
    }
    
    public function index()
    {
        // Si l'utilisateur est connecté, rediriger vers la page membres
        if ($this->session->get('login') || $this->session->get('logged')) {
            return redirect()->to('/signup/membres');
        }
        
        // Sinon, rediriger vers la page de login
        return redirect()->to('/signup/login');
    }
}
