<?php

namespace App\Controllers;

use App\Models\SignupModel;
use App\Models\LoginNoticeModel;
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
                'text_input_mon_nom' => [
                    'label' => 'Mon nom',
                    'rules' => 'trim|required',
                ],
                'text_input_mon_prenom' => [
                    'label' => 'Mon prénom',
                    'rules' => 'trim|required',
                ],
                'text_input_mon_telephone' => [
                    'label' => 'Mon téléphone',
                    'rules' => 'trim|required',
                ],
                'text_input_mon_mail' => [
                    'label' => 'Mon e-mail',
                    'rules' => 'trim|required|valid_email|matches[text_input_mon_mail2]',
                ],
                'text_input_mon_mail2' => [
                    'label' => 'Confirmer votre e-mail',
                    'rules' => 'trim|required|valid_email',
                ],
                'text_input_mon_indicatif' => [
                    'label' => 'Mon indicatif',
                    'rules' => 'trim|required',
                ],
                'text_input_mon_password1' => [
                    'label' => 'Mon mot de passe',
                    'rules' => 'trim|required|min_length[5]|matches[text_input_mon_password2]',
                ],
                'text_input_mon_password2' => [
                    'label' => 'Confirmer le mot de passe',
                    'rules' => 'trim|required|min_length[5]',
                ],
                'text_input_cle_de_licence' => [
                    'label' => 'Ma clé de licence',
                    'rules' => 'trim|required',
                ],
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
                    
                    // Envoi email (HTML pour liens et sauts de ligne)
                    $this->email->setFrom('info@web-dream.fr', 'REZO+ PC Inline - Web-Dream');
                    $this->email->setTo($this->request->getPost('text_input_mon_mail'));
                    $this->email->setSubject('Confirmation de création de compte REZO+ PC Inline.');
                    $this->email->setMailType('html');
                    $message = "Bonjour,<br />vous trouverez ci-dessous les informations de connexions à votre compte REZO+.<br /><br />Code REZO+ : " . $code_administrateur . "<br />" . "Mot de passse : " . $this->request->getPost('text_input_mon_password1') . "<br /><br />" . "Adresse de connexion : <a href=\"http://www.web-dream.fr/rezopcinline\">REZO+ PC INLINE</a>" . "<br /><br />" . "Merci de votre confiance.<br />L'équipe Web-Dream";
                    $this->email->setMessage($message);
                    $this->email->send();
                    
                    $data = [
                        'titre' => 'REZO+ PC INLINE | Créer un compte',
                        'heading' => 'Bienvenue dans REZO+ PC InLine',
                        'footing' => footer_html(),
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
                    'footing' => footer_html(),
                    'validation' => $this->validator
                ];
                return view('signup', $data);
            }
        } else {
            // Affichage initial du formulaire (GET)
            $data = [
                'titre' => 'REZO+ PC INLINE | Créer un compte',
                'heading' => 'Bienvenue dans REZO+ PC InLine',
                'footing' => footer_html(),
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
                'code' => [
                    'label' => 'Mon code REZO+',
                    'rules' => 'trim|required',
                ],
                'pass' => [
                    'label' => 'Mon mot de passe',
                    'rules' => 'trim|required',
                ],
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
                
                // Nettoyer la réponse pour supprimer les warnings PHP et les balises HTML qui pourraient polluer la réponse
                // Cette regex est nécessaire car lit_info_administrateur.php n'a pas display_errors désactivé
                // et des warnings PHP formatés en HTML peuvent apparaître dans la réponse
                // Format attendu: element1><element2><element3>...
                // IMPORTANT: Ne pas supprimer les éléments de la trame qui contiennent @ ou d'autres caractères
                // On supprime seulement les vraies balises HTML: <tag>, <tag/>, <tag attr="value">
                // Cela évite de matcher <info@web-dream.fr> car 'i' n'est pas suivi d'espace/> mais de 'nfo@'
                
                // Supprimer les warnings PHP formatés en HTML (ex: <br /><b>Deprecated</b>...)
                $cleaned_response = preg_replace('/<br \/>\s*<b>(Deprecated|Warning|Fatal error|Notice)<\/b>.*?(<br \/>|$)/', '', $resultat_verif_code_et_licence);
                
                // Supprimer uniquement les balises HTML à risque (br, strong, em, p, div, span)
                // NE PAS inclure b, i, u : un prénom ou autre champ peut être "b" ou "i", la trame contient ...><b><... et <b> serait supprimé à tort, ce qui décale tous les champs
                $cleaned_response = preg_replace('/<(br|strong|em|p|div|span)(\s[^>]*|\/)?>/i', '', $cleaned_response);
                $cleaned_response = preg_replace('/<\/(br|strong|em|p|div|span)>/i', '', $cleaned_response);
                
                $cleaned_response = trim($cleaned_response);
                $resultat_verif_code_et_licence = $cleaned_response;
                
                error_log('=== RESULTAT NETTOYE COMPLET: ' . $resultat_verif_code_et_licence . ' ===');
                
                if ($resultat_verif_code_et_licence == "") {
                    return $this->retourne_une_erreur_au_formulaire('Erreur réseau !<br />Lecture des informations impossible...');
                } else if ($resultat_verif_code_et_licence == "-1") {
                    return $this->retourne_une_erreur_au_formulaire('Erreur de licence !<br />Clé de licence expirée! <br />Veuillez renouveller votre clé de licence sur le site www.web-dream.fr.', true);
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
                            log_message('debug', '=== EXPLOSION DU RESULTAT ===');
                            log_message('debug', '=== TRAME BRUTE: ' . $resultat_verif_code_et_licence . ' ===');
                            $temp = explode("><", $resultat_verif_code_et_licence);
                            log_message('debug', '=== NOMBRE D ELEMENTS: ' . count($temp) . ' ===');
                            
                            // DEBUG: Afficher chaque élément avec son index
                            for ($i = 0; $i < count($temp); $i++) {
                                log_message('debug', "=== ELEMENT [$i]: '" . ($temp[$i] ?? 'VIDE') . "' (longueur: " . strlen($temp[$i] ?? '') . ") ===");
                            }
                            
                            // Mapping identique à CI3 (lignes 149-160 de Signup.php)
                            // CI3 mappe directement sans vérification du nombre d'éléments
                            // Ordre attendu selon lit_info_administrateur.php ligne 156:
                            // code><nom><prenom><telephone><mail><indicatif><iconid><etat><date_fin_validite_licence><date_creation
                            if (count($temp) >= 9) {
                                log_message('debug', '=== FORMAT CORRECT, CREATION DELIVERY DATA (mapping identique CI3) ===');
                                
                                // Mapping exact comme CI3 - même si certains éléments sont vides
                                // CI3 utilise directement $temp[4] pour le mail sans vérification
                                $deliveryData = [
                                    'code_administrateur' => $temp[0] ?? '',
                                    'nom_administrateur' => $temp[1] ?? '',
                                    'prenom_administrateur' => $temp[2] ?? '',
                                    'telephone_administrateur' => $temp[3] ?? '',
                                    'mail_administrateur' => $temp[4] ?? '',  // Mail à l'index 4 comme CI3 (peut être vide)
                                    'indicatif_administrateur' => $temp[5] ?? '',  // Indicatif à l'index 5 comme CI3
                                    'icone_administrateur' => $temp[6] ?? '',
                                    'etat_administrateur' => $temp[7] ?? '',
                                    'date_fin_validite_licence' => $temp[8] ?? '',
                                    'date_creation_compte_administrateur' => $temp[9] ?? ''
                                ];
                                
                                // DEBUG: Vérifier les valeurs mappées
                                log_message('debug', '=== MAIL MAPPED (index 4): "' . ($deliveryData['mail_administrateur'] ?: 'VIDE') . '" ===');
                                log_message('debug', '=== INDICATIF MAPPED (index 5): "' . ($deliveryData['indicatif_administrateur'] ?? 'VIDE') . '" ===');
                                log_message('debug', '=== ICONID MAPPED (index 6): "' . ($deliveryData['icone_administrateur'] ?? 'VIDE') . '" ===');
                                log_message('debug', '=== ETAT MAPPED (index 7): "' . ($deliveryData['etat_administrateur'] ?? 'VIDE') . '" ===');
                                log_message('debug', '=== DATE_FIN_VALIDITE MAPPED (index 8): "' . ($deliveryData['date_fin_validite_licence'] ?? 'VIDE') . '" ===');
                                log_message('debug', '=== DATE_CREATION MAPPED (index 9): "' . ($deliveryData['date_creation_compte_administrateur'] ?? 'VIDE') . '" ===');
                                
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
                    'footing' => footer_html(),
                    'validation' => $this->validator,
                    'success' => $this->session->getFlashdata('success'),
                ];
                return view('login', array_merge($data, $this->getLoginNoticeData()));
            }
        } else {
            // Affichage initial du formulaire (GET)
            $data = [
                'titre' => 'REZO+ PC INLINE | Connexion',
                'heading' => 'Bienvenue dans REZO+ PC InLine',
                'footing' => footer_html(),
                'validation' => $this->validator,
                'success' => $this->session->getFlashdata('success'),
            ];
            return view('login', array_merge($data, $this->getLoginNoticeData()));
        }
    }

    /**
     * Associer une nouvelle clé de licence (page publique, sans session).
     * GET : formulaire code + mot de passe + nouvelle clé.
     * POST : appel API puis redirection login avec succès ou réaffichage formulaire avec erreur.
     */
    public function associer_cle()
    {
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'code' => ['label' => 'Code REZO+', 'rules' => 'trim|required'],
                'pass' => ['label' => 'Mot de passe', 'rules' => 'trim|required'],
                'nouvelle_cle' => ['label' => 'Nouvelle clé', 'rules' => 'trim|required'],
            ];
            if (!$this->validate($rules)) {
                $data = [
                    'titre' => 'REZO+ PC INLINE | Associer une nouvelle clé',
                    'heading' => 'Bienvenue dans REZO+ PC InLine',
                    'footing' => footer_html(),
                    'validation' => $this->validator,
                ];
                return view('associer_cle', $data);
            }
            $reponse = $this->signupModel->update_licence_compte(
                $this->request->getPost('code'),
                $this->request->getPost('pass'),
                $this->request->getPost('nouvelle_cle')
            );
            if ($reponse === false) {
                $data = [
                    'error' => 'Erreur réseau.<br />Veuillez recommencer ultérieurement.',
                    'titre' => 'REZO+ PC INLINE | Associer une nouvelle clé',
                    'heading' => 'Bienvenue dans REZO+ PC InLine',
                    'footing' => footer_html(),
                    'validation' => $this->validator,
                ];
                return view('associer_cle', $data);
            }
            $reponse = trim(str_replace('return_txt=', '', $reponse));
            if (strpos($reponse, 'ok') === 0) {
                $date = (strpos($reponse, '|') !== false) ? trim(substr($reponse, strpos($reponse, '|') + 1)) : '';
                $msg = 'Votre licence a été mise à jour avec succès.';
                if ($date !== '') {
                    $msg .= ' Nouvelle date de validité : ' . esc($date) . '.';
                }
                $this->session->setFlashdata('success', $msg);
                return redirect()->to('/signup/login');
            }
            $messages = [
                'ERR_AUTH' => 'Le code et/ou le mot de passe sont incorrects.',
                'ERR_KEY_INVALID' => 'Cette clé de licence n\'existe pas ou n\'est pas valide.',
                'ERR_KEY_OTHER_ACCOUNT' => 'Cette clé est associée à un autre compte (email d\'achat différent).',
                'ERR_KEY_EXPIRED' => 'Cette clé de licence a expiré.',
                'ERR_ACTIVATE' => 'Impossible d\'activer cette clé. Veuillez réessayer ou contacter le support.',
            ];
            $error = $messages[$reponse] ?? 'Une erreur est survenue. Veuillez réessayer.';
            $data = [
                'error' => $error,
                'titre' => 'REZO+ PC INLINE | Associer une nouvelle clé',
                'heading' => 'Bienvenue dans REZO+ PC InLine',
                'footing' => footer_html(),
                'validation' => $this->validator,
            ];
            return view('associer_cle', $data);
        }
        $data = [
            'titre' => 'REZO+ PC INLINE | Associer une nouvelle clé',
            'heading' => 'Bienvenue dans REZO+ PC InLine',
            'footing' => footer_html(),
        ];
        return view('associer_cle', $data);
    }
    
    public function logout()
    {
        $this->session->remove(['login', 'logged', 'deliverdata']);
        $this->session->destroy();
        return redirect()->to('/');
    }

    /**
     * Réinitialisation du mot de passe via le lien reçu par email (token).
     * GET : affiche le formulaire nouveau mot de passe + confirmation.
     * POST : valide le token et met à jour le mot de passe.
     */
    public function reset_password()
    {
        $token = $this->request->getGet('token') ?? $this->request->getPost('token') ?? '';
        if ($token === '') {
            return redirect()->to('/envoi_password')->with('error', 'Lien invalide ou expiré. Veuillez demander un nouveau lien de réinitialisation.');
        }

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'token' => [
                    'label' => 'Token',
                    'rules' => 'required',
                ],
                'text_input_mon_password_new' => [
                    'label' => 'Nouveau mot de passe',
                    'rules' => 'trim|required|min_length[5]',
                ],
                'text_input_mon_password_confirm' => [
                    'label' => 'Confirmer le mot de passe',
                    'rules' => 'trim|required|matches[text_input_mon_password_new]',
                ],
            ];
            if ($this->validate($rules)) {
                $newPassword = $this->request->getPost('text_input_mon_password_new');
                $result = $this->signupModel->reset_password_by_token($token, $newPassword);
                if ($result !== false && trim($result) === '1') {
                    return redirect()->to('/signup/login')->with('success', 'Votre mot de passe a été réinitialisé. Vous pouvez vous connecter.');
                }
                $data = [
                    'titre' => 'REZO+ PC INLINE | Réinitialisation du mot de passe',
                    'heading' => 'Bienvenue dans REZO+ PC InLine',
                    'footing' => footer_html(),
                    'token' => $token,
                    'error' => 'Ce lien est invalide ou a expiré. Veuillez demander un nouveau lien depuis la page « Mot de passe oublié ».',
                    'validation' => $this->validator,
                ];
                return view('reset_password', $data);
            }
        }

        $data = [
            'titre' => 'REZO+ PC INLINE | Réinitialisation du mot de passe',
            'heading' => 'Bienvenue dans REZO+ PC InLine',
            'footing' => footer_html(),
            'token' => $token,
            'error' => $this->request->getGet('error') ?? ($this->session->getFlashdata('error') ?? null),
            'validation' => $this->validator,
        ];
        return view('reset_password', $data);
    }
    
    public function membres()
    {
        if (!$this->session->get('login') || !$this->session->get('logged')) {
            return redirect()->to('/');
        } else {
            $this->email->setFrom('info@web-dream.fr', 'REZO+ PC Inline - Web-Dream');
            $this->email->setTo('info@web-dream.fr');
            $this->email->setSubject('Connexion à REZO+ PC Inline');
            $this->email->setMailType('html');

            $user_logged = $this->session->get('deliverdata');
            $user_nom = $user_logged['nom_administrateur'] ?? '';
            $user_prenom = $user_logged['prenom_administrateur'] ?? '';
            date_default_timezone_set('Europe/Paris');
            $date_connexion = date('d-m-Y H:i:s');
            $message_connexion = 'Nom : ' . $user_nom . '<br />Prénom : ' . $user_prenom . '<br />Date : ' . $date_connexion;
            $this->email->setMessage("Bonjour,<br />une connexion à REZO+ PC Inline vient d'être effectuée.<br />Voici les détails :<br />" . $message_connexion);
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
            'footing' => footer_html(),
            'validation' => $this->validator
        ];
        return view('signup', $data);
    }
    
    private function retourne_une_erreur_au_formulaire($message, $showAssocierCleLink = false)
    {
        $data = [
            'error' => $message,
            'titre' => 'REZO+ PC INLINE | Connexion',
            'heading' => 'Bienvenue dans REZO+ PC InLine',
            'footing' => footer_html(),
            'validation' => $this->validator
        ];
        if ($showAssocierCleLink) {
            $data['show_associer_cle_link'] = true;
        }
        return view('login', array_merge($data, $this->getLoginNoticeData()));
    }

    /**
     * Données des annonces pour la page login (bloc d'info). Vide si table absente ou aucune annonce.
     */
    private function getLoginNoticeData(): array
    {
        try {
            $model = model(LoginNoticeModel::class);
            $notices = $model->getNoticesForLogin();
            if (empty($notices)) {
                return ['login_notices' => [], 'login_notice_duration' => 8];
            }
            return [
                'login_notices'         => $notices,
                'login_notice_duration' => $model->getDisplayDurationSeconds(),
            ];
        } catch (\Throwable $e) {
            return ['login_notices' => [], 'login_notice_duration' => 8];
        }
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

