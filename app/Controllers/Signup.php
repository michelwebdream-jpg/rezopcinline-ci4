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
        
        // Ne valider que si c'est une requête POST
        if ($this->request->getMethod() === 'POST') {
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
                    return $this->retourne_une_erreur_au_formulaire_signup($this->getErrorMessage($resultat_creation_de_compte));
                }
            } else {
                return $this->retourne_une_erreur_au_formulaire_signup('Erreur réseau.<br />Veuillez recommencer ultérieurement.');
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
        error_log('=== LOGIN METHOD CALLED ===');
        error_log('Method: ' . $this->request->getMethod());
        
        if ($this->session->get('login') || $this->session->get('logged')) {
            error_log('=== ALREADY LOGGED IN, REDIRECTING ===');
            return redirect()->to('/signup/membres');
        }
        
        // Ne valider que si c'est une requête POST
        if ($this->request->getMethod() === 'POST') {
            error_log('=== LOGIN POST DETECTED ===');
            error_log('Code: ' . $this->request->getPost('code'));
            error_log('Pass: ' . (strlen($this->request->getPost('pass')) > 0 ? 'present' : 'vide'));
            
            $rules = [
                'code' => 'trim|required',
                'pass' => 'trim|required'
            ];
            
            if ($this->validate($rules)) {
                error_log('=== VALIDATION REUSSIE ===');
                $resultat_verif_code_et_licence = $this->signupModel->check_code_et_licence(
                $this->request->getPost('code'),
                $this->request->getPost('pass')
            );
            
            error_log('=== RESULTAT API: ' . ($resultat_verif_code_et_licence !== false ? 'OK' : 'FALSE') . ' ===');
            
            if ($resultat_verif_code_et_licence !== false) {
                error_log('=== TRAITEMENT DU RESULTAT API ===');
                error_log('=== RESULTAT ORIGINAL (premiers 200 chars): ' . substr($resultat_verif_code_et_licence, 0, 200) . ' ===');
                $resultat_verif_code_et_licence = str_replace("return_txt=", "", $resultat_verif_code_et_licence);
                
                // Nettoyer la réponse pour supprimer les warnings PHP et les balises HTML
                // Mais NE PAS supprimer les < > qui font partie du format (><)
                $cleaned_response = preg_replace('/<br \/>\s*<b>(Deprecated|Warning|Fatal error|Notice)<\/b>.*?(<br \/>|$)/', '', $resultat_verif_code_et_licence);
                // Ne supprimer que les balises HTML complètes, pas les séparateurs ><
                // Format attendu: element1><element2><element3>...
                // On ne doit supprimer que les balises HTML comme <br />, <b>, etc., pas les >< entre éléments
                $cleaned_response = preg_replace('/<(br|b|i|u|strong|em|p|div|span)[^>]*>/i', '', $cleaned_response);
                $cleaned_response = preg_replace('/<\/(br|b|i|u|strong|em|p|div|span)>/i', '', $cleaned_response);
                $cleaned_response = trim($cleaned_response);
                $resultat_verif_code_et_licence = $cleaned_response;
                
                error_log('=== RESULTAT NETTOYE COMPLET: ' . $resultat_verif_code_et_licence . ' ===');
                
                if ($resultat_verif_code_et_licence == "") {
                    return $this->retourne_une_erreur_au_formulaire('Erreur réseau !<br />Lecture des informations impossible...');
                } else if ($resultat_verif_code_et_licence == "-1") {
                    return $this->retourne_une_erreur_au_formulaire('Erreur de licence !<br />Clé de licence expirée! <br />Veuillez renouveller votre clé de licence sur le site www.web-dream.fr"');
                } else if ($resultat_verif_code_et_licence == "-2") {
                    return $this->retourne_une_erreur_au_formulaire('Erreur !<br />Le code et/ou le mot de passe sont incorrects');
                } else if ($resultat_verif_code_et_licence == "-3") {
                    return $this->retourne_une_erreur_au_formulaire('Erreur !<br />Clé de licence inactive!');
                } else {
                    error_log('=== APPEL test_si_code_pc_dans_table_contact ===');
                    $verifie_si_code_pc_dans_table_pc = $this->signupModel->test_si_code_pc_dans_table_contact($this->request->getPost('code'));
                    error_log('=== RESULTAT test_si_code_pc_dans_table_contact: ' . ($verifie_si_code_pc_dans_table_pc !== false ? 'OK' : 'FALSE') . ' ===');
                    
                    if ($verifie_si_code_pc_dans_table_pc !== false) {
                        error_log('=== APPEL mise_a_jour_pour_galerie_photo ===');
                        $mise_a_jour_galerie_photo = $this->signupModel->mise_a_jour_pour_galerie_photo($this->request->getPost('code'));
                        
                        if ($mise_a_jour_galerie_photo !== false) {
                            error_log('=== EXPLOSION DU RESULTAT ===');
                            $temp = explode("><", $resultat_verif_code_et_licence);
                            error_log('=== NOMBRE D ELEMENTS: ' . count($temp) . ' ===');
                            
                            // Vérifier que le tableau a suffisamment d'éléments (au moins 9, car le format peut varier)
                            if (count($temp) >= 9) {
                                error_log('=== FORMAT CORRECT, CREATION DELIVERY DATA ===');
                                $deliveryData = [
                                    'code_administrateur' => $temp[0] ?? '',
                                    'nom_administrateur' => $temp[1] ?? '',
                                    'prenom_administrateur' => $temp[2] ?? '',
                                    'telephone_administrateur' => $temp[3] ?? '',
                                    'mail_administrateur' => $temp[4] ?? '',  // Mail en position 4 (confirmé par test API)
                                    'indicatif_administrateur' => $temp[5] ?? '',  // Indicatif en position 5 (confirmé par test API)
                                    'icone_administrateur' => $temp[6] ?? '',
                                    'etat_administrateur' => $temp[7] ?? '',
                                    'date_fin_validite_licence' => $temp[8] ?? '',
                                    'date_creation_compte_administrateur' => $temp[9] ?? ''
                                ];
                                
                                error_log('=== CREATION DE LA SESSION ===');
                                $this->session->set([
                                    'login' => $this->request->getPost('code'),
                                    'logged' => true,
                                    'deliverdata' => $deliveryData
                                ]);
                                
                                error_log('=== REDIRECTION VERS /signup/membres ===');
                                return redirect()->to('/signup/membres');
                            } else {
                                log_message('error', 'Signup Controller: Invalid response format. Response: ' . $resultat_verif_code_et_licence);
                                return $this->retourne_une_erreur_au_formulaire('Erreur interne.<br />Format de réponse invalide (erreur 1003).');
                            }
                        } else {
                            return $this->retourne_une_erreur_au_formulaire('Erreur réseau.<br />Veuillez recommencer ultérieurement (erreur 1000).');
                        }
                    } else {
                        return $this->retourne_une_erreur_au_formulaire('Erreur réseau.<br />Veuillez recommencer ultérieurement (erreur 1001).');
                    }
                }
            } else {
                return $this->retourne_une_erreur_au_formulaire('Erreur réseau.<br />Veuillez recommencer ultérieurement (erreur 1002).');
            }
            } else {
                // Validation échouée - afficher le formulaire avec les erreurs
                error_log('=== VALIDATION ECHOUE ===');
                error_log('Erreurs: ' . json_encode($this->validator->getErrors()));
                $data = [
                    'titre' => 'REZO+ PC INLINE | Connexion',
                    'heading' => 'Bienvenue dans REZO+ PC InLine',
                    'footing' => 'copyright@2019 <a href ="https://www.web-dream.fr" target="_blank">Web-Dream</a>',
                    'validation' => $this->validator
                ];
                return view('login', $data);
            }
        } else {
            // Affichage initial du formulaire (GET)
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

