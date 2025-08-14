<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateServerKeysTable extends Migration
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
            'key_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'public_key' => [
                'type' => 'TEXT',
            ],
            'key_type' => [
                'type' => 'ENUM',
                'constraint' => ['rsa', 'ed25519'],
                'default' => 'rsa',
            ],
            'key_size' => [
                'type' => 'INT',
                'constraint' => 5,
                'default' => 2048,
            ],
            'fingerprint' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
            ],
            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true,
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
        $this->forge->addKey('fingerprint');
        $this->forge->addForeignKey('server_id', 'servers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('server_keys');
    }

    public function down()
    {
        $this->forge->dropTable('server_keys');
    }
} 