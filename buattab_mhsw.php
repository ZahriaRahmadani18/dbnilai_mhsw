<?php
require_once __DIR__ . "/koneksi.php";

$sql = "CREATE TABLE IF NOT EXISTS nilai_mhsw (
  nim VARCHAR(15) PRIMARY KEY,
  nama VARCHAR(50) NOT NULL,
  photo VARCHAR(255) NULL,
  nilai_akhir DECIMAL(5,2) NOT NULL,
  nilai_huruf CHAR(2),
  predikat VARCHAR(30),
  reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
  echo "Table <strong>nilai_mhsw</strong> created successfully (atau sudah ada)";
} else {
  echo "Error creating table: " . mysqli_error($conn);
}

mysqli_close($conn);
?>