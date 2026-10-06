# Database Seeders

Folder ini berisi file-file seeder untuk mengisi data awal ke database.

## 📋 Daftar Seeder

### 1. DatabaseSeeder.php
**Main Seeder** yang menjalankan semua seeder dalam urutan yang benar.

**Cara menjalankan:**
```bash
php spark db:seed DatabaseSeeder
```

### 2. KehadiranSeeder.php
Mengisi data master status kehadiran.

**Data yang di-seed:**
- Hadir
- Sakit
- Izin
- Tanpa keterangan

### 3. JurusanSeeder.php
Mengisi data jurusan sekolah.

**Data yang di-seed:**
- OTKP (Otomatisasi dan Tata Kelola Perkantoran)
- BDP (Bisnis Daring dan Pemasaran)
- AKL (Akuntansi dan Keuangan Lembaga)
- RPL (Rekayasa Perangkat Lunak)

**Catatan:** Sesuaikan dengan jurusan di sekolah Anda.

### 4. KelasSeeder.php
Mengisi data kelas awal untuk semua tingkat dan jurusan.

**Data yang di-seed:**
- Kelas X A, B, C, D (untuk setiap jurusan)
- Kelas XI A, B, C, D (untuk setiap jurusan)
- Kelas XII A, B, C, D (untuk setiap jurusan)

**Total:** 12 kelas (3 tingkat × 4 jurusan)

### 5. SuperadminSeeder.php
Membuat akun superadmin awal. Sebelum menjalankan, isi kredensial khusus di `.env`:

```ini
SUPERADMIN_SEED_EMAIL = 'admin@sekolah.sch.id'
SUPERADMIN_SEED_USERNAME = 'superadmin'
SUPERADMIN_SEED_PASSWORD = 'password-acak-minimal-12-karakter'
```

Seeder berhenti jika kredensial belum diisi, dan melewati pembuatan jika email atau username sudah terdaftar. Jangan memakai password contoh di atas.

### 6. GeneralSettingsSeeder.php
Mengisi pengaturan umum aplikasi.

**Data default:**
- School Name: SMK 1 Indonesia
- School Year: 2024/2025
- Copyright: © 2025 All rights reserved.
- Logo: null (akan diisi lewat aplikasi)

**Customize Settings:**
Edit file `GeneralSettingsSeeder.php` sebelum menjalankan seed.

### 7. GuruSeeder.php (Optional)
Contoh seeder untuk data guru. Gunakan untuk development/testing.

**Cara menjalankan:**
```bash
php spark db:seed GuruSeeder
```

### 8. SiswaSeeder.php (Optional)
Contoh seeder untuk data siswa. Gunakan untuk development/testing.

**Cara menjalankan:**
```bash
php spark db:seed SiswaSeeder
```

## 🚀 Cara Penggunaan

### Jalankan Semua Seeder

```bash
php spark db:seed DatabaseSeeder
```

Ini akan menjalankan seeder dalam urutan:
1. KehadiranSeeder
2. JurusanSeeder
3. KelasSeeder
4. SuperadminSeeder
5. GeneralSettingsSeeder

### Jalankan Seeder Individual

```bash
# Jalankan satu seeder saja
php spark db:seed KehadiranSeeder
php spark db:seed JurusanSeeder
php spark db:seed SuperadminSeeder
```

### Reset dan Seed Ulang

```bash
# Truncate table terlebih dahulu
php spark db:table tb_kehadiran --truncate

# Atau reset semua dengan migration
php spark migrate:refresh
php spark db:seed DatabaseSeeder
```

## 🔒 Keamanan

### Credentials Default
Seeder ini membuat akun dengan password default. **Wajib diubah** di production!

### Best Practice
1. ✅ Ubah credentials di `SuperadminSeeder.php` sebelum deploy
2. ✅ Jangan commit file `.env` dengan credentials production
3. ✅ Gunakan environment variables untuk sensitive data
4. ✅ Disable seeder di production setelah initial setup

## 📝 Membuat Seeder Baru

### Template Seeder

```php
<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class NamaSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'field1' => 'value1',
                'field2' => 'value2',
            ],
            // ... more data
        ];

        // Insert batch
        $this->db->table('nama_tabel')->insertBatch($data);
        
        // Or insert one by one
        foreach ($data as $row) {
            $this->db->table('nama_tabel')->insert($row);
        }
    }
}
```

### Generate Seeder via CLI

```bash
php spark make:seeder NamaSeeder
```

## ⚠️ Catatan Penting

### Idempotency
Beberapa seeder sudah dilengkapi dengan pengecekan data existing:
- `SuperadminSeeder` - Cek username/email sebelum insert
- `GeneralSettingsSeeder` - Cek apakah sudah ada settings

### Data Duplikasi
Jika menjalankan seeder berulang kali tanpa truncate:
- Data master akan duplikat
- Gunakan `TRUNCATE` atau `DELETE` sebelum seed ulang

### Foreign Key Constraints
Pastikan seed dalam urutan yang benar:
1. Master data dulu (kehadiran, jurusan)
2. Data yang depend on master (kelas)
3. Users dan settings terakhir

## 🧪 Testing

### Development Environment

```bash
# Seed dengan sample data lengkap
php spark db:seed DatabaseSeeder

# Uncomment GuruSeeder dan SiswaSeeder di DatabaseSeeder
php spark db:seed DatabaseSeeder
```

### Production Environment

```bash
# Seed HANYA data master yang diperlukan
php spark db:seed KehadiranSeeder
php spark db:seed JurusanSeeder
php spark db:seed KelasSeeder
php spark db:seed SuperadminSeeder
php spark db:seed GeneralSettingsSeeder
```

## 📚 Referensi

- [CodeIgniter 4 Database Seeding](https://codeigniter.com/user_guide/dbmgmt/seeds.html)
- [Query Builder Documentation](https://codeigniter.com/user_guide/database/query_builder.html)
- [MIGRATION_GUIDE.md](../../../MIGRATION_GUIDE.md) - Panduan lengkap

## 🔗 Related

- Migrations: `app/Database/Migrations/`
- Models: `app/Models/`
- Documentation: `MIGRATION_GUIDE.md`

---

**Last Updated:** 2025-12-26
