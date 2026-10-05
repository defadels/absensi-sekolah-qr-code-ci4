<?php

namespace App\Controllers\Admin;

use App\Models\GuruModel;
use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

class DataGuru extends BaseController
{
    protected GuruModel $guruModel;

    protected $guruValidationRules = [
        'nuptk' => [
            'rules' => 'required|max_length[30]|min_length[3]',
            'errors' => [
                'required' => 'NUPTK/NIP harus diisi.',
                'min_length' => 'Panjang NUPTK minimal 3 karakter'
            ]
        ],
        'nama' => [
            'rules' => 'required|min_length[3]',
            'errors' => [
                'required' => 'Nama harus diisi'
            ]
        ],
        'jk'     => ['rules' => 'required', 'errors' => ['required' => 'Jenis kelamin wajib diisi']],
        'no_hp'  => 'required|numeric|max_length[20]|min_length[5]',
        'alamat' => 'permit_empty',
        'rfid'   => 'permit_empty',
        'mapel'  => 'permit_empty'
    ];

    public function __construct()
    {
        $this->guruModel = new GuruModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Data Guru',
            'ctx'   => 'guru',
        ];

        return view('admin/data/data-guru', $data);
    }

    public function ambilDataGuru()
    {
        $result = $this->guruModel->getAllGuru();

        $data = [
            'data'  => $result,
            'empty' => empty($result)
        ];

        return view('admin/data/list-data-guru', $data);
    }

    public function formTambahGuru()
    {
        $data = [
            'ctx'   => 'guru',
            'title' => 'Tambah Data Guru'
        ];

        return view('admin/data/create/create-data-guru', $data);
    }

    public function saveGuru()
    {
        if (!$this->validate($this->guruValidationRules)) {
            $data = [
                'ctx'        => 'guru',
                'title'      => 'Tambah Data Guru',
                'validation' => $this->validator,
                'oldInput'   => $this->request->getVar()
            ];
            return view('admin/data/create/create-data-guru', $data);
        }

        $nuptk     = trim($this->request->getVar('nuptk'));
        $nama      = trim($this->request->getVar('nama'));
        $jkInput   = $this->request->getVar('jk');
        $jk        = ($jkInput == '1' || strtolower($jkInput) == 'laki-laki') ? 'Laki-laki' : (($jkInput == '2' || strtolower($jkInput) == 'perempuan') ? 'Perempuan' : $jkInput);
        $noHp      = trim($this->request->getVar('no_hp'));
        $alamat    = trim($this->request->getVar('alamat'));
        $rfid      = $this->request->getVar('rfid');
        $namaMapel = trim($this->request->getVar('mapel') ?? $this->request->getVar('nama_mapel') ?? $this->request->getVar('mata_pelajaran') ?? '');

        $db = \Config\Database::connect();

        $dataGuru = [
            'nuptk'         => $nuptk,
            'nama_guru'     => $nama,
            'jenis_kelamin' => $jk,
            'alamat'        => $alamat,
            'no_hp'         => $noHp,
            'unique_code'   => sha1($nama . md5($nuptk . $nama . $noHp)) . substr(sha1($nuptk . rand(0, 100)), 0, 24),
            'rfid_code'     => $rfid
        ];

        if ($db->table('tb_guru')->insert($dataGuru)) {
            $idGuru = $db->insertID();

            if ($idGuru && !empty($namaMapel)) {
                $db->table('tb_mapel')->where('id_guru', $idGuru)->delete();
                $db->table('tb_mapel')->insert([
                    'id_guru'    => $idGuru,
                    'nama_mapel' => $namaMapel
                ]);
            }

            session()->setFlashdata(['msg' => 'Tambah data berhasil', 'error' => false]);
            return redirect()->to('/admin/guru');
        }

        session()->setFlashdata(['msg' => 'Gagal menambah data', 'error' => true]);
        return redirect()->to('/admin/guru/create');
    }

    public function bulkPost()
    {
        $data = [
            'ctx'   => 'guru',
            'title' => 'Import Data Guru (Excel / CSV)'
        ];

        return view('admin/data/import-guru', $data);
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
                if (strpos($lineString, 'nama') !== false || strpos($lineString, 'nuptk') !== false) {
                    $headerFound = true;
                    foreach ($row as $index => $colName) {
                        $clean = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $colName));
                        if (preg_match('/(nuptk|nip)/i', $clean)) $headerMap[$index] = 'nuptk';
                        elseif (preg_match('/(nama)/i', $clean)) $headerMap[$index] = 'nama';
                        elseif (preg_match('/(mapel|pelajaran)/i', $clean)) $headerMap[$index] = 'mapel';
                        elseif (preg_match('/(jk|kelamin)/i', $clean)) $headerMap[$index] = 'jk';
                        elseif (preg_match('/(hp|wa|telepon)/i', $clean)) $headerMap[$index] = 'no_hp';
                        elseif (preg_match('/(alamat)/i', $clean)) $headerMap[$index] = 'alamat';
                    }
                }
                continue;
            }

            $nuptk = ''; $nama = ''; $mapel = ''; $jk = ''; $noHp = ''; $alamat = '';

            if (!empty($headerMap)) {
                foreach ($row as $index => $cell) {
                    if (isset($headerMap[$index])) {
                        ${$headerMap[$index]} = $cell;
                    }
                }
            } else {
                $nama   = $row[2] ?? '';
                $mapel  = $row[3] ?? '';
                $nuptk  = $row[4] ?? '';
                $noHp   = $row[5] ?? '';
                $alamat = $row[6] ?? '';
            }

            if (empty($nama) || strtolower($nama) == 'nama guru') {
                continue;
            }

            $nuptk  = (!empty($nuptk) && $nuptk !== '-') ? $nuptk : '-';
            $noHp   = !empty($noHp) ? $noHp : '-';
            $alamat = !empty($alamat) ? $alamat : '-';

            $jkLower  = strtolower($jk);
            $jkFormat = in_array($jkLower, ['p', 'perempuan', '2', 'female']) ? 'Perempuan' : 'Laki-laki';

            if ($nuptk !== '-') {
                $checkDuplicate = $db->table('tb_guru')->where('nuptk', $nuptk)->get()->getRowArray();
                if ($checkDuplicate) {
                    $skippedCount++;
                    continue;
                }
            }

            $dataGuru = [
                'nuptk'         => $nuptk,
                'nama_guru'     => $nama,
                'jenis_kelamin' => $jkFormat,
                'alamat'        => $alamat,
                'no_hp'         => $noHp,
                'unique_code'   => sha1($nama . md5($nuptk . $nama . $noHp)) . substr(sha1($nuptk . rand(0, 100)), 0, 24),
                'rfid_code'     => null
            ];

            if ($db->table('tb_guru')->insert($dataGuru)) {
                $idGuru = $db->insertID();

                if ($idGuru && !empty($mapel) && $db->tableExists('tb_mapel')) {
                    $db->table('tb_mapel')->insert([
                        'id_guru'    => $idGuru,
                        'nama_mapel' => $mapel
                    ]);
                }

                $successCount++;
            }
        }

        $msg = "Berhasil mengimpor $successCount data Guru!";
        if ($skippedCount > 0) {
            $msg .= " ($skippedCount data dilewati karena NUPTK sudah terdaftar)";
        }

        session()->setFlashdata(['msg' => $msg, 'error' => false]);
        return redirect()->to('/admin/guru');
    }

    public function formEditGuru($id)
    {
        $guru = $this->guruModel->getGuruById($id);

        if (empty($guru)) {
            throw new PageNotFoundException('Data guru dengan id ' . $id . ' tidak ditemukan');
        }

        $db = \Config\Database::connect();
        if (!isset($guru['nama_mapel']) && $db->tableExists('tb_mapel')) {
            $mapelData = $db->table('tb_mapel')->where('id_guru', $id)->get()->getRowArray();
            $guru['nama_mapel'] = $mapelData['nama_mapel'] ?? '';
        }

        $data = [
            'data'  => $guru,
            'ctx'   => 'guru',
            'title' => 'Edit Data Guru',
        ];

        return view('admin/data/edit/edit-data-guru', $data);
    }

    public function updateGuru()
    {
        $idGuru    = $this->request->getVar('id');
        $nuptk     = trim($this->request->getVar('nuptk'));
        $nama      = trim($this->request->getVar('nama'));
        $jkInput   = $this->request->getVar('jk');
        $jk        = ($jkInput == '1' || strtolower($jkInput) == 'laki-laki') ? 'Laki-laki' : (($jkInput == '2' || strtolower($jkInput) == 'perempuan') ? 'Perempuan' : $jkInput);
        $noHp      = trim($this->request->getVar('no_hp'));
        $alamat    = trim($this->request->getVar('alamat'));
        $rfid      = $this->request->getVar('rfid');
        $namaMapel = trim($this->request->getVar('mapel') ?? $this->request->getVar('nama_mapel') ?? $this->request->getVar('mata_pelajaran') ?? '');

        if (!$this->validate($this->guruValidationRules)) {
            $guru = $this->guruModel->getGuruById($idGuru);

            $data = [
                'data'       => $guru,
                'ctx'        => 'guru',
                'title'      => 'Edit Data Guru',
                'validation' => $this->validator,
                'oldInput'   => $this->request->getVar()
            ];
            return view('admin/data/edit/edit-data-guru', $data);
        }

        $db = \Config\Database::connect();

        $updateData = [
            'nuptk'         => $nuptk,
            'nama_guru'     => $nama,
            'jenis_kelamin' => $jk,
            'alamat'        => $alamat,
            'no_hp'         => $noHp,
            'rfid_code'     => $rfid,
        ];

        $db->table('tb_guru')->where('id_guru', $idGuru)->update($updateData);

        if ($idGuru && $db->tableExists('tb_mapel')) {
            $existing = $db->table('tb_mapel')->where('id_guru', $idGuru)->get()->getRowArray();
            if ($existing) {
                $db->table('tb_mapel')->where('id_guru', $idGuru)->update(['nama_mapel' => $namaMapel]);
            } else {
                if (!empty($namaMapel)) {
                    $db->table('tb_mapel')->insert([
                        'id_guru'    => $idGuru,
                        'nama_mapel' => $namaMapel
                    ]);
                }
            }
        }

        session()->setFlashdata(['msg' => 'Edit data berhasil', 'error' => false]);
        return redirect()->to('/admin/guru');
    }

    public function delete($id)
    {
        $db = \Config\Database::connect();

        $db->query('SET FOREIGN_KEY_CHECKS = 0');

        if ($db->tableExists('tb_mapel')) {
            $db->table('tb_mapel')->where('id_guru', $id)->delete();
        }
        if ($db->tableExists('users')) {
            $db->table('users')->where('id_guru', $id)->delete();
        }

        $result = $this->guruModel->delete($id);

        $db->query('SET FOREIGN_KEY_CHECKS = 1');

        if ($result) {
            session()->setFlashdata(['msg' => 'Data berhasil dihapus', 'error' => false]);
        } else {
            session()->setFlashdata(['msg' => 'Gagal menghapus data', 'error' => true]);
        }
        return redirect()->to('/admin/guru');
    }

    public function deleteAllGuru()
    {
        $db = \Config\Database::connect();
        
        $db->query('SET FOREIGN_KEY_CHECKS = 0');

        if ($db->tableExists('tb_mapel')) {
            $db->table('tb_mapel')->truncate();
        }
        $db->table('tb_guru')->truncate();

        $db->query('SET FOREIGN_KEY_CHECKS = 1');

        session()->setFlashdata(['msg' => 'Semua data Guru berhasil dikosongkan!', 'error' => false]);
        return redirect()->to('/admin/guru');
    }
}