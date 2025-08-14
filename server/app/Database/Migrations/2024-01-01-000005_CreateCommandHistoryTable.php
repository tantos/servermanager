<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCommandHistoryTable extends Migration
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
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'command' => [
                'type' => 'TEXT',
            ],
            'output' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'error' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'exit_code' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'execution_time' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['success', 'failed', 'timeout'],
                'default' => 'success',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('server_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('server_id', 'servers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('command_history');
    }

    public function down()
    {
        $this->forge->dropTable('command_history');
    }
} 