# Multi-Server Control Panel (Web-Based)

## 1. Deskripsi
Aplikasi **web-based control panel** untuk mengelola **multiple Ubuntu server** yang menjalankan **Apache2, MySQL, PHP-FPM (multi version)**.  
Fokus pada manajemen website, database, service, dan konfigurasi PHP-FPM, serta akses terminal dari satu dashboard pusat.

## 2. Fitur Utama (Wajib)
1. **Apache2 Configuration**
   - Edit `apache2.conf` dan virtual host `.conf` langsung dari panel.
   - Aktif/nonaktifkan site (`a2ensite` / `a2dissite`).
   - Enable/disable module Apache (`a2enmod` / `a2dismod`).
   - Generate virtual host config dari template.

2. **Site Management**
   - Tambah site Apache2 pada server.
   - Upload file aplikasi ke `/var/www/html`.
   - Extract ZIP/tar.gz langsung dari panel.
   - Set permission (chown/chmod) folder/file.

3. **PHP-FPM Version Control**
   - Pilih PHP-FPM version per site atau folder (`/var/www/html/...`).
   - Lihat modul PHP aktif per versi.
   - Aktif/nonaktifkan PHP extension.

4. **Service Management**
   - Start/stop/restart Apache2, MySQL, PHP-FPM, PHP7.4-FPM, PHP8.4-FPM.
   - Lihat status service (Running / Stopped).

5. **Terminal Access**
   - Jalankan perintah shell langsung ke server agent dari panel.
   - Output terminal tampil langsung di browser.
   - Dukungan untuk multi-line command & script singkat.
   - History perintah yang pernah dijalankan.

## 3. Arsitektur Sistem
- **Control Panel Pusat** (Web App)
  - Dibuat dengan PHP (CodeIgniter 4.6.2) dan database mysql
  - koneksi database lokal laptop ini : host=localhost, port=33066, username=simrs, password=bismilah
  - Menyediakan dashboard & API untuk komunikasi ke server-agent.
  - Authentikasi login multi user
- **Server Agent**
  - Aplikasi ringan Python yang berjalan di setiap server lokal.
  - Terkoneksi ke Control Panel melalui jaringan **VPN** (IP private).
  - Mendengarkan perintah dari Control Panel via protokol TCP/Socket atau HTTP sederhana (tidak perlu HTTPS karena lewat VPN).
  - Eksekusi perintah (ubah config, restart service, jalankan command) secara aman.

**Catatan:**
- Autentikasi antar panel ↔ agent dilakukan dengan **pre-shared token** atau kunci RSA.
- Semua agent berada di jaringan tertutup VPN (tidak ada IP public).

## 6. Catatan Pengembangan
- Tidak perlu HTTPS karena semua koneksi lewat VPN.
- Gunakan autentikasi **token** untuk komunikasi Control Panel ↔ Agent.
- Untuk fitur terminal, eksekusi perintah dengan pembatasan (whitelist command) agar tidak merusak sistem.
- UI dibuat responsif Bootstrap 5.
- Pastikan file konfigurasi & log bisa diakses langsung dari panel.
- BUATKAN APLIKASI AGENT TERLEBIH DAHULU
- Aplikasi server terletak di folder server
- Aplikasi agent terletak di folder agent
