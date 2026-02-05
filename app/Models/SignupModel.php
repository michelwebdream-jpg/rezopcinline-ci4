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
            $envUrl = getenv('APP_SERVER_URL');
            if (!empty($envUrl)) {
                log_message('debug', 'getAppServerURL - Local environment, using .env value: ' . $envUrl);
                return $envUrl;
            }
            $url = $protocol . '://' . $hostname;
            log_message('debug', 'getAppServerURL - Local environment, auto-detected: ' . $url);
            return $url;
        }
        
        // Détecter le serveur de test (rezoci4.web-dream.fr)
        // IMPORTANT: Ignorer la valeur du .env sur le serveur de test
        if (strpos($hostname, 'rezoci4.web-dream.fr') !== false) {
            $url = 'https://rezoci4.web-dream.fr';
            log_message('debug', 'getAppServerURL - Detected test server, returning: ' . $url);
            return $url;
        }
        
        // Production (www.web-dream.fr)
        // Si l'app est dans le sous-dossier /rezopcinline/, inclure ce préfixe pour que les appels API
        // (dev/rezo_flash_code/..., etc.) pointent vers https://www.web-dream.fr/rezopcinline/dev/...
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($scriptName, '/rezopcinline/') !== false || strpos($requestUri, '/rezopcinline') === 0) {
            $url = 'https://www.web-dream.fr/rezopcinline';
            log_message('debug', 'getAppServerURL - Production with /rezopcinline subfolder: ' . $url);
            return $url;
        }
        $url = 'https://www.web-dream.fr';
        log_message('debug', 'getAppServerURL - Using production default: ' . $url);
        return $url;
    }
    
    public function envoi_mot_de_passe($data)
    {
        $url = $this->getAppServerURL() . getenv('SENDPASSWORD_URI');
        return $this->postCURL($url, $data);
    }
    
    public function modifier_mot_de_passe($data)
    {
        $url = $this->getAppServerURL() . getenv('UPDATEPASSWORD_URI');
        return $this->postCURL($url, $data);
    }
    
    public function update_compte_administrateur($data)
    {
        $url = $this->getAppServerURL() . getenv('UPDATEUSER_URI');
        return $this->postCURL($url, $data);
    }
    
    public function check_code_et_licence($code, $pass)
    {
        $passData = [
            "mon_code" => $code,
            "mon_mot_de_passe" => $pass
        ];
        $baseUrl = $this->getAppServerURL();
        $uri = getenv('LIT_INFO_ADMINISTRATEUR_URI');
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
        $url = $this->getAppServerURL() . getenv('AJOUTE_CODE_PC_TABLE_CONTACT_URI');
        return $this->postCURL($url, $passData);
    }
    
    public function mise_a_jour_pour_galerie_photo($code)
    {
        $passData = [
            "code_PC" => $code,
            "size_limit_galerie" => "100",
            "appli_type_PC" => "3"
        ];
        $url = $this->getAppServerURL() . getenv('MAJ_APP_POUR_GALERIE_PHOTO_URI');
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
        $url = $this->getAppServerURL() . getenv('REGISTER_URI');
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

