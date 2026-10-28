<?php
require_once __DIR__ . "/koneksi.php";

$pesan = "";
$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';

function kirim_json($data) {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data);
  exit;
}

// 1) Form dikirim: simpan perubahan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nim   = trim($_POST['nim'] ?? '');
  $nama  = trim($_POST['nama'] ?? '');
  $nilai = (float) ($_POST['nilai_akhir'] ?? 0);

  // Validasi sisi server
  $errors = [];
  if (!preg_match('/^[0-9]{10}$/', $nim)) {
    $errors['nim'] = "NIM harus berupa 10 digit angka.";
  }
  if ($nama === '') {
    $errors['nama'] = "Nama lengkap wajib diisi.";
  } elseif (mb_strlen($nama) > 50) {
    $errors['nama'] = "Nama maksimal 50 karakter.";
  }
  $nilai_in = trim($_POST['nilai_akhir'] ?? '');
  if ($nilai_in === '') {
    $errors['nilai_akhir'] = "Nilai akhir wajib diisi.";
  } elseif (!is_numeric($nilai_in) || $nilai < 0 || $nilai > 100) {
    $errors['nilai_akhir'] = "Nilai akhir harus berupa angka 0 sampai 100.";
  }

  $fotoBaru = simpan_foto($_FILES['photo'] ?? null, $errFoto);
  if ($errFoto) $errors['photo'] = $errFoto;

  // Konversi nilai ke huruf & predikat (sesuai ketentuan dosen)
  if ($nilai <= 34.50)      { $huruf = 'E'; $predikat = 'Tidak Lulus'; }
  elseif ($nilai <= 50.50)  { $huruf = 'D'; $predikat = 'Kurang Memuaskan'; }
  elseif ($nilai <= 65.50)  { $huruf = 'C'; $predikat = 'Cukup Memuaskan'; }
  elseif ($nilai <= 80.50)  { $huruf = 'B'; $predikat = 'Memuaskan'; }
  else                      { $huruf = 'A'; $predikat = 'Sangat Memuaskan'; }

  if (!$errors) try {
    if ($fotoBaru) {
      // Ganti foto: simpan nama baru, hapus file lama
      $s = mysqli_prepare($conn, "SELECT photo FROM nilai_mhsw WHERE nim = ?");
      mysqli_stmt_bind_param($s, "s", $nim);
      mysqli_stmt_execute($s);
      $lama = mysqli_stmt_get_result($s)->fetch_assoc()['photo'] ?? null;
      mysqli_stmt_close($s);
      $stmt = mysqli_prepare($conn, "UPDATE nilai_mhsw SET nama=?, photo=?, nilai_akhir=?, nilai_huruf=?, predikat=? WHERE nim=?");
      mysqli_stmt_bind_param($stmt, "ssdsss", $nama, $fotoBaru, $nilai, $huruf, $predikat, $nim);
    } else {
      $stmt = mysqli_prepare($conn, "UPDATE nilai_mhsw SET nama=?, nilai_akhir=?, nilai_huruf=?, predikat=? WHERE nim=?");
      mysqli_stmt_bind_param($stmt, "sdsss", $nama, $nilai, $huruf, $predikat, $nim);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    if ($fotoBaru) hapus_foto($lama);
    mysqli_close($conn);
    if ($isAjax) kirim_json(['ok' => true]);
    header("Location: adminlte.php");
    exit;
  } catch (mysqli_sql_exception $e) {
    hapus_foto($fotoBaru);
    $errors['umum'] = "Error: " . $e->getMessage();
  }
  if ($errors && $fotoBaru) hapus_foto($fotoBaru);
  if ($isAjax) kirim_json(['ok' => false, 'errors' => $errors]);
  if ($errors) {
    $pesan = implode("<br>", array_map('htmlspecialchars', $errors));
  }

  // Jika gagal, tampilkan lagi isian yang tadi diketik
  $data = ['nim' => $nim, 'nama' => $nama, 'nilai_akhir' => $_POST['nilai_akhir']];
} else {
  // 2) Dibuka dari tombol Edit: ambil data lama
  if (!isset($_GET['nim'])) {
    header("Location: adminlte.php");
    exit;
  }
  $stmt = mysqli_prepare($conn, "SELECT nim, nama, nilai_akhir FROM nilai_mhsw WHERE nim = ?");
  mysqli_stmt_bind_param($stmt, "s", $_GET['nim']);
  mysqli_stmt_execute($stmt);
  $data = mysqli_stmt_get_result($stmt)->fetch_assoc();
  if (!$data) {
    die("Data tidak ditemukan.");
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Form Edit Nilai</title>
</head>
<body>
  <h3>Form Edit Nilai Mahasiswa</h3>

  <?php if ($pesan != "") { ?>
    <p style="color:red;"><?= $pesan ?></p>
  <?php } ?>

  <form method="post" action="editnilai.php" enctype="multipart/form-data">
    NIM:<br>
    <?= htmlspecialchars($data['nim']) ?>
    <input type="hidden" name="nim" value="<?= htmlspecialchars($data['nim']) ?>"><br>

    <label for="nama">Nama</label><br>
    <input type="text" id="nama" name="nama" required value="<?= htmlspecialchars($data['nama']) ?>"><br>

    <label for="photo">Photo (kosongkan jika tidak diganti)</label><br>
    <input type="file" id="photo" name="photo" accept="image/*"><br>

    <label for="nilai_akhir">Nilai Akhir</label><br>
    <input type="number" step="0.01" min="0" max="100" id="nilai_akhir" name="nilai_akhir" required
           value="<?= htmlspecialchars($data['nilai_akhir']) ?>"><br><br>

    <a href="adminlte.php"><button type="button">Cancel</button></a>
    <input type="submit" value="Update">
  </form>
</body>
</html>