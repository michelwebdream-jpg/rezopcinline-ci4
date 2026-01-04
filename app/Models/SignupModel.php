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
    
    public function envoi_mot_de_passe($data)
    {
        $url = getenv('APP_SERVER_URL') . getenv('SENDPASSWORD_URI');
        return $this->postCURL($url, $data);
    }
    
    public function modifier_mot_de_passe($data)
    {
        $url = getenv('APP_SERVER_URL') . getenv('UPDATEPASSWORD_URI');
        return $this->postCURL($url, $data);
    }
    
    public function update_compte_administrateur($data)
    {
        $url = getenv('APP_SERVER_URL') . getenv('UPDATEUSER_URI');
        return $this->postCURL($url, $data);
    }
    
    public function check_code_et_licence($code, $pass)
    {
        $passData = [
            "mon_code" => $code,
            "mon_mot_de_passe" => $pass
        ];
        $url = getenv('APP_SERVER_URL') . getenv('LIT_INFO_ADMINISTRATEUR_URI');
        return $this->postCURL($url, $passData);
    }
    
    public function test_si_code_pc_dans_table_contact($code)
    {
        $passData = [
            "mon_code" => $code
        ];
        $url = getenv('APP_SERVER_URL') . getenv('AJOUTE_CODE_PC_TABLE_CONTACT_URI');
        return $this->postCURL($url, $passData);
    }
    
    public function mise_a_jour_pour_galerie_photo($code)
    {
        $passData = [
            "code_PC" => $code,
            "size_limit_galerie" => "100",
            "appli_type_PC" => "3"
        ];
        $url = getenv('APP_SERVER_URL') . getenv('MAJ_APP_POUR_GALERIE_PHOTO_URI');
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
        $url = getenv('APP_SERVER_URL') . getenv('REGISTER_URI');
        return $this->postCURL($url, $passData);
    }
    
    private function postCURL($url, $param)
    {
        try {
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
            
            if ($httpCode >= 400) {
                log_message('error', 'HTTP Error: ' . $httpCode . ' - URL: ' . $url);
                return false;
            }
            
            return $response->getBody();
            
        } catch (\Exception $e) {
            log_message('error', 'cURL Error: ' . $e->getMessage() . ' - URL: ' . $url);
            return false;
        }
    }
}

