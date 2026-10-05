<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PetugasModel;
use App\Models\GuruModel;
use App\Models\TendikModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use App\Libraries\enums\UserRole;

class DataPetugas extends BaseController
{
   protected PetugasModel $petugasModel;
   protected GuruModel $guruModel;
   protected TendikModel $tendikModel;
   protected \App\Models\UploadModel $uploadModel;

   protected $petugasValidationRules = [
      'email' => [
         'rules' => 'required',
         'errors' => [
            'required' => 'Email harus diisi.',
            'is_unique' => 'Email ini telah terdaftar.'
         ]
      ],
      'username' => [
         'rules' => 'required|min_length[6]',
         'errors' => [
            'required' => 'Username harus diisi',
            'is_unique' => 'Username ini telah terdaftar.'
         ]
      ],
      'password' => [
         'rules' => 'permit_empty|min_length[6]',
      ],
      'role' => [
         'rules' => 'required',
         'errors' => [
            'required' => 'Role wajib diisi'
         ]
      ]
   ];

   public function __construct()
   {
      $this->petugasModel = new PetugasModel();
      $this->guruModel    = new GuruModel();
      $this->tendikModel  = new TendikModel();
      $this->uploadModel  = new \App\Models\UploadModel();
   }

   public function index()
   {
      if (!is_superadmin()) {
         return redirect()->to('admin');
      }

      $data = [
         'title' => 'Data Petugas',
         'ctx'   => 'petugas'
      ];

      return view('admin/petugas/data-petugas', $data);
   }

   public function ambilDataPetugas()
   {
      $petugas = $this->petugasModel->getAllPetugas();

      $data = [
         'data'  => $petugas,
         'empty' => empty($petugas)
      ];

      return view('admin/petugas/list-data-petugas', $data);
   }

   public function registerPetugas()
   {
      if (!is_superadmin()) {
         return redirect()->to('admin');
      }

      $data = [
         'title'  => 'Register Petugas',
         'ctx'    => 'petugas',
         'guru'   => $this->guruModel->getAllGuru(),
         'tendik' => $this->tendikModel->getAllTendik(),
         'roles'  => UserRole::ALL_ROLES
      ];

      return view('admin/petugas/register', $data);
   }

   public function registerPetugasPost()
   {
      if (!is_superadmin()) {
         return redirect()->to('admin');
      }

      $this->petugasValidationRules['email']['rules'] .= '|is_unique[auth_identities.secret]';
      $this->petugasValidationRules['username']['rules'] .= '|is_unique[users.username]';
      $this->petugasValidationRules['password']['rules'] = 'required|min_length[6]';

      if (!$this->validate($this->petugasValidationRules)) {
         return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
      }

      $email     = $this->request->getVar('email');
      $username  = $this->request->getVar('username');
      $password  = $this->request->getVar('password');
      $role      = $this->request->getVar('role');
      $id_guru   = $this->request->getVar('id_guru') ?: null;
      $id_tendik = $this->request->getVar('id_tendik') ?: null;

      if ($role === 'scanner') {
         $role = 'tendik';
      }

      // Validasi penyesuaian Role otomatis berdasarkan identitas pegawainya
      if (!empty($id_tendik) && empty($id_guru) && $role === 'guru') {
         $role = 'tendik';
      } elseif (!empty($id_guru) && empty($id_tendik) && $role === 'tendik') {
         $role = 'guru';
      }

      $result = $this->petugasModel->savePetugas(null, $email, $username, $password, $role, $id_guru, 1, $id_tendik);

      if ($result) {
         session()->setFlashdata(['msg' => 'Registrasi petugas berhasil', 'error' => false]);
         return redirect()->to('/admin/petugas');
      }

      session()->setFlashdata(['msg' => 'Gagal registrasi petugas', 'error' => true]);
      return redirect()->back()->withInput();
   }

   public function formEditPetugas($id)
   {
      if (!is_superadmin()) {
         return redirect()->to('admin');
      }

      $petugas = $this->petugasModel->getPetugasById($id);

      if (empty($petugas)) {
         throw new PageNotFoundException('Data petugas dengan id ' . $id . ' tidak ditemukan');
      }

      $data = [
         'data'   => $petugas,
         'ctx'    => 'petugas',
         'title'  => 'Edit Data Petugas',
         'guru'   => $this->guruModel->getAllGuru(),
         'tendik' => $this->tendikModel->getAllTendik(),
         'roles'  => UserRole::ALL_ROLES
      ];

      return view('admin/petugas/edit-data-petugas', $data);
   }

   public function updatePetugas()
   {
      if (!is_superadmin()) {
         return redirect()->to('admin');
      }

      $idPetugas   = $this->request->getVar('id');
      $petugasLama = $this->petugasModel->getPetugasById($idPetugas);

      if ($petugasLama['username'] != $this->request->getVar('username')) {
         $this->petugasValidationRules['username']['rules'] = 'required|is_unique[users.username]';
      }

      if ($petugasLama['email'] != $this->request->getVar('email')) {
         $this->petugasValidationRules['email']['rules'] = 'required|is_unique[auth_identities.secret]';
      }

      if (!$this->validate($this->petugasValidationRules)) {
         $data = [
            'data'       => $this->petugasModel->getPetugasById($idPetugas),
            'ctx'        => 'petugas',
            'title'      => 'Edit Data Petugas',
            'validation' => $this->validator,
            'oldInput'   => $this->request->getVar(),
            'guru'       => $this->guruModel->getAllGuru(),
            'tendik'     => $this->tendikModel->getAllTendik(),
            'roles'      => UserRole::ALL_ROLES
         ];
         return view('admin/petugas/edit-data-petugas', $data);
      }

      $password  = $this->request->getVar('password') ?? false;
      $email     = $this->request->getVar('email');
      $username  = $this->request->getVar('username');
      $role      = $this->request->getVar('role');
      $id_guru   = $this->request->getVar('id_guru') ?: null;
      $id_tendik = $this->request->getVar('id_tendik') ?: null;

      if ($role === 'scanner') {
         $role = 'tendik';
      }

      // Validasi penyesuaian Role otomatis berdasarkan identitas pegawainya
      if (!empty($id_tendik) && empty($id_guru) && $role === 'guru') {
         $role = 'tendik';
      } elseif (!empty($id_guru) && empty($id_tendik) && $role === 'tendik') {
         $role = 'guru';
      }

      $result = $this->petugasModel->savePetugas(
         $idPetugas,
         $email,
         $username,
         $password,
         $role,
         $id_guru,
         $petugasLama['active'],
         $id_tendik
      );

      if ($result) {
         session()->setFlashdata(['msg' => 'Edit data berhasil', 'error' => false]);
         return redirect()->to('/admin/petugas');
      }

      session()->setFlashdata(['msg' => 'Gagal mengubah data', 'error' => true]);
      return redirect()->to('/admin/petugas/edit/' . $idPetugas);
   }

   public function delete($id)
   {
      if (!is_superadmin()) {
         return redirect()->to('admin');
      }

      $result = $this->petugasModel->delete($id);

      if ($result) {
         session()->setFlashdata(['msg' => 'Data berhasil dihapus', 'error' => false]);
         return redirect()->to('/admin/petugas');
      }

      session()->setFlashdata(['msg' => 'Gagal menghapus data', 'error' => true]);
      return redirect()->to('/admin/petugas');
   }

   public function toggleActivation($id)
   {
      if (!is_superadmin()) {
         return redirect()->to('admin');
      }

      $petugas = $this->petugasModel->getPetugasById($id);
      if (empty($petugas)) {
         throw new PageNotFoundException('Data petugas dengan id ' . $id . ' tidak ditemukan');
      }

      $newStatus = ($petugas['active'] ?? 0) == 1 ? 0 : 1;
      $this->petugasModel->update($id, ['active' => $newStatus]);

      session()->setFlashdata(['msg' => 'Status akun berhasil diubah', 'error' => false]);
      return redirect()->to('/admin/petugas');
   }

   public function bulkPost()
   {
      if (!is_superadmin()) {
         return redirect()->to('admin');
      }

      $data = [
         'title'  => 'Import Petugas',
         'ctx'    => 'petugas',
         'guru'   => $this->guruModel->getAllGuru(),
         'tendik' => $this->tendikModel->getAllTendik(),
      ];

      return view('admin/petugas/import-petugas', $data);
   }

   public function generateCSVObjectPost()
   {
      if (!is_superadmin()) {
         return redirect()->to('admin');
      }

      $files = glob(FCPATH . 'uploads/tmp/*.txt');
      if (!empty($files)) {
         foreach ($files as $item) {
            if (file_exists($item) && !unlink($item)) {
               log_message('error', 'Failed to delete temporary file: {file}', ['file' => $item]);
            }
         }
      }
      $file = $this->uploadModel->uploadCSVFile('file');
      if (!empty($file) && !empty($file['path'])) {
         $obj = $this->petugasModel->generateCSVObject($file['path']);
         if (!empty($obj)) {
            $data = [
               'result' => 1,
               'numberOfItems' => $obj->numberOfItems,
               'txtFileName' => $obj->txtFileName,
            ];
            return $this->response->setJSON($data);
         }
      }
      return $this->response->setJSON(['result' => 0]);
   }

   public function importCSVItemPost()
   {
      if (!is_superadmin()) {
         return redirect()->to('admin');
      }

      $txtFileName = inputPost('txtFileName');
      $index = inputPost('index');

      if (!is_numeric($index) || (int) $index < 1) {
         return $this->response->setJSON(['result' => 0, 'index' => $index, 'message' => 'Invalid index']);
      }
      $index = (int) $index;

      try {
         $petugas = $this->petugasModel->importCSVItem($txtFileName, $index);
         if (!empty($petugas)) {
            return $this->response->setJSON(['result' => 1, 'petugas' => $petugas, 'index' => $index]);
         } else {
            return $this->response->setJSON(['result' => 0, 'index' => $index, 'message' => 'Duplicate or invalid data']);
         }
      } catch (\Exception $e) {
         return $this->response->setJSON(['result' => 0, 'index' => $index, 'message' => 'Error: ' . $e->getMessage()]);
      }
   }

   public function downloadCSVFilePost()
   {
      if (!is_superadmin()) {
         return redirect()->to('admin');
      }

      $file = FCPATH . 'assets/file/csv_petugas_template.csv';
      if (file_exists($file)) {
         return $this->response->download($file, null);
      }
      return redirect()->back()->with('error', 'Template file not found.');
   }
}