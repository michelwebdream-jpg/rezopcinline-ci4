<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class LoginConnections extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('REZO_login_connections')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'user_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'user_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'connected_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_code');
        $this->forge->addKey('connected_at');
        $this->forge->createTable('REZO_login_connections');
    }

    public function down(): void
    {
        $this->forge->dropTable('REZO_login_connections', true);
    }
}
