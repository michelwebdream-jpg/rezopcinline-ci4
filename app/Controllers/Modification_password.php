<?php

namespace App\Controllers;

use App\Models\SignupModel;
use CodeIgniter\Controller;

class Modification_password extends BaseController
{
    protected $session;
    protected $validation;
    protected $signupModel;
    
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->signupModel = model('SignupModel');
    }
    
    public function index()
    {
        if (!$this->session->get('login') || !$this->session->get('logged')) {
            return redirect()->to('/signup/login');
        }
        
        $rules = [
            'text_input_mon_password_actuel' => 'trim|required|min_length[5]',
            'text_input_mon_password_new' => 'trim|required|min_length[5]'
        ];
        
        if ($this->validate($rules)) {
            $utilisateur = $this->session->get('deliverdata');
            $mon_code = $utilisateur['code_administrateur'] ?? '';
            
            $data = [
                'mon_code' => $mon_code,
                'mon_password_actuel' => $this->request->getPost('text_input_mon_password_actuel'),
                'mon_password_new' => $this->request->getPost('text_input_mon_password_new')
            ];
            
            $resultat_modifier_password = $this->signupModel->modifier_mot_de_passe($data);
            
            if ($resultat_modifier_password !== false) {
                if ($resultat_modifier_password == "1") {
                    $data = [
                        'succes' => 'Votre mot de passe a bien été mis à jour. Vous devrez utiliser votre nouveau mot de passe lors de votre prochaine connexion.',
                        'titre' => 'REZO+ PC INLINE | Mon compte',
                        'heading' => 'Bienvenue dans REZO+ PC InLine',
                        'footing' => 'copyright@2019 <a href ="https://www.web-dream.fr" target="_blank">Web-Dream</a>',
                        'utilisateur' => $this->session->get('deliverdata')
                    ];
                    return view('mon_compte', $data);
                } else {
                    $this->retourne_une_erreur_au_formulaire('Erreur.<br />Le mot de passe actuel saisie ne correspond pas à votre mot de passe.');
                }
            } else {
                $this->retourne_une_erreur_au_formulaire('Erreur réseau.<br />Veuillez recommencer ultérieurement.');
            }
        } else {
            $data = [
                'titre' => 'REZO+ PC INLINE | Modifier mon mot de passe',
                'heading' => 'Bienvenue dans REZO+ PC InLine',
                'footing' => 'copyright@2019 <a href ="https://www.web-dream.fr" target="_blank">Web-Dream</a>',
                'utilisateur' => $this->session->get('deliverdata'),
                'validation' => $this->validator
            ];
            return view('modification_password', $data);
        }
    }
    
    private function retourne_une_erreur_au_formulaire($message)
    {
        $data = [
            'error' => $message,
            'titre' => 'REZO+ PC INLINE | Modifier mon mot de passe',
            'heading' => 'Bienvenue dans REZO+ PC InLine',
            'footing' => 'copyright@2019 <a href ="https://www.web-dream.fr" target="_blank">Web-Dream</a>',
            'utilisateur' => $this->session->get('deliverdata'),
            'validation' => $this->validator
        ];
        return view('modification_password', $data);
    }
}

