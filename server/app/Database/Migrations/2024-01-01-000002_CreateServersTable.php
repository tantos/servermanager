<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateServersTable extends Migration
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
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'hostname' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45, // IPv6 compatible
            ],
            'port' => [
                'type' => 'INT',
                'constraint' => 5,
                'default' => 6969,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'os_info' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['online', 'offline', 'maintenance'],
                'default' => 'offline',
            ],
            'last_seen' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->addKey('hostname');
        $this->forge->addKey('ip_address');
        $this->forge->addKey('status');
        $this->forge->createTable('servers');
    }

    public function down()
    {
        $this->forge->dropTable('servers');
    }
} 