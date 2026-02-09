<?php

namespace App\Controllers;

use App\Libraries\Googlemaps;
use CodeIgniter\Controller;

class Membres extends BaseController
{
    protected $session;
    protected $googlemaps;
    
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->googlemaps = new Googlemaps();
    }
    
    public function index()
    {
        if (!$this->session->get('login') || !$this->session->get('logged')) {
            return redirect()->to('/signup/login');
        }
        
        // Initialize our map
        $config = [
            'center' => 'France',
            'zoom' => '5'
        ];
        
        $this->googlemaps->initialize($config);
        
        // Create the map
        $data['map'] = $this->googlemaps->create_map();
        
        $data['titre'] = 'REZO+ PC INLINE';
        $data['heading'] = 'Bienvenue dans REZO+ PC InLine';
        $data['footing'] = footer_html();
        $data['base_url'] = base_url();
        $data['utilisateur'] = $this->session->get('deliverdata');
        
        return view('template/template_main', $data);
    }
    
    public function mon_compte()
    {
        if (!$this->session->get('login') || !$this->session->get('logged')) {
            return redirect()->to('/signup/login');
        }
        
        $data['titre'] = 'REZOPCINLINE';
        $data['heading'] = 'Mon compte';
        $data['content'] = "mon_compte";
        $data['footing'] = footer_html();
        
        return view('template/template', $data);
    }
}

