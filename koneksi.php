<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db5d";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}


// echo "<strong> Connected successfully </strong><br>";

// ---- Kolom photo: otomatis ditambahkan jika belum ada di tabel nilai_mhsw ----
function pastikan_kolom_photo($conn) {
  $r = mysqli_query($conn, "SHOW COLUMNS FROM nilai_mhsw LIKE 'photo'");
  if ($r && mysqli_num_rows($r) === 0) {
    mysqli_query($conn, "ALTER TABLE nilai_mhsw ADD COLUMN photo VARCHAR(255) NULL AFTER nama");
  }
}
pastikan_kolom_photo($conn);

// ---- Upload foto: kembalikan nama file, null jika tidak ada upload, atau salah satu error ----
const FOTO_DIR = __DIR__ . "/uploads/";
function simpan_foto($file, &$error) {
  $error = "";
  if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
  if ($file['error'] !== UPLOAD_ERR_OK) { $error = "Upload foto gagal."; return null; }
  if ($file['size'] > 2 * 1024 * 1024) { $error = "Ukuran foto maksimal 2 MB."; return null; }
  $info = @getimagesize($file['tmp_name']);
  $ext = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
  if (!$info || !isset($ext[$info[2]])) { $error = "Foto harus berformat JPG, PNG, GIF, atau WEBP."; return null; }
  if (!is_dir(FOTO_DIR)) mkdir(FOTO_DIR, 0755, true);
  $nama = bin2hex(random_bytes(8)) . "." . $ext[$info[2]];
  if (!move_uploaded_file($file['tmp_name'], FOTO_DIR . $nama)) { $error = "Foto gagal disimpan."; return null; }
  return $nama;
}
function hapus_foto($nama) {
  if ($nama && preg_match('/^[a-f0-9]{16}\.(jpg|png|gif|webp)$/', $nama) && is_file(FOTO_DIR . $nama)) {
    unlink(FOTO_DIR . $nama);
  }
}
?>