<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\HTTP\CURLRequest;

class SignupModel extends Model
{
    protected $curlClient;
    
    public function __construct()
    {
        parent::__construct();
        $this->curlClient = \Config\Services::curlrequest();
    }
    
    /**
     * Détecte automatiquement APP_SERVER_URL selon l'environnement
     * - Local: utilise la valeur du .env ou détecte automatiquement
     * - Test (rezoci4.web-dream.fr): https://rezoci4.web-dream.fr
     * - Production (web-dream.fr/rezopcinline): https://www.web-dream.fr
     */
    private function getAppServerURL(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        log_message('debug', 'getAppServerURL - Protocol: ' . $protocol);
        log_message('debug', 'getAppServerURL - Hostname: ' . $hostname);
        
        // Détecter si on est en local
        $is_local = false;
        $local_indicators = ['localhost', '127.0.0.1', '::1', 'local', '.local', '.dev'];
        foreach ($local_indicators as $indicator) {
            if (stripos($hostname, $indicator) !== false) {
                $is_local = true;
                break;
            }
        }
        
        // Si local, utiliser la valeur du .env ou l'URL locale
        if ($is_local) {
            $envUrl = $_ENV['APP_SERVER_URL'] ?? getenv('APP_SERVER_URL');
            if (!empty($envUrl)) {
                log_message('debug', 'getAppServerURL - Local environment, using .env value: ' . $envUrl);
                return $envUrl;
            }
            $url = $protocol . '://' . $hostname;
            log_message('debug', 'getAppServerURL - Local environment, auto-detected: ' . $url);
            return $url;
        }
        
        // Détecter le serveur de test (rezoci4.web-dream.fr)
        if (strpos($hostname, 'rezoci4.web-dream.fr') !== false) {
            $url = 'https://rezoci4.web-dream.fr';
            log_message('debug', 'getAppServerURL - Detected test server, returning: ' . $url);
            return $url;
        }
        
        // Production (www.web-dream.fr / web-dream.fr)
        // L'API doit pointer vers rezopcinline/public/dev/... donc base = https://www.web-dream.fr/rezopcinline
        // On force toujours /rezopcinline en prod pour que creat_customer.php soit bien celui du projet CI4.
        if (strpos($hostname, 'web-dream.fr') !== false) {
            $url = 'https://www.web-dream.fr/rezopcinline';
            log_message('debug', 'getAppServerURL - Production web-dream.fr, API base: ' . $url);
            return $url;
        }
        
        $url = $protocol . '://' . $hostname;
        log_message('debug', 'getAppServerURL - Fallback: ' . $url);
        return $url;
    }
    
    /** Lit une clé du .env (CI4 charge dans $_ENV, getenv() peut être vide en production) */
    private function envUri(string $key, string $default): string
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    public function envoi_mot_de_passe($data)
    {
        $url = $this->getAppServerURL() . $this->envUri('SENDPASSWORD_URI', '/dev/rezo_flash_code/send_password.php');
        return $this->postCURL($url, $data);
    }
    
    public function modifier_mot_de_passe($data)
    {
        $url = $this->getAppServerURL() . $this->envUri('UPDATEPASSWORD_URI', '/dev/rezo_flash_code/update_password.php');
        return $this->postCURL($url, $data);
    }

    /**
     * Réinitialise le mot de passe via un token (lien « mot de passe oublié »).
     * @param string $token Token reçu par email
     * @param string $newPassword Nouveau mot de passe
     * @return string|false '1' succès, '0' ou '-1' échec, false erreur réseau
     */
    public function reset_password_by_token(string $token, string $newPassword)
    {
        $url = $this->getAppServerURL() . '/dev/rezo_flash_code/reset_password_by_token.php';
        $data = [
            'token' => $token,
            'mon_password_new' => $newPassword,
        ];
        return $this->postCURL($url, $data);
    }
    
    public function update_compte_administrateur($data)
    {
        $url = $this->getAppServerURL() . $this->envUri('UPDATEUSER_URI', '/dev/rezo_flash_code/updateuser_customer.php');
        return $this->postCURL($url, $data);
    }
    
    public function check_code_et_licence($code, $pass)
    {
        $passData = [
            "mon_code" => $code,
            "mon_mot_de_passe" => $pass
        ];
        $baseUrl = $this->getAppServerURL();
        $uri = $this->envUri('LIT_INFO_ADMINISTRATEUR_URI', '/dev/rezo_flash_code/lit_info_administrateur.php');
        $url = $baseUrl . $uri;
        
        log_message('debug', 'check_code_et_licence - Base URL: ' . $baseUrl);
        log_message('debug', 'check_code_et_licence - URI: ' . $uri);
        log_message('debug', 'check_code_et_licence - Full URL: ' . $url);
        
        return $this->postCURL($url, $passData);
    }
    
    public function test_si_code_pc_dans_table_contact($code)
    {
        $passData = [
           "mon_code" => $code
        ];
        $url = $this->getAppServerURL() . $this->envUri('AJOUTE_CODE_PC_TABLE_CONTACT_URI', '/dev/rezo_flash_code/ajoute_code_pc_table_contact.php');
        return $this->postCURL($url, $passData);
    }
    
    public function mise_a_jour_pour_galerie_photo($code)
    {
        $passData = [
            "code_PC" => $code,
            "size_limit_galerie" => "100",
            "appli_type_PC" => "3"
        ];
        $url = $this->getAppServerURL() . $this->envUri('MAJ_APP_POUR_GALERIE_PHOTO_URI', '/dev/rezo_flash_code/maj_app_pour_galerie_photo.php');
        return $this->postCURL($url, $passData);
    }
    
    public function creer_compte_administrateur($data)
    {
        $passData = [
            "mon_nom" => $data['text_input_mon_nom'] ?? '',
            "mon_prenom" => $data['text_input_mon_prenom'] ?? '',
            "mon_telephone" => $data['text_input_mon_telephone'] ?? '',
            "mon_mail" => $data['text_input_mon_mail'] ?? '',
            "mon_indicatif" => $data['text_input_mon_indicatif'] ?? '',
            "mon_iconid" => $data['radio_button_icon_id'] ?? '',
            "mon_password" => $data['text_input_mon_password1'] ?? '',
            "ma_licence" => $data['text_input_cle_de_licence'] ?? ''
        ];
        $url = $this->getAppServerURL() . $this->envUri('REGISTER_URI', '/dev/rezo_flash_code/creat_customer.php');
        return $this->postCURL($url, $passData);
    }
    
    private function postCURL($url, $param)
    {
        try {
            // Log l'URL complète pour le débogage
            log_message('debug', 'postCURL - URL: ' . $url);
            log_message('debug', 'postCURL - Params: ' . json_encode($param));
            
            $options = [
                'form_params' => $param,
                'timeout' => 30,
                'connect_timeout' => 10,
                'http_errors' => false
            ];
            
            // Pour le développement local avec HTTPS auto-signé
            if (ENVIRONMENT === 'development') {
                $options['verify'] = false;
            }
            
            $response = $this->curlClient->post($url, $options);
            
            $httpCode = $response->getStatusCode();
            $body = $response->getBody();
            
            log_message('debug', 'postCURL - HTTP Code: ' . $httpCode);
            log_message('debug', 'postCURL - Response body (first 200 chars): ' . substr($body, 0, 200));
            
            if ($httpCode >= 400) {
                log_message('error', 'HTTP Error: ' . $httpCode . ' - URL: ' . $url . ' - Response: ' . substr($body, 0, 500));
                return false;
            }
            
            return $body;
            
        } catch (\Exception $e) {
            log_message('error', 'cURL Error: ' . $e->getMessage() . ' - URL: ' . $url . ' - Trace: ' . $e->getTraceAsString());
            return false;
        }
    }
}

