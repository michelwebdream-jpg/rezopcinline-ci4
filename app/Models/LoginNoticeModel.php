<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginNoticeModel extends Model
{
    protected $table            = 'login_notices';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement  = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['title', 'content', 'sort_order'];
    protected $useTimestamps    = true;
    protected $createdField      = 'created_at';
    protected $updatedField      = 'updated_at';
    protected $validationRules   = [
        'title' => 'required|max_length[255]',
    ];
    protected $validationMessages = [];
    protected $skipValidation    = false;

    /**
     * Liste toutes les annonces pour la page login, triées par sort_order.
     */
    public function getNoticesForLogin(): array
    {
        return $this->orderBy('sort_order', 'ASC')->findAll();
    }

    /**
     * Retourne la durée d'affichage en secondes (config globale).
     */
    public function getDisplayDurationSeconds(): int
    {
        $row = $this->db->table('login_notice_config')->getWhere(['id' => 1])->getRowArray();
        if (!$row || !isset($row['display_duration_seconds'])) {
            return 8;
        }
        $v = (int) $row['display_duration_seconds'];
        return $v >= 1 ? $v : 8;
    }

    /**
     * Met à jour la durée d'affichage.
     */
    public function setDisplayDurationSeconds(int $seconds): bool
    {
        $seconds = max(1, min(300, $seconds));
        return $this->db->table('login_notice_config')->where('id', 1)->update(['display_duration_seconds' => $seconds]);
    }
}
