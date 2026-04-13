<?php

namespace App\Controllers;

use App\Models\SignupModel;
use CodeIgniter\Controller;
use CodeIgniter\Email\Email;

class Envoi_password extends BaseController
{
    protected $session;
    protected $validation;
    protected $signupModel;
    protected Email $email;
    
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->signupModel = model('SignupModel');
        $this->email = \Config\Services::email();
    }
    
    public function index()
    {
        // Ne valider que si c'est une requête POST
        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'text_input_mon_email' => [
                    'label' => 'Mon adresse email',
                    'rules' => 'trim|required|valid_email',
                ],
            ];
            
            if ($this->validate($rules)) {
            $data = [
                'mon_email' => $this->request->getPost('text_input_mon_email'),
                'base_url' => base_url(),
            ];
            
            $resultat_envoi_password = $this->signupModel->envoi_mot_de_passe($data);
            
            if ($resultat_envoi_password !== false) {
                $payload = json_decode((string) $resultat_envoi_password, true);

                if (is_array($payload) && ($payload['status'] ?? '') === '1' && !empty($payload['email']) && !empty($payload['subject']) && !empty($payload['html'])) {
                    $this->email->setFrom('info@web-dream.fr', 'REZO+ PC Inline - Web-Dream');
                    $this->email->setTo((string) $payload['email']);
                    $this->email->setSubject((string) $payload['subject']);
                    $this->email->setMailType('html');
                    $this->email->setMessage((string) $payload['html']);

                    if (!$this->email->send()) {
                        log_message('error', 'Echec envoi SMTP reset password: ' . $this->email->printDebugger(['headers', 'subject']));
                        return $this->retourne_une_erreur_au_formulaire('Erreur.<br />Le serveur n\'a pas pu envoyer l\'email de réinitialisation. Veuillez réessayer plus tard.');
                    }

                    $data = [
                        'succes' => 'Un lien de réinitialisation a été envoyé à votre adresse email. Consultez votre boîte de réception (et les spams) puis cliquez sur le lien pour définir un nouveau mot de passe.',
                        'titre' => 'REZO+ PC INLINE | Mon compte',
                        'heading' => 'Bienvenue dans REZO+ PC InLine',
                        'footing' => footer_html()
                    ];
                    return view('envoi_password', $data);
                } else if ($resultat_envoi_password == "-2") {
                    return $this->retourne_une_erreur_au_formulaire('Erreur.<br />Le serveur n\'a pas pu envoyer l\'email de réinitialisation. Veuillez réessayer plus tard.');
                } else {
                    return $this->retourne_une_erreur_au_formulaire('Erreur.<br />Cette adresse email n\'existe pas.');
                }
            } else {
                return $this->retourne_une_erreur_au_formulaire('Erreur réseau.<br />Veuillez recommencer ultérieurement.');
            }
            } else {
                // Validation échouée - afficher le formulaire avec les erreurs
                $data = [
                    'titre' => 'REZO+ PC INLINE | Envoyer mon code et mon mot de passe',
                    'heading' => 'Bienvenue dans REZO+ PC InLine',
                    'footing' => footer_html(),
                    'validation' => $this->validator
                ];
                return view('envoi_password', $data);
            }
        } else {
            // Affichage initial du formulaire (GET)
            $data = [
                'titre' => 'REZO+ PC INLINE | Mot de passe oublié',
                'heading' => 'Bienvenue dans REZO+ PC InLine',
                'footing' => footer_html(),
                'validation' => $this->validator,
                'error' => $this->session->getFlashdata('error'),
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
            'footing' => footer_html(),
            'validation' => $this->validator
        ];
        return view('envoi_password', $data);
    }
}

