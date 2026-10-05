<?php

namespace App\Models;

use CodeIgniter\Model;

class PetugasModel extends Model
{
   protected function initialize()
   {
      $this->allowedFields = [
         'email',
         'username',
         'password_hash',
         'id_guru',
         'id_tendik',
         'active'
      ];
   }

   protected $table = 'users';

   protected $primaryKey = 'id';

   /**
    * Map old form role integer values to Shield group names.
    */
   private const ROLE_GROUP_MAP = [
      '0' => 'tendik',
      '1' => 'superadmin',
      '2' => 'kepsek',
      '3' => 'admin',
   ];

   /**
    * Get all petugas (staff users) with group info and Tendik / Guru data.
    */
   public function getAllPetugas()
   {
      $db = \Config\Database::connect();

      // Query bersih tanpa join ke tb_kelas
      $users = $this->select('users.*, auth_identities.secret as email, tb_guru.nama_guru, tb_tendik.nama_tendik')
         ->join('auth_identities', 'users.id = auth_identities.user_id AND auth_identities.type = "email_password"', 'left')
         ->join('tb_guru', 'users.id_guru = tb_guru.id_guru', 'left')
         ->join('tb_tendik', 'users.id_tendik = tb_tendik.id_tendik', 'left')
         ->findAll();

      // Enrich with group information
      $groupModel = model(\CodeIgniter\Shield\Models\GroupModel::class);
      $userIds = array_column($users, 'id');
      $groupsByUser = !empty($userIds) ? $groupModel->getGroupsByUserIds($userIds) : [];

      foreach ($users as &$user) {
         $user['groups'] = $groupsByUser[$user['id']] ?? [];

         // SMART FALLBACK: Jika nama_guru & nama_tendik masih kosong, cocokkan otomatis via Username
         if (empty($user['nama_guru']) && empty($user['nama_tendik'])) {
            $cleanName = str_replace(['Tendik_', 'tendik_', 'Guru_', 'guru_'], '', $user['username']);
            $cleanName = trim(str_replace('_', ' ', $cleanName));

            $tendikMatch = $db->table('tb_tendik')
               ->like('nama_tendik', $cleanName, 'both')
               ->get()->getRowArray();

            if ($tendikMatch) {
               $user['nama_tendik'] = $tendikMatch['nama_tendik'];
               $user['id_tendik']   = $tendikMatch['id_tendik'];
               
               // Otomatis simpan ID ke database agar permanen
               $db->table('users')->where('id', $user['id'])->update(['id_tendik' => $tendikMatch['id_tendik']]);
            }
         }
      }

      return $users;
   }

   /**
    * Get a single petugas by ID with group info and Tendik / Guru data.
    */
   public function getPetugasById($id)
   {
      $user = $this->select('users.*, auth_identities.secret as email, tb_guru.nama_guru, tb_tendik.nama_tendik')
         ->join('auth_identities', 'users.id = auth_identities.user_id AND auth_identities.type = "email_password"', 'left')
         ->join('tb_guru', 'users.id_guru = tb_guru.id_guru', 'left')
         ->join('tb_tendik', 'users.id_tendik = tb_tendik.id_tendik', 'left')
         ->where(['users.' . $this->primaryKey => $id])
         ->first();

      if ($user) {
         $groupModel = model(\CodeIgniter\Shield\Models\GroupModel::class);
         $userGroups = $groupModel->getForUser(new \CodeIgniter\Shield\Entities\User(['id' => $user['id']]));
         $user['groups'] = $userGroups;
      }

      return $user;
   }

   /**
    * Save (create or update) a petugas user.
    */
   public function savePetugas($idPetugas, $email, $username, $password, $role, $id_guru = null, $active = 1, $id_tendik = null)
   {
      $users = auth()->getProvider();

      if ($idPetugas) {
         $user = $users->find($idPetugas);
      } else {
         $user = new \CodeIgniter\Shield\Entities\User();
      }

      $user->fill([
         'username' => $username,
         'email'    => $email,
      ]);

      if (!empty($password)) {
         $user->password = $password;
      }

      $user->id_guru   = $id_guru;
      $user->id_tendik = $id_tendik;
      $user->active    = $active;

      if ($role === 'scanner') {
         $role = 'tendik';
      }

      // Map role value to Shield group name
      $targetGroup = self::ROLE_GROUP_MAP[$role] ?? $role;
      $validGroups = array_keys(setting('AuthGroups.groups'));
      if (!in_array($targetGroup, $validGroups, true)) {
         $targetGroup = 'tendik';
      }

      if ($users->save($user)) {
         $savedId   = $idPetugas ?: $users->getInsertID();
         $savedUser = $users->find($savedId);

         // Sync groups: primary role group + 'guru'/'tendik' jika terhubung ID
         $groupsToSync = [$targetGroup];
         if (!empty($id_guru)) {
            $groupsToSync[] = 'guru';
         }
         if (!empty($id_tendik)) {
            $groupsToSync[] = 'tendik';
         }

         $savedUser->syncGroups(...array_unique($groupsToSync));

         return true;
      }

      return false;
   }

   /**
    * Generate CSV object from uploaded file.
    */
   public function generateCSVObject($filePath)
   {
      $array = array();
      $fields = array();
      $txtName = uniqid() . '.txt';
      $i = 0;
      $handle = fopen($filePath, 'r');
      if ($handle) {
         while (($row = fgetcsv($handle)) !== false) {
            if (empty($fields)) {
               $fields = $row;
               $bom = pack('H*', 'EFBBBF');
               $fields[0] = preg_replace("/^$bom/", '', $fields[0]);
               $fields = array_map('trim', $fields);
               continue;
            }
            foreach ($row as $k => $value) {
               $array[$i][$fields[$k]] = $value;
            }
            $i++;
         }
         if (!feof($handle)) {
            return false;
         }
         fclose($handle);
         if (!empty($array)) {
            $txtFile = fopen(FCPATH . 'uploads/tmp/' . $txtName, 'w');

            fwrite($txtFile, json_encode($array));
            fclose($txtFile);

            $obj = new \stdClass();
            $obj->numberOfItems = countItems($array);
            $obj->txtFileName = $txtName;

            if (file_exists($filePath) && !unlink($filePath)) {
               if (function_exists('log_message')) {
                  log_message('error', 'Failed to delete CSV file: ' . $filePath);
               }
            }

            return $obj;
         }
      }
      return false;
   }

   /**
    * Import a single CSV item and create a user.
    */
   public function importCSVItem($txtFileName, $index)
   {
      if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $txtFileName) || strpos($txtFileName, '..') !== false) {
         return null;
      }

      $filePath = FCPATH . 'uploads/tmp/' . $txtFileName;
      if (!file_exists($filePath)) {
         return null;
      }
      $file = fopen($filePath, 'r');
      $content = fread($file, filesize($filePath));
      fclose($file);
      $array = json_decode($content, true);
      if (!empty($array)) {
         $i = 1;
         foreach ($array as $item) {
            if ($i == $index) {
               $data = array();
               $data['username'] = getCSVInputValue($item, 'username');
               $data['email']    = getCSVInputValue($item, 'email');

               if (empty($data['username']) || strlen($data['username']) < 3) {
                  return null;
               }
               if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                  return null;
               }

               $password = getCSVInputValue($item, 'password');
               if (empty($password) || strlen($password) < 6) {
                  return null;
               }

               $roleValue = getCSVInputValue($item, 'role', 'int');
               $idGuru    = getCSVInputValue($item, 'id_guru', 'int');
               $idTendik  = getCSVInputValue($item, 'id_tendik', 'int');

               if (!empty($idGuru)) {
                  $guruModel = new GuruModel();
                  $guru = $guruModel->find($idGuru);
                  if ($guru === null) {
                     return null;
                  }
                  $data['id_guru'] = $idGuru;
               } else {
                  $data['id_guru'] = null;
               }

               $data['id_tendik'] = $idTendik ?: null;
               $data['active']    = 1;

               $users = auth()->getProvider();
               $existing = $users->findByCredentials(['email' => $data['email']]);
               if (!$existing) {
                  $existing = $users->where('username', $data['username'])->first();
               }

               if (!empty($existing)) {
                  return null;
               }

               $user = new \CodeIgniter\Shield\Entities\User([
                  'username' => $data['username'],
                  'email'    => $data['email'],
                  'password' => $password,
               ]);
               $user->id_guru   = $data['id_guru'];
               $user->id_tendik = $data['id_tendik'];
               $user->active    = $data['active'];

               if ($users->save($user)) {
                  $newId = $users->getInsertID();
                  $savedUser = $users->find($newId);

                  $targetGroup = self::ROLE_GROUP_MAP[(string) $roleValue] ?? 'tendik';
                  
                  $groupsToSync = [$targetGroup];
                  if (!empty($data['id_guru'])) {
                     $groupsToSync[] = 'guru';
                  }
                  if (!empty($data['id_tendik'])) {
                     $groupsToSync[] = 'tendik';
                  }
                  $savedUser->syncGroups(...array_unique($groupsToSync));
                  
                  return $data;
               }

               return null;
            }
            $i++;
         }
      }
   }
}