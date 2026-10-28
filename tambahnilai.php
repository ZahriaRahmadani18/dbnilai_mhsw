<?php
require_once __DIR__ . "/koneksi.php";

// Permintaan dari popup (fetch) menandai dirinya dengan header ini -> balas JSON
$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';

function kirim_json($data) {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data);
  exit;
}

$pesan = "";
$nim = $nama = $nilai_akhir = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nim         = trim($_POST['nim'] ?? '');
  $nama        = trim($_POST['nama'] ?? '');
  $nilai_akhir = trim($_POST['nilai_akhir'] ?? '');
  $nilai       = (float) $nilai_akhir;

  // Validasi sisi server (wajib, karena validasi di browser bisa dilewati)
  $errors = [];
  if ($nim === '') {
    $errors['nim'] = "NIM wajib diisi.";
  } elseif (!preg_match('/^[0-9]+$/', $nim)) {
    $errors['nim'] = "NIM harus berupa angka.";
  } elseif (strlen($nim) !== 10) {
    $errors['nim'] = "NIM harus 10 digit angka.";
  }
  if ($nama === '') {
    $errors['nama'] = "Nama lengkap wajib diisi.";
  } elseif (mb_strlen($nama) > 50) {
    $errors['nama'] = "Nama maksimal 50 karakter.";
  }
  if ($nilai_akhir === '') {
    $errors['nilai_akhir'] = "Nilai akhir wajib diisi.";
  } elseif (!is_numeric($nilai_akhir) || $nilai < 0 || $nilai > 100) {
    $errors['nilai_akhir'] = "Nilai akhir harus berupa angka 0 sampai 100.";
  }

  $photo = simpan_foto($_FILES['photo'] ?? null, $errFoto);
  if ($errFoto) $errors['photo'] = $errFoto;

  // Konversi nilai ke huruf & predikat (sesuai ketentuan dosen)
  if ($nilai <= 34.50)      { $huruf = 'E'; $predikat = 'Tidak Lulus'; }
  elseif ($nilai <= 50.50)  { $huruf = 'D'; $predikat = 'Kurang Memuaskan'; }
  elseif ($nilai <= 65.50)  { $huruf = 'C'; $predikat = 'Cukup Memuaskan'; }
  elseif ($nilai <= 80.50)  { $huruf = 'B'; $predikat = 'Memuaskan'; }
  else                      { $huruf = 'A'; $predikat = 'Sangat Memuaskan'; }

  if (!$errors) {
    try {
      $stmt = mysqli_prepare($conn, "INSERT INTO nilai_mhsw (nim, nama, photo, nilai_akhir, nilai_huruf, predikat) VALUES (?, ?, ?, ?, ?, ?)");
      mysqli_stmt_bind_param($stmt, "sssdss", $nim, $nama, $photo, $nilai, $huruf, $predikat);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);
      mysqli_close($conn);
      if ($isAjax) kirim_json(['ok' => true]);
      header("Location: adminlte.php");
      exit;
    } catch (mysqli_sql_exception $e) {
      hapus_foto($photo);
      if ($e->getCode() == 1062) {
        $errors['nim'] = "NIM $nim sudah terdaftar. Gunakan NIM lain.";
      } else {
        $errors['umum'] = "Error: " . $e->getMessage();
      }
    }
  }

  if ($errors && $photo) hapus_foto($photo);
  if ($isAjax) kirim_json(['ok' => false, 'errors' => $errors]);
  $pesan = implode("<br>", array_map('htmlspecialchars', $errors));
} elseif ($isAjax) {
  kirim_json(['ok' => false, 'errors' => ['umum' => 'Metode tidak valid.']]);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Data Nilai</title>
</head>
<body>
  <h3>Tambah Data Nilai Mahasiswa</h3>

  <?php if ($pesan != "") { ?>
    <p style="color:red;"><?= $pesan ?></p>
  <?php } ?>

  <form method="post" action="tambahnilai.php" enctype="multipart/form-data">
    <label for="nim">NIM</label><br>
    <input type="text" id="nim" name="nim" required inputmode="numeric"
           pattern="[0-9]{10}" minlength="10" maxlength="10"
           title="NIM harus 10 digit angka"
           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
           value="<?= htmlspecialchars($nim) ?>"><br>

    <label for="nama">Nama</label><br>
    <input type="text" id="nama" name="nama" required maxlength="50" value="<?= htmlspecialchars($nama) ?>"><br>

    <label for="photo">Photo (opsional)</label><br>
    <input type="file" id="photo" name="photo" accept="image/*"><br>

    <label for="nilai_akhir">Nilai Akhir</label><br>
    <input type="number" step="0.01" min="0" max="100" id="nilai_akhir" name="nilai_akhir" required
           value="<?= htmlspecialchars($nilai_akhir) ?>"><br><br>

    <a href="adminlte.php"><button type="button">Cancel</button></a>
    <input type="submit" value="Simpan">
  </form>
</body>
</html>