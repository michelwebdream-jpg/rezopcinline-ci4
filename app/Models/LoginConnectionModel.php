<?php

namespace App\Models;

use CodeIgniter\I18n\Time;
use CodeIgniter\Model;

class LoginConnectionModel extends Model
{
    protected $table            = 'REZO_login_connections';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_code', 'user_name', 'user_email', 'connected_at'];

    public function recordConnection(array $userData): bool
    {
        $code = trim((string) ($userData['code_administrateur'] ?? ''));
        if ($code === '') {
            return false;
        }

        $name = trim((string) (($userData['prenom_administrateur'] ?? '') . ' ' . ($userData['nom_administrateur'] ?? '')));
        $email = trim((string) ($userData['mail_administrateur'] ?? ''));

        return $this->insert([
            'user_code'    => $code,
            'user_name'    => $name !== '' ? $name : null,
            'user_email'   => $email !== '' ? $email : null,
            'connected_at' => Time::now('Europe/Paris')->toDateTimeString(),
        ]) !== false;
    }
}
