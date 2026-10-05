<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds the staff tables used by the current Guru/Tendik application.
 * These tables were present in the SQL dump but were missing from the
 * migration chain, which made a clean migration fail on the admin dashboard.
 */
class CreateTendikAttendanceSchema extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('tb_tendik')) {
            $this->forge->addField([
                'id_tendik' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => false,
                    'auto_increment' => true,
                ],
                'nip' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'null'       => false,
                ],
                'nama_tendik' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'jenis_kelamin' => [
                    'type'       => 'ENUM',
                    'constraint' => ['Laki-laki', 'Perempuan'],
                    'null'       => false,
                ],
                'alamat' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'no_hp' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 32,
                    'null'       => false,
                ],
                'unique_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                    'null'       => false,
                ],
                'rfid_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
            ]);
            $this->forge->addKey('id_tendik', true);
            $this->forge->addUniqueKey('unique_code');
            $this->forge->addKey('rfid_code');
            $this->forge->createTable('tb_tendik');
        }

        if (!$this->db->tableExists('tb_presensi_tendik')) {
            $this->forge->addField([
                'id_presensi' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => false,
                    'auto_increment' => true,
                ],
                'id_tendik' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => false,
                    'null'       => false,
                ],
                'tanggal' => [
                    'type' => 'DATE',
                    'null' => false,
                ],
                'jam_masuk' => [
                    'type' => 'TIME',
                    'null' => true,
                ],
                'jam_keluar' => [
                    'type' => 'TIME',
                    'null' => true,
                ],
                'id_kehadiran' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => false,
                    'null'       => false,
                ],
                'menit_keterlambatan' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'keterangan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'default'    => '',
                ],
                'foto_masuk' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'foto_keluar' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'latitude' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'longitude' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
            ]);
            $this->forge->addKey('id_presensi', true);
            $this->forge->addUniqueKey(['id_tendik', 'tanggal'], 'uq_presensi_tendik_tanggal');
            $this->forge->addKey('id_kehadiran');
            $this->forge->addForeignKey(
                'id_tendik',
                'tb_tendik',
                'id_tendik',
                'CASCADE',
                'CASCADE',
                'fk_presensi_tendik_tendik'
            );
            $this->forge->addForeignKey(
                'id_kehadiran',
                'tb_kehadiran',
                'id_kehadiran',
                'CASCADE',
                'CASCADE',
                'fk_presensi_tendik_kehadiran'
            );
            $this->forge->createTable('tb_presensi_tendik');
        }

        if (!$this->db->tableExists('tb_jabatan')) {
            $this->forge->addField([
                'id_jabatan' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => false,
                    'auto_increment' => true,
                ],
                'id_tendik' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => false,
                    'null'       => false,
                ],
                'nama_jabatan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => false,
                ],
            ]);
            $this->forge->addKey('id_jabatan', true);
            $this->forge->addKey('id_tendik');
            $this->forge->addForeignKey(
                'id_tendik',
                'tb_tendik',
                'id_tendik',
                'CASCADE',
                'CASCADE',
                'fk_jabatan_tendik'
            );
            $this->forge->createTable('tb_jabatan');
        }

        if (!$this->db->tableExists('tb_mapel')) {
            $this->forge->addField([
                'id_mapel' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => false,
                    'auto_increment' => true,
                ],
                'id_guru' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => false,
                    'null'       => false,
                ],
                'nama_mapel' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => false,
                ],
            ]);
            $this->forge->addKey('id_mapel', true);
            $this->forge->addKey('id_guru');
            $this->forge->addForeignKey(
                'id_guru',
                'tb_guru',
                'id_guru',
                'CASCADE',
                'CASCADE',
                'fk_mapel_guru'
            );
            $this->forge->createTable('tb_mapel');
        }

        if ($this->db->tableExists('users') && !$this->db->fieldExists('id_tendik', 'users')) {
            $this->forge->addColumn('users', [
                'id_tendik' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => false,
                    'null'       => true,
                ],
            ]);
        }

        if (
            $this->db->tableExists('users')
            && $this->db->fieldExists('id_tendik', 'users')
            && !$this->constraintExists('users', 'fk_users_id_tendik')
        ) {
            $this->db->query(
                'ALTER TABLE `users` ADD CONSTRAINT `fk_users_id_tendik` '
                . 'FOREIGN KEY (`id_tendik`) REFERENCES `tb_tendik` (`id_tendik`) '
                . 'ON UPDATE CASCADE ON DELETE SET NULL'
            );
        }

        if ($this->db->tableExists('tb_perizinan') && !$this->db->fieldExists('id_tendik', 'tb_perizinan')) {
            $this->forge->addColumn('tb_perizinan', [
                'id_tendik' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => false,
                    'null'       => true,
                ],
            ]);
        }

        if (
            $this->db->tableExists('tb_perizinan')
            && $this->db->fieldExists('id_tendik', 'tb_perizinan')
            && !$this->constraintExists('tb_perizinan', 'fk_perizinan_tendik')
        ) {
            $this->db->query(
                'ALTER TABLE `tb_perizinan` ADD CONSTRAINT `fk_perizinan_tendik` '
                . 'FOREIGN KEY (`id_tendik`) REFERENCES `tb_tendik` (`id_tendik`) '
                . 'ON UPDATE CASCADE ON DELETE SET NULL'
            );
        }
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

        foreach ([
            'users'        => 'id_tendik',
            'tb_perizinan' => 'id_tendik',
        ] as $table => $field) {
            if ($this->db->tableExists($table)) {
                $constraint = $table === 'users' ? 'fk_users_id_tendik' : 'fk_perizinan_tendik';
                if ($this->constraintExists($table, $constraint)) {
                    $this->db->query("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint}`");
                }
                if ($this->db->fieldExists($field, $table)) {
                    $this->forge->dropColumn($table, $field);
                }
            }
        }

        foreach (['tb_presensi_tendik', 'tb_jabatan', 'tb_tendik', 'tb_mapel'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
    }

    private function constraintExists(string $table, string $constraint): bool
    {
        return $this->db->query(
            'SELECT 1 FROM information_schema.TABLE_CONSTRAINTS '
            . 'WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
            [$this->db->getDatabase(), $table, $constraint]
        )->getNumRows() > 0;
    }
}
