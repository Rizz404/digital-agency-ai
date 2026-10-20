# Digital Agency AI

Studio agensi digital multi-tenant berbasis Laravel 13, Blade, Alpine 3, Tailwind 4, dan Vite 8. Seluruh development berada dalam folder ini, dengan riwayat Git baru tanpa commit maupun remote.

## Fitur dan domain

- Katalog layanan: branding, web design, iklan digital, konten, SEO, dan otomasi. Harga paket adalah estimasi; penawaran final memerlukan konfirmasi tim setelah brief.
- Konsultan dan permintaan konsultasi 60 menit. Satu perusahaan/pemesan per slot konsultan, status awal pending, tanpa pembayaran otomatis. Nama konsultan, durasi, dan biaya konsultasi disimpan saat permintaan dibuat.
- Pembatalan oleh tim melepaskan kapasitas sekali. Permintaan yang dibatalkan tidak dapat dibuka kembali.
- AI Digital Strategist membantu memilih layanan, merangkum brief awal, memeriksa slot melalui tool, mengajukan konsultasi, dan menyerahkan percakapan ke tim. AI tidak mengonfirmasi transaksi atau menjanjikan hasil kampanye.
- Bahasa Indonesia, Inggris, dan Jepang. Pembuka, beranda, katalog/detail, konsultan/detail, proses/detail, profil/FAQ, kontak, wizard, narator, dan chat.
- SVG asli di `public/images/agency`: ruang studio, pemandu transparan, profil konsultan demo, dan studi visual paket. Indeks proyek bernomor di kanan, kanvas konten utama, pemandu bawah tengah, dan chat tengah. Pada mobile indeks menjadi daftar gulir di bawah.
- Admin memiliki katalog, konsultan, knowledge, konsultasi, dan handover yang dibatasi tenant.

## Lokal

Kebutuhan: PHP sesuai `composer.lock` (dependency terkunci membutuhkan setidaknya PHP 8.4), Composer, Node >=22.12 dan npm. SQLite dipakai lokal. Jalankan dengan binary PHP yang memuat ekstensi OpenSSL, mbstring, intl, fileinfo, pdo_sqlite, sqlite3 dan zip.

```bash
composer setup
php artisan db:seed --no-interaction
php artisan serve --host=127.0.0.1 --port=8027
```

Port 8027 adalah kandidat lokal; pastikan belum dipakai sebelum menjalankan server. `composer setup` hanya instalasi dependency, pembuatan environment/key, migrasi dan build; tidak menjalankan testing. Pembuka berada di `/`, tenant demo di `/digital-agency-ai`, admin di `/admin`.

Seeder lokal membuat enam paket, empat konsultan ilustratif, proses/FAQ, dan slot hari kerja untuk 60 hari ke depan. Tidak ada akun admin default. Kontak publik demo sengaja kosong. Seluruh profil, harga, dan konten demo bukan informasi bisnis yang telah disahkan.

## Akun admin

Command berikut tersedia; jalankan setelah identitas akun dan tenant ditetapkan:

```bash
php artisan agency:create-admin --email=admin@example.com --name="Agency Admin" --agency=digital-agency-ai
```

Password minimum 12 karakter dibaca dua kali secara tersembunyi atau melalui stdin. Password tidak diterima sebagai argumen dan tidak dicetak. Jangan menjalankan UserSeeder untuk setup produksi. Belum ada akun atau kredensial integrasi yang dibuat untuk proyek ini.

## AI dan privasi konfigurasi

Isi `LOCAL_LLM_BASE_URL` tanpa `/v1`, `LOCAL_LLM_API_KEY`, dan `LOCAL_LLM_MODEL` hanya melalui `.env` privat setelah disetujui. Model harus mendukung function calling. TTS menggunakan `OPENROUTER_*` dan opsional. Tanpa konfigurasi provider, halaman dan formulir tersedia, tetapi chat provider nyata belum aktif. SMTP default `log`.

Tool yang diizinkan: `search_knowledge`, `search_catalog`, `get_service_package`, `search_team`, `check_consultation_availability`, `create_consultation_request`, `request_human_handover`. Tenant dan percakapan berasal dari ToolContext. Simpan rahasia di `.env`, jangan kirim melalui chat atau memasukkannya ke Git.

## Fondasi produksi

- Template `deploy/env.production.example` menggunakan PostgreSQL; migrasi ditulis dengan schema builder agar portabel ke SQLite dan PostgreSQL 13. Kompatibilitas PostgreSQL belum dijalankan di server.
- `ProductionAgencySeeder` membuat tenant draft kosong secara idempoten tanpa akun. Konten demo hanya dapat dipasang per kelas setelah keputusan konten produksi.
- `agency:open-slots` menyiapkan slot hari kerja dalam jendela 60 hari, satu kapasitas per konsultan, tanpa menimpa `reserved_slots`. Konfigurasi jam mengikuti tenant. Jadwal harian pukul 01:00 Asia/Jakarta memakai pencegahan overlap; scheduler Plesk belum diaktifkan.
- `TRUSTED_PROXIES` dibaca aplikasi. Isi hanya alamat/CIDR proxy aktual, jangan wildcard. Konfigurasi diterapkan setelah boot konfigurasi.
- Template PHP: 256 MB, 95 detik, timestamp validation aktif. Additional actions Plesk tersedia di `deploy/plesk-actions.sh.example`; dependency tetap dipasang oleh Toolkit. Template nginx/supervisor warisan bukan instruksi instalasi Plesk.
- `.github/workflows/ci.yml` hanya dipicu `workflow_dispatch`. Testing/lint/audit tidak berjalan pada push atau pull request dan tidak disisipkan dalam build/deploy.

## Status pekerjaan

Development lokal tersedia. Build dan kompilasi runtime dicatat dalam laporan sesi; tidak ada commit, push, remote GitHub, atau deploy. Identitas akun, kredensial, repository eksternal, dan perubahan produksi menunggu konfirmasi terpisah.

Testing tidak dijalankan karena belum diminta secara eksplisit. Pemeriksaan browser/visual, login, alur konsultasi, provider LLM/TTS/SMTP, serta PostgreSQL produksi belum dijalankan. Fixture suite warisan disesuaikan dengan domain agensi tanpa mengklaim hasil lulus.
