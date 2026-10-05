<?php

namespace App\Controllers\Admin;

use App\Models\TendikModel;
use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

class DataTendik extends BaseController
{
    protected TendikModel $tendikModel;

    protected $tendikValidationRules = [
        'nip' => [
            'rules' => 'required|max_length[30]|min_length[3]',
            'errors' => [
                'required' => 'NIP / NUPTK harus diisi.',
                'min_length' => 'Panjang NIP minimal 3 karakter'
            ]
        ],
        'nama' => [
            'rules' => 'required|min_length[3]',
            'errors' => [
                'required' => 'Nama harus diisi'
            ]
        ],
        'jk'      => ['rules' => 'required', 'errors' => ['required' => 'Jenis kelamin wajib diisi']],
        'no_hp'   => 'required|numeric|max_length[20]|min_length[5]',
        'alamat'  => 'permit_empty',
        'rfid'    => 'permit_empty',
        'jabatan' => 'permit_empty'
    ];

    public function __construct()
    {
        $this->tendikModel = new TendikModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Data Tendik',
            'ctx'   => 'tendik',
        ];

        return view('admin/data/data-tendik', $data);
    }

    public function ambilDataTendik()
    {
        $result = $this->tendikModel->getAllTendik();

        $data = [
            'data'  => $result,
            'empty' => empty($result)
        ];

        return view('admin/data/list-data-tendik', $data);
    }

    public function formTambahTendik()
    {
        $data = [
            'ctx'   => 'tendik',
            'title' => 'Tambah Data Tendik'
        ];

        return view('admin/data/create/create-data-tendik', $data);
    }

    public function saveTendik()
    {
        if (!$this->validate($this->tendikValidationRules)) {
            $data = [
                'ctx'        => 'tendik',
                'title'      => 'Tambah Data Tendik',
                'validation' => $this->validator,
                'oldInput'   => $this->request->getVar()
            ];
            return view('admin/data/create/create-data-tendik', $data);
        }

        $nip         = trim($this->request->getVar('nip') ?? $this->request->getVar('nuptk') ?? '');
        $nama        = trim($this->request->getVar('nama'));
        $jkInput     = $this->request->getVar('jk');
        $jk          = ($jkInput == '1' || strtolower($jkInput) == 'laki-laki') ? 'Laki-laki' : (($jkInput == '2' || strtolower($jkInput) == 'perempuan') ? 'Perempuan' : $jkInput);
        $noHp        = trim($this->request->getVar('no_hp'));
        $alamat      = trim($this->request->getVar('alamat') ?? '');
        $rfid        = $this->request->getVar('rfid');
        $namaJabatan = trim($this->request->getVar('jabatan') ?? $this->request->getVar('nama_jabatan') ?? '');

        $db     = \Config\Database::connect();
        $fields = $db->getFieldNames('tb_tendik');

        $dataTendik = [];
        if (in_array('nip', $fields)) $dataTendik['nip'] = $nip;
        if (in_array('nuptk', $fields)) $dataTendik['nuptk'] = $nip;

        if (in_array('nama_tendik', $fields)) $dataTendik['nama_tendik'] = $nama;
        elseif (in_array('nama_lengkap', $fields)) $dataTendik['nama_lengkap'] = $nama;
        elseif (in_array('nama', $fields)) $dataTendik['nama'] = $nama;

        if (in_array('jenis_kelamin', $fields)) $dataTendik['jenis_kelamin'] = $jk;
        if (in_array('alamat', $fields)) $dataTendik['alamat'] = $alamat;

        if (in_array('no_hp', $fields)) $dataTendik['no_hp'] = $noHp;
        elseif (in_array('hp', $fields)) $dataTendik['hp'] = $noHp;

        if (in_array('unique_code', $fields)) $dataTendik['unique_code'] = sha1($nama . md5($nip . $nama . $noHp)) . substr(sha1($nip . rand(0, 100)), 0, 24);
        if (in_array('rfid_code', $fields)) $dataTendik['rfid_code'] = $rfid;

        if ($db->table('tb_tendik')->insert($dataTendik)) {
            $idTendik = $db->insertID();

            if ($idTendik && !empty($namaJabatan)) {
                $tableJabatan = $db->tableExists('tb_jabatan') ? 'tb_jabatan' : ($db->tableExists('tb_jabatan_tendik') ? 'tb_jabatan_tendik' : 'tb_jabatan');

                if ($db->tableExists($tableJabatan)) {
                    $db->table($tableJabatan)->where('id_tendik', $idTendik)->delete();
                    $db->table($tableJabatan)->insert([
                        'id_tendik'    => $idTendik,
                        'nama_jabatan' => $namaJabatan
                    ]);
                }
            }

            session()->setFlashdata(['msg' => 'Tambah data berhasil', 'error' => false]);
            return redirect()->to('/admin/tendik');
        }

        session()->setFlashdata(['msg' => 'Gagal menambah data', 'error' => true]);
        return redirect()->to('/admin/tendik/create');
    }

    public function bulkPost()
    {
        return $this->bulkPostTendik();
    }

    public function bulkPostTendik()
    {
        $data = [
            'ctx'   => 'tendik',
            'title' => 'Import Data Tendik (Excel / CSV)'
        ];

        return view('admin/data/import-tendik', $data);
    }

    public function importCSVItemPost()
    {
        $file = $this->request->getFile('file_csv') ?? $this->request->getFile('file');

        if (!$file || !$file->isValid()) {
            session()->setFlashdata(['msg' => 'Silakan pilih file Excel / CSV yang valid.', 'error' => true]);
            return redirect()->back();
        }

        $ext      = strtolower($file->getClientExtension());
        $db       = \Config\Database::connect();
        $fields   = $db->getFieldNames('tb_tendik');
        $rowsData = [];

        if (($ext === 'xlsx' || $ext === 'xls') && class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
                $rowsData    = $spreadsheet->getActiveSheet()->toArray();
            } catch (\Exception $e) {
                session()->setFlashdata(['msg' => 'Gagal membaca file Excel: ' . $e->getMessage(), 'error' => true]);
                return redirect()->back();
            }
        } else {
            $handle = fopen($file->getTempName(), 'r');
            if ($handle) {
                while (($row = fgetcsv($handle, 2000, ',')) !== FALSE) {
                    if (count($row) == 1 && strpos($row[0], ';') !== false) {
                        $row = explode(';', $row[0]);
                    }
                    $rowsData[] = array_map('trim', $row);
                }
                fclose($handle);
            }
        }

        if (empty($rowsData)) {
            session()->setFlashdata(['msg' => 'File kosong atau format tidak didukung.', 'error' => true]);
            return redirect()->back();
        }

        $headerMap    = [];
        $headerFound  = false;
        $successCount = 0;
        $skippedCount = 0;

        foreach ($rowsData as $row) {
            $row = array_map(function($val) {
                return trim((string)$val);
            }, $row);

            if (empty(array_filter($row))) {
                continue;
            }

            if (!$headerFound) {
                $lineString = strtolower(implode(' ', $row));
                if (strpos($lineString, 'nama') !== false || strpos($lineString, 'nip') !== false || strpos($lineString, 'nuptk') !== false) {
                    $headerFound = true;
                    foreach ($row as $index => $colName) {
                        $clean = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $colName));
                        if (preg_match('/(nip|nuptk|nik)/i', $clean)) $headerMap[$index] = 'nip';
                        elseif (preg_match('/(nama|tendik|pegawai)/i', $clean)) $headerMap[$index] = 'nama';
                        elseif (preg_match('/(jabatan|tugas|bagian)/i', $clean)) $headerMap[$index] = 'jabatan';
                        elseif (preg_match('/(jk|kelamin)/i', $clean)) $headerMap[$index] = 'jk';
                        elseif (preg_match('/(hp|wa|telepon)/i', $clean)) $headerMap[$index] = 'no_hp';
                        elseif (preg_match('/(alamat)/i', $clean)) $headerMap[$index] = 'alamat';
                    }
                }
                continue;
            }

            $nip = ''; $nama = ''; $jabatan = ''; $jk = ''; $noHp = ''; $alamat = '';

            if (!empty($headerMap)) {
                foreach ($row as $index => $cell) {
                    if (isset($headerMap[$index])) {
                        ${$headerMap[$index]} = $cell;
                    }
                }
            } else {
                $nama    = $row[2] ?? '';
                $jabatan = $row[3] ?? '';
                $nip     = $row[4] ?? '';
                $noHp    = $row[5] ?? '';
                $alamat  = $row[6] ?? '';
            }

            if (empty($nama) || strtolower($nama) == 'nama tendik' || strtolower($nama) == 'nama pegawai') {
                continue;
            }

            $nip    = (!empty($nip) && $nip !== '-') ? $nip : '-';
            $noHp   = !empty($noHp) ? $noHp : '-';
            $alamat = !empty($alamat) ? $alamat : '-';

            $jkLower  = strtolower($jk);
            $jkFormat = in_array($jkLower, ['p', 'perempuan', '2', 'female']) ? 'Perempuan' : 'Laki-laki';

            if ($nip !== '-') {
                $builder = $db->table('tb_tendik');
                if (in_array('nip', $fields) && in_array('nuptk', $fields)) {
                    $builder->groupStart()->where('nip', $nip)->orWhere('nuptk', $nip)->groupEnd();
                } elseif (in_array('nip', $fields)) {
                    $builder->where('nip', $nip);
                } elseif (in_array('nuptk', $fields)) {
                    $builder->where('nuptk', $nip);
                }

                $checkDuplicate = $builder->get()->getRowArray();
                if ($checkDuplicate) {
                    $skippedCount++;
                    continue;
                }
            }

            $dataTendik = [];
            if (in_array('nip', $fields)) $dataTendik['nip'] = $nip;
            if (in_array('nuptk', $fields)) $dataTendik['nuptk'] = $nip;

            if (in_array('nama_tendik', $fields)) $dataTendik['nama_tendik'] = $nama;
            elseif (in_array('nama_lengkap', $fields)) $dataTendik['nama_lengkap'] = $nama;
            elseif (in_array('nama', $fields)) $dataTendik['nama'] = $nama;

            if (in_array('jenis_kelamin', $fields)) $dataTendik['jenis_kelamin'] = $jkFormat;
            if (in_array('alamat', $fields)) $dataTendik['alamat'] = $alamat;

            if (in_array('no_hp', $fields)) $dataTendik['no_hp'] = $noHp;
            elseif (in_array('hp', $fields)) $dataTendik['hp'] = $noHp;

            if (in_array('unique_code', $fields)) $dataTendik['unique_code'] = sha1($nama . md5($nip . $nama . $noHp)) . substr(sha1($nip . rand(0, 100)), 0, 24);
            if (in_array('rfid_code', $fields)) $dataTendik['rfid_code'] = null;

            if ($db->table('tb_tendik')->insert($dataTendik)) {
                $idTendik = $db->insertID();

                $tableJabatan = $db->tableExists('tb_jabatan') ? 'tb_jabatan' : ($db->tableExists('tb_jabatan_tendik') ? 'tb_jabatan_tendik' : 'tb_jabatan');

                if ($idTendik && !empty($jabatan) && $db->tableExists($tableJabatan)) {
                    $db->table($tableJabatan)->insert([
                        'id_tendik'    => $idTendik,
                        'nama_jabatan' => $jabatan
                    ]);
                }

                $successCount++;
            }
        }

        $msg = "Berhasil mengimpor $successCount data Tendik!";
        if ($skippedCount > 0) {
            $msg .= " ($skippedCount data dilewati karena NIP/NUPTK sudah terdaftar)";
        }

        session()->setFlashdata(['msg' => $msg, 'error' => false]);
        return redirect()->to('/admin/tendik');
    }

    public function formEditTendik($id)
    {
        $tendik = $this->tendikModel->getTendikById($id);

        if (empty($tendik)) {
            throw new PageNotFoundException('Data tendik dengan id ' . $id . ' tidak ditemukan');
        }

        $db = \Config\Database::connect();
        $tableJabatan = $db->tableExists('tb_jabatan') ? 'tb_jabatan' : ($db->tableExists('tb_jabatan_tendik') ? 'tb_jabatan_tendik' : 'tb_jabatan');

        if (!isset($tendik['nama_jabatan']) && $db->tableExists($tableJabatan)) {
            $jabatanData = $db->table($tableJabatan)->where('id_tendik', $id)->get()->getRowArray();
            $tendik['nama_jabatan'] = $jabatanData['nama_jabatan'] ?? '';
        }

        $data = [
            'data'  => $tendik,
            'ctx'   => 'tendik',
            'title' => 'Edit Data Tendik',
        ];

        return view('admin/data/edit/edit-data-tendik', $data);
    }

    public function updateTendik()
    {
        $idTendik    = $this->request->getVar('id');
        $nip         = trim($this->request->getVar('nip') ?? $this->request->getVar('nuptk') ?? '');
        $nama        = trim($this->request->getVar('nama'));
        $jkInput     = $this->request->getVar('jk');
        $jk          = ($jkInput == '1' || strtolower($jkInput) == 'laki-laki') ? 'Laki-laki' : (($jkInput == '2' || strtolower($jkInput) == 'perempuan') ? 'Perempuan' : $jkInput);
        $noHp        = trim($this->request->getVar('no_hp'));
        $alamat      = trim($this->request->getVar('alamat') ?? '');
        $rfid        = $this->request->getVar('rfid');
        $namaJabatan = trim($this->request->getVar('jabatan') ?? $this->request->getVar('nama_jabatan') ?? '');

        if (!$this->validate($this->tendikValidationRules)) {
            $tendik = $this->tendikModel->getTendikById($idTendik);

            $data = [
                'data'       => $tendik,
                'ctx'        => 'tendik',
                'title'      => 'Edit Data Tendik',
                'validation' => $this->validator,
                'oldInput'   => $this->request->getVar()
            ];
            return view('admin/data/edit/edit-data-tendik', $data);
        }

        $db     = \Config\Database::connect();
        $fields = $db->getFieldNames('tb_tendik');

        $updateData = [];
        if (in_array('nip', $fields)) $updateData['nip'] = $nip;
        if (in_array('nuptk', $fields)) $updateData['nuptk'] = $nip;

        if (in_array('nama_tendik', $fields)) $updateData['nama_tendik'] = $nama;
        elseif (in_array('nama_lengkap', $fields)) $updateData['nama_lengkap'] = $nama;
        elseif (in_array('nama', $fields)) $updateData['nama'] = $nama;

        if (in_array('jenis_kelamin', $fields)) $updateData['jenis_kelamin'] = $jk;
        if (in_array('alamat', $fields)) $updateData['alamat'] = $alamat;

        if (in_array('no_hp', $fields)) $updateData['no_hp'] = $noHp;
        elseif (in_array('hp', $fields)) $updateData['hp'] = $noHp;

        if (in_array('rfid_code', $fields)) $updateData['rfid_code'] = $rfid;

        $db->table('tb_tendik')->where('id_tendik', $idTendik)->update($updateData);

        $tableJabatan = $db->tableExists('tb_jabatan') ? 'tb_jabatan' : ($db->tableExists('tb_jabatan_tendik') ? 'tb_jabatan_tendik' : 'tb_jabatan');

        if ($idTendik && $db->tableExists($tableJabatan)) {
            $existing = $db->table($tableJabatan)->where('id_tendik', $idTendik)->get()->getRowArray();
            if ($existing) {
                $db->table($tableJabatan)->where('id_tendik', $idTendik)->update(['nama_jabatan' => $namaJabatan]);
            } else {
                if (!empty($namaJabatan)) {
                    $db->table($tableJabatan)->insert([
                        'id_tendik'    => $idTendik,
                        'nama_jabatan' => $namaJabatan
                    ]);
                }
            }
        }

        session()->setFlashdata(['msg' => 'Edit data berhasil', 'error' => false]);
        return redirect()->to('/admin/tendik');
    }

    public function delete($id)
    {
        $db = \Config\Database::connect();

        $db->query('SET FOREIGN_KEY_CHECKS = 0');

        $tableJabatan = $db->tableExists('tb_jabatan') ? 'tb_jabatan' : ($db->tableExists('tb_jabatan_tendik') ? 'tb_jabatan_tendik' : 'tb_jabatan');

        if ($db->tableExists($tableJabatan)) {
            $db->table($tableJabatan)->where('id_tendik', $id)->delete();
        }
        if ($db->tableExists('users')) {
            $db->table('users')->where('id_tendik', $id)->delete();
        }

        $result = $this->tendikModel->delete($id);

        $db->query('SET FOREIGN_KEY_CHECKS = 1');

        if ($result) {
            session()->setFlashdata(['msg' => 'Data berhasil dihapus', 'error' => false]);
        } else {
            session()->setFlashdata(['msg' => 'Gagal menghapus data', 'error' => true]);
        }
        return redirect()->to('/admin/tendik');
    }

    public function deleteAllTendik()
    {
        $db = \Config\Database::connect();
        $tableJabatan = $db->tableExists('tb_jabatan') ? 'tb_jabatan' : ($db->tableExists('tb_jabatan_tendik') ? 'tb_jabatan_tendik' : 'tb_jabatan');

        $db->query('SET FOREIGN_KEY_CHECKS = 0');

        if ($db->tableExists($tableJabatan)) {
            $db->table($tableJabatan)->truncate();
        }
        $db->table('tb_tendik')->truncate();

        $db->query('SET FOREIGN_KEY_CHECKS = 1');

        session()->setFlashdata(['msg' => 'Semua data Tendik berhasil dikosongkan!', 'error' => false]);
        return redirect()->to('/admin/tendik');
    }
}