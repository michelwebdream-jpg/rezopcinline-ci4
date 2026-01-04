<?php

namespace App\Controllers;

use App\Models\SignupModel;
use CodeIgniter\Controller;

class Mon_compte extends BaseController
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
        
        // Ne valider que si c'est une requête POST
        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'text_input_mon_nom' => 'trim|required',
                'text_input_mon_prenom' => 'trim|required',
                'text_input_mon_telephone' => 'trim|required',
                'text_input_mon_indicatif' => 'trim|required'
            ];
            
            if ($this->validate($rules)) {
            $utilisateur = $this->session->get('deliverdata');
            $mon_code = $utilisateur['code_administrateur'] ?? '';
            $mon_mail = $utilisateur['mail_administrateur'] ?? '';
            
            $data = [
                'mon_code' => $mon_code,
                'mon_nom' => $this->request->getPost('text_input_mon_nom'),
                'mon_prenom' => $this->request->getPost('text_input_mon_prenom'),
                'mon_telephone' => $this->request->getPost('text_input_mon_telephone'),
                'mon_mail' => $mon_mail,
                'mon_indicatif' => $this->request->getPost('text_input_mon_indicatif'),
                'mon_iconid' => $this->request->getPost('marker')
            ];
            
            $resultat_update_compte = $this->signupModel->update_compte_administrateur($data);
            
            if ($resultat_update_compte !== false) {
                $mes_infos = $this->session->get('deliverdata');
                
                $deliveryData = [
                    'code_administrateur' => $mon_code,
                    'nom_administrateur' => $this->request->getPost('text_input_mon_nom'),
                    'prenom_administrateur' => $this->request->getPost('text_input_mon_prenom'),
                    'telephone_administrateur' => $this->request->getPost('text_input_mon_telephone'),
                    'mail_administrateur' => $mon_mail,
                    'indicatif_administrateur' => $this->request->getPost('text_input_mon_indicatif'),
                    'icone_administrateur' => $this->request->getPost('marker'),
                    'etat_administrateur' => $mes_infos['etat_administrateur'] ?? '',
                    'date_fin_validite_licence' => $mes_infos['date_fin_validite_licence'] ?? '',
                    'date_creation_compte_administrateur' => $mes_infos['date_creation_compte_administrateur'] ?? ''
                ];
                
                $this->session->set([
                    'login' => $this->session->get('login'),
                    'logged' => true,
                    'deliverdata' => $deliveryData
                ]);
                
                $data = [
                    'succes' => 'Votre compte à bien été mis à jour.',
                    'titre' => 'REZO+ PC INLINE | Mon compte',
                    'heading' => 'Bienvenue dans REZO+ PC InLine',
                    'footing' => 'copyright@2019 <a href ="https://www.web-dream.fr" target="_blank">Web-Dream</a>',
                    'utilisateur' => $this->session->get('deliverdata')
                ];
                return view('mon_compte', $data);
            } else {
                return $this->retourne_une_erreur_au_formulaire('Erreur réseau.<br />Veuillez recommencer ultérieurement.');
            }
            } else {
                // Validation échouée - afficher le formulaire avec les erreurs
                $data = [
                    'titre' => 'REZO+ PC INLINE | Mon compte',
                    'heading' => 'Bienvenue dans REZO+ PC InLine',
                    'footing' => 'copyright@2019 <a href ="https://www.web-dream.fr" target="_blank">Web-Dream</a>',
                    'utilisateur' => $this->session->get('deliverdata'),
                    'validation' => $this->validator
                ];
                return view('mon_compte', $data);
            }
        } else {
            // Affichage initial du formulaire (GET)
            $data = [
                'titre' => 'REZO+ PC INLINE | Mon compte',
                'heading' => 'Bienvenue dans REZO+ PC InLine',
                'footing' => 'copyright@2019 <a href ="https://www.web-dream.fr" target="_blank">Web-Dream</a>',
                'utilisateur' => $this->session->get('deliverdata'),
                'validation' => $this->validator
            ];
            return view('mon_compte', $data);
        }
    }
    
    private function retourne_une_erreur_au_formulaire($message)
    {
        $data = [
            'error' => $message,
            'titre' => 'REZO+ PC INLINE | Mon compte',
            'heading' => 'Bienvenue dans REZO+ PC InLine',
            'footing' => 'copyright@2019 <a href ="https://www.web-dream.fr" target="_blank">Web-Dream</a>',
            'utilisateur' => $this->session->get('deliverdata'),
            'validation' => $this->validator
        ];
        return view('mon_compte', $data);
    }
}

