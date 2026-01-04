<?php

namespace App\Controllers;

use App\Models\SignupModel;
use CodeIgniter\Controller;

class Envoi_password extends BaseController
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
        $rules = [
            'text_input_mon_email' => 'trim|required|valid_email'
        ];
        
        if ($this->validate($rules)) {
            $data = [
                'mon_email' => $this->request->getPost('text_input_mon_email')
            ];
            
            $resultat_envoi_password = $this->signupModel->envoi_mot_de_passe($data);
            
            if ($resultat_envoi_password !== false) {
                if ($resultat_envoi_password == "1") {
                    $data = [
                        'succes' => 'Votre code et mot de passe ont bien étés envoyés à votre adresse email.',
                        'titre' => 'REZO+ PC INLINE | Mon compte',
                        'heading' => 'Bienvenue dans REZO+ PC InLine',
                        'footing' => 'copyright@2019 <a href ="https://www.web-dream.fr" target="_blank">Web-Dream</a>'
                    ];
                    return view('envoi_password', $data);
                } else {
                    $this->retourne_une_erreur_au_formulaire('Erreur.<br />Cette adresse email n\'existe pas.');
                }
            } else {
                $this->retourne_une_erreur_au_formulaire('Erreur réseau.<br />Veuillez recommencer ultérieurement.');
            }
        } else {
            $data = [
                'titre' => 'REZO+ PC INLINE | Envoyer mon code et mon mot de passe',
                'heading' => 'Bienvenue dans REZO+ PC InLine',
                'footing' => 'copyright@2019 <a href ="https://www.web-dream.fr" target="_blank">Web-Dream</a>',
                'validation' => $this->validator
            ];
            return view('envoi_password', $data);
        }
    }
    
    private function retourne_une_erreur_au_formulaire($message)
    {
        $data = [
            'error' => $message,
            'titre' => 'REZO+ PC INLINE | Envoyer mon code et mon mot de passe',
            'heading' => 'Bienvenue dans REZO+ PC InLine',
            'footing' => 'copyright@2019 <a href ="https://www.web-dream.fr" target="_blank">Web-Dream</a>',
            'validation' => $this->validator
        ];
        return view('envoi_password', $data);
    }
}

