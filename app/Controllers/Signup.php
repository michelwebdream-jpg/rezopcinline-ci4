<?php

namespace App\Controllers;

use App\Models\SignupModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Session\Session;
use CodeIgniter\Email\Email;

class Signup extends BaseController
{
    protected $session;
    protected $validation;
    protected $email;
    protected $signupModel;
    
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->email = \Config\Services::email();
        $this->signupModel = model('SignupModel');
    }
    
    public function index()
    {
        if ($this->session->get('login') || $this->session->get('logged')) {
            return redirect()->to('/signup/membres');
        }
        
        // Si on accède depuis la racine / et que l'utilisateur n'est pas connecté, rediriger vers login
        $currentPath = $this->request->getUri()->getPath();
        if ($currentPath === '/' && $this->request->getMethod() === 'get') {
            return redirect()->to('/signup/login');
        }
        
        // Ne valider que si c'est une requête POST
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'text_input_mon_nom' => 'trim|required',
                'text_input_mon_prenom' => 'trim|required',
                'text_input_mon_telephone' => 'trim|required',
                'text_input_mon_mail' => 'trim|required|valid_email|matches[text_input_mon_mail2]',
                'text_input_mon_mail2' => 'trim|required|valid_email',
                'text_input_mon_indicatif' => 'trim|required',
                'text_input_mon_password1' => 'trim|required|min_length[5]|matches[text_input_mon_password2]',
                'text_input_mon_password2' => 'trim|required|min_length[5]',
                'text_input_cle_de_licence' => 'trim|required',
            ];
            
            if ($this->validate($rules)) {
            $data = [
                'text_input_mon_nom' => $this->request->getPost('text_input_mon_nom'),
                'text_input_mon_prenom' => $this->request->getPost('text_input_mon_prenom'),
                'text_input_mon_telephone' => $this->request->getPost('text_input_mon_telephone'),
                'text_input_mon_mail' => $this->request->getPost('text_input_mon_mail'),
                'text_input_mon_mail2' => $this->request->getPost('text_input_mon_mail2'),
                'text_input_mon_indicatif' => $this->request->getPost('text_input_mon_indicatif'),
                'text_input_mon_password1' => $this->request->getPost('text_input_mon_password1'),
                'text_input_mon_password2' => $this->request->getPost('text_input_mon_password2'),
                'text_input_cle_de_licence' => $this->request->getPost('text_input_cle_de_licence'),
                'radio_button_icon_id' => $this->request->getPost('marker')
            ];
            
            $resultat_creation_de_compte = $this->signupModel->creer_compte_administrateur($data);
            
            if ($resultat_creation_de_compte !== false) {
                $resultat_creation_de_compte = str_replace("return_txt=", "", $resultat_creation_de_compte);
                
                if (strpos($resultat_creation_de_compte, "ok") === 0) {
                    $resultat_creation_de_compte = substr($resultat_creation_de_compte, 2);
                    $code_administrateur = substr($resultat_creation_de_compte, 0, 8);
                    $date_fin_validite_licence = substr($resultat_creation_de_compte, 8);
                    
                    // Envoi email
                    $this->email->setFrom('info@web-dream.fr', 'REZO+ PC Inline - Web-Dream');
                    $this->email->setTo($this->request->getPost('text_input_mon_mail'));
                    $this->email->setSubject('Confirmation de création de compte REZO+ PC Inline.');
                    $message = "Bonjour,<br />vous trouverez ci-dessous les informations de connexions à votre compte REZO+.<br /><br />Code REZO+ : " . $code_administrateur . "<br />" . "Mot de passse : " . $this->request->getPost('text_input_mon_password1') . "<br /><br />" . "Adresse de connexion : <a href=\"http://www.web-dream.fr/rezopcinline\">REZO+ PC INLINE</a>" . "<br /><br />" . "Merci de votre confiance.<br />L'équipe Web-Dream";
                    $this->email->setMessage($message);
                    $this->email->send();
                    
                    $data = [
                        'titre' => 'REZO+ PC INLINE | Créer un compte',
                        'heading' => 'Bienvenue dans REZO+ PC InLine',
                        'footing' => 'copyright@2019 <a href ="https://www.web-dream.fr" target="_blank">Web-Dream</a>',
                        'code_administrateur' => $code_administrateur,
                        'date_fin_validite_licence' => $date_fin_validite_licence
                    ];
                    
                    return view('succes_creation_compte', $data);
                } else {
                    $this->retourne_une_erreur_au_formulaire_signup($this->getErrorMessage($resultat_creation_de_compte));
                }
            } else {
                $this->retourne_une_erreur_au_formulaire_signup('Erreur réseau.<br />Veuillez recommencer ultérieurement.');
            }
            } else {
                // Validation échouée - afficher le formulaire avec les erreurs
                $data = [
                    'titre' => 'REZO+ PC INLINE | Créer un compte',
                    'heading' => 'Bienvenue dans REZO+ PC InLine',
                    'footing' => 'copyright@2019 <a href ="https://www.web-dream.fr" target="_blank">Web-Dream</a>',
                    'validation' => $this->validator
                ];
                return view('signup', $data);
            }
        } else {
            // Affichage initial du formulaire (GET)
            $data = [
                'titre' => 'REZO+ PC INLINE | Créer un compte',
                'heading' => 'Bienvenue dans REZO+ PC InLine',
                'footing' => 'copyright@2019 <a href ="https://www.web-dream.fr" target="_blank">Web-Dream</a>',
                'validation' => $this->validator
            ];
            return view('signup', $data);
        }
    }
    
    public function login()
    {
        if ($this->session->get('login') || $this->session->get('logged')) {
            return redirect()->to('/signup/membres');
        }
        
        $rules = [
            'code' => 'trim|required',
            'pass' => 'trim|required'
        ];
        
        if ($this->validate($rules)) {
            $resultat_verif_code_et_licence = $this->signupModel->check_code_et_licence(
                $this->request->getPost('code'),
                $this->request->getPost('pass')
            );
            
            if ($resultat_verif_code_et_licence !== false) {
                $resultat_verif_code_et_licence = str_replace("return_txt=", "", $resultat_verif_code_et_licence);
                
                // Nettoyer la réponse pour supprimer les warnings PHP et les balises HTML
                $cleaned_response = preg_replace('/<br \/>\s*<b>(Deprecated|Warning|Fatal error|Notice)<\/b>.*?(<br \/>|$)/', '', $resultat_verif_code_et_licence);
                $cleaned_response = preg_replace('/<[^>]*>/', '', $cleaned_response);
                $cleaned_response = trim($cleaned_response);
                $resultat_verif_code_et_licence = $cleaned_response;
                
                if ($resultat_verif_code_et_licence == "") {
                    $this->retourne_une_erreur_au_formulaire('Erreur réseau !<br />Lecture des informations impossible...');
                } else if ($resultat_verif_code_et_licence == "-1") {
                    $this->retourne_une_erreur_au_formulaire('Erreur de licence !<br />Clé de licence expirée! <br />Veuillez renouveller votre clé de licence sur le site www.web-dream.fr"');
                } else if ($resultat_verif_code_et_licence == "-2") {
                    $this->retourne_une_erreur_au_formulaire('Erreur !<br />Le code et/ou le mot de passe sont incorrects');
                } else if ($resultat_verif_code_et_licence == "-3") {
                    $this->retourne_une_erreur_au_formulaire('Erreur !<br />Clé de licence inactive!');
                } else {
                    $verifie_si_code_pc_dans_table_pc = $this->signupModel->test_si_code_pc_dans_table_contact($this->request->getPost('code'));
                    
                    if ($verifie_si_code_pc_dans_table_pc !== false) {
                        $mise_a_jour_galerie_photo = $this->signupModel->mise_a_jour_pour_galerie_photo($this->request->getPost('code'));
                        
                        if ($mise_a_jour_galerie_photo !== false) {
                            $temp = explode("><", $resultat_verif_code_et_licence);
                            
                            // Vérifier que le tableau a suffisamment d'éléments
                            if (count($temp) >= 10) {
                                $deliveryData = [
                                    'code_administrateur' => $temp[0] ?? '',
                                    'nom_administrateur' => $temp[1] ?? '',
                                    'prenom_administrateur' => $temp[2] ?? '',
                                    'telephone_administrateur' => $temp[3] ?? '',
                                    'mail_administrateur' => $temp[4] ?? '',
                                    'indicatif_administrateur' => $temp[5] ?? '',
                                    'icone_administrateur' => $temp[6] ?? '',
                                    'etat_administrateur' => $temp[7] ?? '',
                                    'date_fin_validite_licence' => $temp[8] ?? '',
                                    'date_creation_compte_administrateur' => $temp[9] ?? ''
                                ];
                                
                                $this->session->set([
                                    'login' => $this->request->getPost('code'),
                                    'logged' => true,
                                    'deliverdata' => $deliveryData
                                ]);
                                
                                return redirect()->to('/signup/membres');
                            } else {
                                log_message('error', 'Signup Controller: Invalid response format. Response: ' . $resultat_verif_code_et_licence);
                                $this->retourne_une_erreur_au_formulaire('Erreur interne.<br />Format de réponse invalide (erreur 1003).');
                            }
                        } else {
                            $this->retourne_une_erreur_au_formulaire('Erreur réseau.<br />Veuillez recommencer ultérieurement (erreur 1000).');
                        }
                    } else {
                        $this->retourne_une_erreur_au_formulaire('Erreur réseau.<br />Veuillez recommencer ultérieurement (erreur 1001).');
                    }
                }
            } else {
                $this->retourne_une_erreur_au_formulaire('Erreur réseau.<br />Veuillez recommencer ultérieurement (erreur 1002).');
            }
        } else {
            $data = [
                'titre' => 'REZO+ PC INLINE | Connexion',
                'heading' => 'Bienvenue dans REZO+ PC InLine',
                'footing' => 'copyright@2019 <a href ="https://www.web-dream.fr" target="_blank">Web-Dream</a>',
                'validation' => $this->validator
            ];
            return view('login', $data);
        }
    }
    
    public function logout()
    {
        $this->session->remove(['login', 'logged', 'deliverdata']);
        $this->session->destroy();
        return redirect()->to('/');
    }
    
    public function membres()
    {
        if (!$this->session->get('login') || !$this->session->get('logged')) {
            return redirect()->to('/');
        } else {
            $this->email->setFrom('info@web-dream.fr', 'REZO+ PC Inline - Web-Dream');
            $this->email->setTo('info@web-dream.fr');
            $this->email->setSubject('Connexion à REZO+ PC Inline');
            
            $user_logged = $this->session->get('deliverdata');
            $user_nom = $user_logged['nom_administrateur'] ?? '';
            $user_prenom = $user_logged['prenom_administrateur'] ?? '';
            date_default_timezone_set('Europe/Paris');
            $date_connexion = date('d-m-Y H:i:s');
            $message_connexion = 'Nom : ' . $user_nom . '<br />Prénom : ' . $user_prenom . '<br />Date : ' . $date_connexion;
            $this->email->setMessage("Bonjour,<br />une connexion à REZO+ PC Inline vient d'être effectuée.<br />Voiciles détails :<br />" . $message_connexion);
            $this->email->send();
            
            return redirect()->to('/membres');
        }
    }
    
    private function retourne_une_erreur_au_formulaire_signup($message)
    {
        $data = [
            'error' => $message,
            'titre' => 'REZO+ PC INLINE | Créer un compte',
            'heading' => 'Bienvenue dans REZO+ PC InLine',
            'footing' => 'copyright@2019 <a href ="https://www.web-dream.fr" target="_blank">Web-Dream</a>',
            'validation' => $this->validator
        ];
        return view('signup', $data);
    }
    
    private function retourne_une_erreur_au_formulaire($message)
    {
        $data = [
            'error' => $message,
            'titre' => 'REZO+ PC INLINE | Connexion',
            'heading' => 'Bienvenue dans REZO+ PC InLine',
            'footing' => 'copyright@2019 <a href ="https://www.web-dream.fr" target="_blank">Web-Dream</a>',
            'validation' => $this->validator
        ];
        return view('login', $data);
    }
    
    private function getErrorMessage($code)
    {
        $messages = [
            '1' => 'Erreur de compte !<br />Cette adresse mail est déjà enrergistrée sur un autre compte. Création du compte impossible...',
            '2' => 'Erreur de licence !<br />Cette clé licence est associée à un autre compte. Création du compte impossible.',
            '3' => 'Erreur de licence !<br />Cette clé de licence n\'existe pas. Création du compte impossible.',
            '4' => 'Erreur de licence !<br />Cette clé de licence est déjà active. Création du compte impossible.',
            '5' => 'Erreur de licence !<br />Cette clé de licence a expirée. Création du compte impossible.',
            '6' => 'Erreur système !<br />Création du compte impossible.',
            '-1' => 'Erreur de licence !<br />Création du compte impossible.'
        ];
        
        return $messages[$code] ?? 'Erreur inconnue.';
    }
}

