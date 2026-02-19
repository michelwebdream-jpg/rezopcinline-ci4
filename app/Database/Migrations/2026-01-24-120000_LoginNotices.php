<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class LoginNotices extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'content' => [
                'type' => 'MEDIUMTEXT',
                'null' => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('sort_order');
        $this->forge->createTable('login_notices');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'display_duration_seconds' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 8,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('login_notice_config');

        $this->db->table('login_notice_config')->insert(['id' => 1, 'display_duration_seconds' => 8]);
    }

    public function down(): void
    {
        $this->forge->dropTable('login_notices', true);
        $this->forge->dropTable('login_notice_config', true);
    }
}
