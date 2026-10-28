<?php
require_once __DIR__ . "/koneksi.php";

$sql = "SELECT nim, nama, nilai_akhir, nilai_huruf, predikat, reg_date
        FROM nilai_mhsw ORDER BY reg_date ASC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Rekap Nilai Mahasiswa</title>
  <style>
    table { border-collapse: collapse; margin-top: 8px; }
    th, td { border: 1px solid #555; padding: 5px 8px; }
    caption { font-weight: bold; padding-bottom: 6px; }
  </style>
</head>
<body>
  <a href="tambahnilai.php"><button type="button">Tambah Data Nilai</button></a>

  <table>
    <caption>Rekap Nilai Mahasiswa</caption>
    <tr>
      <th>NIM</th>
      <th>Nama Lengkap</th>
      <th>Nilai Akhir</th>
      <th>Nilai Huruf</th>
      <th>Predikat</th>
      <th>Tgl Dibuat</th>
      <th>Aksi</th>
    </tr>
    <?php if ($result && mysqli_num_rows($result) > 0) { ?>
      <?php while ($row = mysqli_fetch_assoc($result)) { ?>
      <tr>
        <td><?= htmlspecialchars($row['nim']) ?></td>
        <td><?= htmlspecialchars($row['nama']) ?></td>
        <td><?= $row['nilai_akhir'] ?></td>
        <td><?= htmlspecialchars($row['nilai_huruf']) ?></td>
        <td><?= htmlspecialchars($row['predikat']) ?></td>
        <td><?= $row['reg_date'] ?></td>
        <td>
          <a href="editnilai.php?nim=<?= urlencode($row['nim']) ?>"><button type="button">Edit</button></a> |
          <a href="deletenilai.php?nim=<?= urlencode($row['nim']) ?>"
             onclick="return confirm('Yakin hapus data ini?')"><button type="button">Hapus</button></a>
        </td>
      </tr>
      <?php } ?>
    <?php } else { ?>
      <tr><td colspan="7" align="center">Data tidak ditemukan</td></tr>
    <?php } ?>
  </table>
</body>
</html>
<?php mysqli_close($conn); ?>