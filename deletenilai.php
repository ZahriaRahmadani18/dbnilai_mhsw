<?php
require_once __DIR__ . "/koneksi.php";

if (isset($_GET['nim'])) {
  $s = mysqli_prepare($conn, "SELECT photo FROM nilai_mhsw WHERE nim = ?");
  mysqli_stmt_bind_param($s, "s", $_GET['nim']);
  mysqli_stmt_execute($s);
  $lama = mysqli_stmt_get_result($s)->fetch_assoc()['photo'] ?? null;
  mysqli_stmt_close($s);

  $stmt = mysqli_prepare($conn, "DELETE FROM nilai_mhsw WHERE nim = ?");
  mysqli_stmt_bind_param($stmt, "s", $_GET['nim']);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
  hapus_foto($lama);
}

mysqli_close($conn);
header("Location: adminlte.php");
exit;
?>