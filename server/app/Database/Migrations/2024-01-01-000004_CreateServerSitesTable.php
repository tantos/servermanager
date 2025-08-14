<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateServerSitesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'server_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'site_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'document_root' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
            ],
            'server_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'server_alias' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'php_version' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'is_enabled' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'ssl_enabled' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'ssl_cert_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'ssl_key_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('server_id');
        $this->forge->addKey('site_name');
        $this->forge->addKey('is_enabled');
        $this->forge->addForeignKey('server_id', 'servers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('server_sites');
    }

    public function down()
    {
        $this->forge->dropTable('server_sites');
    }
} 