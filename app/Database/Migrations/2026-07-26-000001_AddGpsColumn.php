<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGpsColumn extends Migration
{
    public function up()
    {
        $fields = [

            'latitude' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'copyright',
            ],

            'longitude' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'latitude',
            ],

            'radius' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 100,
                'after'      => 'longitude',
            ],

        ];

        $this->forge->addColumn('general_settings', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('general_settings', [
            'latitude',
            'longitude',
            'radius'
        ]);
    }
}