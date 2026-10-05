<?php

namespace App\Models;

use App\Models\PresensiInterface;
use CodeIgniter\I18n\Time;
use CodeIgniter\Model;
use App\Libraries\enums\Kehadiran;

class PresensiTendikModel extends Model implements PresensiInterface
{
    protected $table = 'tb_presensi_tendik';
    protected $primaryKey = 'id_presensi';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_tendik',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'id_kehadiran',
        'menit_keterlambatan',
        'keterangan',
        'foto_masuk',
        'foto_keluar',
        'latitude',
        'longitude'
    ];

    public function cekAbsen(string|int $id, string|Time $date)
    {
        $result = $this->where(['id_tendik' => $id, 'tanggal' => $date])->first();

        if (empty($result)) {
            return false;
        }

        return $result[$this->primaryKey];
    }

    public function absenMasuk(string $id, $date, $time, $idKelas = '', $menitKeterlambatan = 0)
    {
        return $this->save([
            'id_tendik'           => $id,
            'tanggal'             => $date,
            'jam_masuk'           => $time,
            'id_kehadiran'        => Kehadiran::Hadir->value,
            'menit_keterlambatan' => $menitKeterlambatan,
            'keterangan'          => $menitKeterlambatan > 0 ? "Terlambat $menitKeterlambatan menit" : ''
        ]);
    }

    public function absenKeluar(string $id, $time)
    {
        return $this->update($id, [
            'jam_keluar' => $time,
            'keterangan' => ''
        ]);
    }

    public function getPresensiByIdTendikTanggal($idTendik, $date)
    {
        return $this->where(['id_tendik' => $idTendik, 'tanggal' => $date])->first();
    }

    public function getPresensiById(string $idPresensi)
    {
        return $this->where([$this->primaryKey => $idPresensi])->first();
    }

    public function getPresensiByKehadiran(string $idKehadiran, $tanggal)
    {
        $this->join(
            'tb_tendik',
            "tb_presensi_tendik.id_tendik = tb_tendik.id_tendik AND tb_presensi_tendik.tanggal = '$tanggal'",
            'right'
        );

        if ($idKehadiran == '4') {
            $result = $this->findAll();

            $schoolConfigurations = new \Config\School();
            $generalSettings = $schoolConfigurations::$generalSettings;
            $jamPulangStandard = $generalSettings->jam_pulang_standard ?? '14:00:00';
            
            $now = Time::now();
            $nowTime = $now->toTimeString();
            $today = $now->toDateString();
            $isAfterSchool = ($today > $tanggal) || ($today == $tanggal && $nowTime > $jamPulangStandard);

            $filteredResult = [];

            foreach ($result as $value) {
                if (!in_array($value['id_kehadiran'], ['1', '2', '3'])) {
                    $value['is_alfa_final'] = $isAfterSchool;
                    array_push($filteredResult, $value);
                }
            }

            return $filteredResult;
        } else {
            $this->where(['tb_presensi_tendik.id_kehadiran' => $idKehadiran]);
            return $this->findAll();
        }
    }

    public function getAttendanceTrend(int $days = 7): array
    {
        $now = Time::now();
        $result = ['hadir' => [], 'sakit' => [], 'izin' => [], 'alfa' => [], 'belum_absen' => []];

        $schoolConfigurations = new \Config\School();
        $generalSettings = $schoolConfigurations::$generalSettings;
        $jamPulangStandard = $generalSettings->jam_pulang_standard ?? '14:00:00';
        $holidayModel = new \App\Models\HariLiburModel();

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $now->subDays($i)->toDateString();

            if ($holidayModel->isHoliday($date)) {
                $result['hadir'][] = 0;
                $result['sakit'][] = 0;
                $result['izin'][] = 0;
                $result['alfa'][] = 0;
                $result['belum_absen'][] = 0;
                continue;
            }

            $isToday = ($date == $now->toDateString());
            $isAfterSchool = (date('Y-m-d') > $date) || ($isToday && $now->toTimeString() > $jamPulangStandard);

            $result['hadir'][] = count($this->getPresensiByKehadiran('1', $date));
            $result['sakit'][] = count($this->getPresensiByKehadiran('2', $date));
            $result['izin'][] = count($this->getPresensiByKehadiran('3', $date));
            
            $notPresentCount = count($this->getPresensiByKehadiran('4', $date));
            
            if ($isAfterSchool) {
                $result['alfa'][] = $notPresentCount;
                $result['belum_absen'][] = 0;
            } else {
                $result['alfa'][] = 0;
                $result['belum_absen'][] = $notPresentCount;
            }
        }

        return $result;
    }

    public function updatePresensi(
        $idPresensi,
        $idTendik,
        $tanggal,
        $idKehadiran,
        $jamMasuk,
        $jamKeluar,
        $keterangan
    ) {
        $presensi = $this->getPresensiByIdTendikTanggal($idTendik, $tanggal);

        $data = [
            'id_tendik'    => $idTendik,
            'tanggal'      => $tanggal,
            'id_kehadiran' => $idKehadiran,
            'keterangan'   => $keterangan ?? $presensi['keterangan'] ?? ''
        ];

        if ($idPresensi != null) {
            $data[$this->primaryKey] = $idPresensi;
        }

        if ($jamMasuk != null) {
            $data['jam_masuk'] = $jamMasuk;
        }

        if ($jamKeluar != null) {
            $data['jam_keluar'] = $jamKeluar;
        }

        return $this->save($data);
    }

    public function getConsecutiveAbsences(int $consecutiveDays = 3): array
    {
        $dates = $this->select('tanggal')
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'DESC')
            ->limit($consecutiveDays)
            ->get()
            ->getResultArray();

        if (count($dates) < $consecutiveDays) {
            return [];
        }

        $dateStrings = array_column($dates, 'tanggal');

        $builder = $this->db->table('tb_tendik')
            ->select('tb_tendik.*');

        $tendiks = $builder->get()->getResultArray();
        $flaggedTendiks = [];

        foreach ($tendiks as $tendik) {
            $totalPresensi = $this->where('id_tendik', $tendik['id_tendik'])->countAllResults();
            if ($totalPresensi === 0) {
                continue;
            }

            $absentCount = 0;
            foreach ($dateStrings as $date) {
                $presensi = $this->where([
                    'id_tendik' => $tendik['id_tendik'],
                    'tanggal'   => $date
                ])->whereIn('id_kehadiran', ['1', '2', '3'])->first();

                if (!$presensi) {
                    $absentCount++;
                }
            }

            if ($absentCount >= $consecutiveDays) {
                $tendik['days_count'] = $absentCount;
                $flaggedTendiks[] = $tendik;
            }
        }

        return $flaggedTendiks;
    }
}