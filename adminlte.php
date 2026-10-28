<?php
// koneksi ke databases
require_once __DIR__ . "/koneksi.php";

// ---------- Parameter dari URL: pencarian, urutan, halaman ----------
$daftar_predikat = ['Sangat Memuaskan', 'Memuaskan', 'Cukup Memuaskan', 'Kurang Memuaskan', 'Tidak Lulus'];
$pilihan_ukuran  = [5, 10, 15];

$q    = trim($_GET['q'] ?? '');                       // cari nama (LIKE)
$pred = $_GET['predikat'] ?? '';                      // cari predikat (WHERE predikat = ...)
if (!in_array($pred, $daftar_predikat, true)) $pred = '';
$sort = $_GET['sort'] ?? '';                          // urut nilai_akhir: asc / desc
if ($sort !== 'asc' && $sort !== 'desc') $sort = '';
$size = (int) ($_GET['size'] ?? 5);
if (!in_array($size, $pilihan_ukuran, true)) $size = 5;
$page = max(1, (int) ($_GET['page'] ?? 1));

// ---------- WHERE (prepared statement) ----------
$where = [];
$tipe  = "";
$param = [];
if ($q !== '') {
  $where[] = "(nama LIKE ? OR nim LIKE ?)";
  $tipe   .= "ss";
  $like    = "%" . addcslashes($q, "%_\\") . "%";
  $param[] = $like;
  $param[] = $like;
}
if ($pred !== '') {
  $where[] = "predikat = ?";
  $tipe   .= "s";
  $param[] = $pred;
}
$sql_where = $where ? " WHERE " . implode(" AND ", $where) : "";

// ---------- Hitung total data -> jumlah halaman ----------
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM nilai_mhsw" . $sql_where);
if ($param) mysqli_stmt_bind_param($stmt, $tipe, ...$param);
mysqli_stmt_execute($stmt);
$total = (int) mysqli_stmt_get_result($stmt)->fetch_row()[0];
mysqli_stmt_close($stmt);

$total_halaman = max(1, (int) ceil($total / $size));
if ($page > $total_halaman) $page = $total_halaman;
$offset = ($page - 1) * $size;

// ---------- Ambil data satu halaman: ORDER BY nilai_akhir + LIMIT ----------
$order = $sort ? "nilai_akhir " . strtoupper($sort) . ", nim ASC" : "reg_date ASC, nim ASC";
$stmt = mysqli_prepare($conn, "SELECT * FROM nilai_mhsw" . $sql_where . " ORDER BY $order LIMIT ? OFFSET ?");
$tipe2  = $tipe . "ii";
$param2 = array_merge($param, [$size, $offset]);
mysqli_stmt_bind_param($stmt, $tipe2, ...$param2);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$dari   = $total ? $offset + 1 : 0;
$sampai = min($offset + $size, $total);

// Buat URL yang mempertahankan pencarian/urutan/ukuran halaman
function url_nilai(array $ubah = []) {
  global $q, $pred, $sort, $size, $page;
  $p = array_merge(['q' => $q, 'predikat' => $pred, 'sort' => $sort, 'size' => $size, 'page' => $page], $ubah);
  $p = array_filter($p, fn($v) => $v !== '' && $v !== null);
  return 'adminlte.php' . ($p ? '?' . http_build_query($p) : '');
}
// Klik pertama = ASC, klik berikutnya = DESC
$sort_berikut = ($sort === 'asc') ? 'desc' : 'asc';
$ikon_sort = $sort === 'asc' ? 'bi-sort-up' : ($sort === 'desc' ? 'bi-sort-down' : 'bi-arrow-down-up');
?>

<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>AdminLTE 4 | Users</title>

    <!--begin::Theme Init (prevents flash of incorrect theme on load, #6043)-->
    <script>
      (() => {
        'use strict';
        const root = document.documentElement;

        // Applications with their own theming opt out of AdminLTE's color mode
        // entirely, here as well as in the bundle.
        if (root.getAttribute('data-lte-color-mode') === 'off') {
          return;
        }

        const STORAGE_KEY = 'lte-theme';
        let stored = null;
        try {
          stored = localStorage.getItem(STORAGE_KEY);
        } catch {
          // localStorage may be unavailable (private mode, sandboxed iframe).
        }
        // Mirror the precedence in color-mode.ts: the visitor's stored choice
        // wins, then a theme this page declared itself, then the OS preference.
        const authored = root.getAttribute('data-bs-theme');
        let resolved = 'light';
        if (stored === 'dark' || stored === 'light') {
          resolved = stored;
        } else if (authored === 'dark' || authored === 'light') {
          resolved = authored;
        } else if (globalThis.matchMedia('(prefers-color-scheme: dark)').matches) {
          resolved = 'dark';
        }
        root.setAttribute('data-bs-theme', resolved);
        root.style.colorScheme = resolved;
        // Flag values computed here, so the bundle does not mistake them for a
        // theme the page declared and stop following the OS preference.
        if (resolved !== authored) {
          root.setAttribute('data-lte-theme-resolved', '');
        }
      })();
    </script>
    <!--end::Theme Init-->

    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->

    <!--begin::Primary Meta Tags-->
    <meta name="title" content="AdminLTE 4 | Users" />
    <meta name="author" content="ColorlibHQ" />
    <meta
      name="description"
      content="AdminLTE is a free Bootstrap 5 admin dashboard template with almost 50 example pages, built with vanilla JS and designed with accessibility in mind."
    />
    <meta
      name="keywords"
      content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel"
    />
    <!--end::Primary Meta Tags-->

    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="./css/adminlte.css" as="style" />
    <!--end::Accessibility Features-->

    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
      media="print"
      onload="this.media = 'all'"
    />
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="./css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
      <!--begin::Header-->
      <nav class="app-header navbar navbar-expand bg-body">
        
      </nav>
      <!--end::Header-->
      
      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Rekap Nilai Mahasiswa</h1>
              </div>
              <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="#">Beranda</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nilai</li>
                  </ol>
                </nav>
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->
        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-12">
                <!--begin::Card-->
                <div class="card mb-4">
                  <!--begin::Card Header-->
                  <div class="card-header">
                    <div class="row g-2 align-items-center">
                      <div class="col-12 col-md-4">
                        <h3 class="card-title">Directory Nilai</h3>
                      </div>
                      <div class="col-12 col-md-8">
                        <div class="d-flex flex-wrap justify-content-md-end gap-2">
                          <form method="get" action="adminlte.php" class="d-flex flex-wrap gap-2" id="form-cari">
                            <input type="hidden" name="size" value="<?= $size ?>" />
                            <?php if ($sort) { ?><input type="hidden" name="sort" value="<?= $sort ?>" /><?php } ?>
                            <div class="input-group input-group-sm w-auto">
                              <span class="input-group-text">
                                <i class="bi bi-search" aria-hidden="true"></i>
                              </span>
                              <input
                                type="search"
                                id="user-search"
                                name="q"
                                class="form-control"
                                placeholder="Cari nama atau NIM"
                                aria-label="Cari nama atau NIM"
                                value="<?= htmlspecialchars($q) ?>"
                                style="width: 180px"
                              />
                            </div>
                            <select
                              id="user-role-filter"
                              name="predikat"
                              class="form-select form-select-sm w-auto"
                              aria-label="Filter predikat"
                              onchange="this.form.submit()"
                            >
                              <option value="">Semua Predikat</option>
                              <?php foreach ($daftar_predikat as $p) { ?>
                                <option value="<?= $p ?>" <?= $pred === $p ? 'selected' : '' ?>><?= $p ?></option>
                              <?php } ?>
                            </select>
                            <?php if ($q !== '' || $pred !== '') { ?>
                              <a href="<?= htmlspecialchars(url_nilai(['q' => '', 'predikat' => '', 'page' => 1])) ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
                            <?php } ?>
                          </form>
                          <button
                            type="button"
                            class="btn btn-sm btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modal-add-user"
                          >
                            <i class="bi bi-person-plus-fill me-1" aria-hidden="true"> </i>
                            Tambah Data Nilai
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!--end::Card Header-->

                  <!--begin::Card Body-->
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-hover align-middle m-0" id="tabel-nilai">
                        <thead>
                          <tr>
                            <th>NIM</th>
                            <th>Nama Lengkap</th>
                            <th>
                              <a href="<?= htmlspecialchars(url_nilai(['sort' => $sort_berikut, 'page' => 1])) ?>"
                                 class="text-reset text-decoration-none"
                                 title="Urutkan nilai akhir <?= strtoupper($sort_berikut) ?>">
                                Nilai Akhir <i class="bi <?= $ikon_sort ?>" aria-hidden="true"></i>
                              </a>
                            </th>
                            <th>Nilai Huruf</th>
                            <th>Predikat</th>
                            <th>Tgl dibuat</th>
                            <th class="text-end">Aksi</th>
                          </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                          <tr>
                            <td><span class="fw-medium"><?= htmlspecialchars($row['nim']) ?></span></td>
                            <td>
                              <div class="d-flex align-items-center">
                                <img
                                  src="<?= !empty($row['photo']) ? 'uploads/' . htmlspecialchars($row['photo']) : './assets/img/user1-128x128.jpg' ?>"
                                  alt="Foto <?= htmlspecialchars($row['nama']) ?>"
                                  class="rounded-circle me-2"
                                  style="width:32px; height:32px; object-fit:cover; flex-shrink:0;"
                                />
                                <span class="fw-medium"><?= htmlspecialchars($row['nama']) ?></span>
                              </div>
                            </td>
                            <td><span class="fw-medium"><?= htmlspecialchars($row['nilai_akhir']) ?></span></td>
                            <td><span class="fw-medium"><?= htmlspecialchars($row['nilai_huruf']) ?></span></td>
                            <td><span class="fw-medium"><?= htmlspecialchars($row['predikat']) ?></span></td>
                            <td><span class="fw-medium"><?= htmlspecialchars($row['reg_date']) ?></span></td>
                            <td class="text-end">
                              <div class="btn-group btn-group-sm">
                                <button
                                  type="button"
                                  class="btn btn-outline-secondary btn-edit"
                                  title="Edit"
                                  data-bs-toggle="modal"
                                  data-bs-target="#modal-edit-nilai"
                                  data-nim="<?= htmlspecialchars($row['nim']) ?>"
                                  data-nama="<?= htmlspecialchars($row['nama']) ?>"
                                  data-nilai="<?= htmlspecialchars($row['nilai_akhir']) ?>"
                                  data-photo="<?= !empty($row['photo']) ? 'uploads/' . htmlspecialchars($row['photo']) : './assets/img/user1-128x128.jpg' ?>"
                                  aria-label="Edit <?= htmlspecialchars($row['nama']) ?>"
                                >
                                  <i class="bi bi-pencil" aria-hidden="true"></i>
                                </button>
                                <a
                                  href="deletenilai.php?nim=<?= urlencode($row['nim']) ?>"
                                  class="btn btn-outline-danger"
                                  title="Hapus"
                                  aria-label="Hapus <?= htmlspecialchars($row['nama']) ?>"
                                  onclick="return confirm('Hapus data <?= htmlspecialchars(addslashes($row['nama']), ENT_QUOTES) ?> (<?= htmlspecialchars(addslashes($row['nim']), ENT_QUOTES) ?>)? Tindakan ini tidak bisa dibatalkan.')"
                                >
                                  <i class="bi bi-trash" aria-hidden="true"></i>
                                </a>
                              </div>
                            </td>
                          </tr>
                        <?php } ?>
                        <?php if ($total === 0) { ?>
                          <tr id="baris-kosong">
                            <td colspan="7" class="text-center text-body-secondary py-4">
                              Data tidak ditemukan
                            </td>
                          </tr>
                        <?php } ?>
                        </tbody>
                      </table>
                    </div>
                    <!-- /.table-responsive -->
                  </div>
                  <!--end::Card Body-->
                  <!--begin::Card Footer-->
                  <div class="card-footer">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                      <div class="d-flex align-items-center gap-2 fs-7 text-body-secondary" id="info-jumlah">
                        <form method="get" action="adminlte.php" class="d-flex align-items-center gap-1 m-0">
                          <?php if ($q !== '') { ?><input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>" /><?php } ?>
                          <?php if ($pred !== '') { ?><input type="hidden" name="predikat" value="<?= htmlspecialchars($pred) ?>" /><?php } ?>
                          <?php if ($sort) { ?><input type="hidden" name="sort" value="<?= $sort ?>" /><?php } ?>
                          <label for="page-size" class="mb-0">Tampilkan</label>
                          <select id="page-size" name="size" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            <?php foreach ($pilihan_ukuran as $u) { ?>
                              <option value="<?= $u ?>" <?= $size === $u ? 'selected' : '' ?>><?= $u ?></option>
                            <?php } ?>
                          </select>
                          <span>data per halaman</span>
                        </form>
                        <span>| Menampilkan <?= $dari ?>&ndash;<?= $sampai ?> dari <?= $total ?> data</span>
                      </div>
                      <nav aria-label="Navigasi halaman">
                        <ul class="pagination pagination-sm m-0">
                          <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars(url_nilai(['page' => max(1, $page - 1)])) ?>">Prev</a>
                          </li>
                          <?php for ($i = 1; $i <= $total_halaman; $i++) { ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                              <a class="page-link" href="<?= htmlspecialchars(url_nilai(['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                          <?php } ?>
                          <li class="page-item <?= $page >= $total_halaman ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars(url_nilai(['page' => min($total_halaman, $page + 1)])) ?>">Next</a>
                          </li>
                        </ul>
                      </nav>
                    </div>
                  </div>
                  <!--end::Card Footer-->
                </div>
                <!--end::Card-->
              </div>
              <!-- /.col -->
            </div>
            <!--end::Row-->

            <!--begin::Tambah Data Nilai Modal-->
            <div class="modal fade" id="modal-add-user" tabindex="-1" aria-labelledby="modal-add-user-label" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="post" action="tambahnilai.php" id="form-new" novalidate>
                    <div class="modal-header">
                      <h5 class="modal-title" id="modal-add-user-label">Tambah Data Nilai</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="alert alert-danger d-none" id="new-umum" role="alert"></div>
                      <div class="mb-3">
                        <label for="new-nim" class="form-label">NIM <span class="text-danger" title="Wajib diisi">*</span></label>
                        <input type="text" class="form-control" id="new-nim" name="nim" inputmode="numeric" maxlength="10" placeholder="e.g. 3202416001" autocomplete="off" />
                        <div class="invalid-feedback" id="new-nim-err"></div>
                      </div>
                      <div class="mb-3">
                        <label for="new-nama" class="form-label">Nama Lengkap <span class="text-danger" title="Wajib diisi">*</span></label>
                        <input type="text" class="form-control" id="new-nama" name="nama" maxlength="50" placeholder="e.g. Jane Doe" autocomplete="off" />
                        <div class="invalid-feedback" id="new-nama-err"></div>
                      </div>
                      <div class="mb-3">
                        <label for="new-photo" class="form-label">Photo</label>
                        <input type="file" class="form-control" id="new-photo" name="photo" accept="image/*" />
                        <div class="invalid-feedback" id="new-photo-err"></div>
                        <div class="form-text">Opsional. JPG/PNG/GIF/WEBP, maks. 2 MB.</div>
                      </div>
                      <div class="mb-3">
                        <label for="new-nilai" class="form-label">Nilai Akhir <span class="text-danger" title="Wajib diisi">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" inputmode="decimal" class="form-control" id="new-nilai" name="nilai_akhir" placeholder="0 - 100" autocomplete="off" />
                        <div class="invalid-feedback" id="new-nilai-err"></div>
                        <div class="form-text">Nilai huruf dan predikat dihitung otomatis dari nilai akhir.</div>
                      </div>
                      <div class="form-text"><span class="text-danger">*</span> wajib diisi</div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                      <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!--end::Tambah Data Nilai Modal-->

            <!--begin::Edit Data Nilai Modal-->
            <div class="modal fade" id="modal-edit-nilai" tabindex="-1" aria-labelledby="modal-edit-nilai-label" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="post" action="editnilai.php" id="form-edit" novalidate>
                    <div class="modal-header">
                      <h5 class="modal-title" id="modal-edit-nilai-label">Edit Data Nilai</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="alert alert-danger d-none" id="edit-umum" role="alert"></div>
                      <div class="mb-3">
                        <label for="edit-nim" class="form-label">NIM <span class="text-danger" title="Wajib diisi">*</span></label>
                        <input type="text" class="form-control" id="edit-nim" name="nim" readonly />
                        <div class="invalid-feedback" id="edit-nim-err"></div>
                      </div>
                      <div class="mb-3">
                        <label for="edit-nama" class="form-label">Nama Lengkap <span class="text-danger" title="Wajib diisi">*</span></label>
                        <input type="text" class="form-control" id="edit-nama" name="nama" maxlength="50" placeholder="e.g. Jane Doe" autocomplete="off" />
                        <div class="invalid-feedback" id="edit-nama-err"></div>
                      </div>
                      <div class="mb-3">
                        <label for="edit-photo" class="form-label">Photo</label><br>
                        <img id="edit-photo-preview" src="" alt="Foto saat ini"
                             class="rounded-circle mb-2" style="width:64px; height:64px; object-fit:cover;" />
                        <input type="file" class="form-control" id="edit-photo" name="photo" accept="image/*" />
                        <div class="invalid-feedback" id="edit-photo-err"></div>
                        <div class="form-text">Kosongkan jika tidak ingin mengganti. JPG/PNG/GIF/WEBP, maks. 2 MB.</div>
                      </div>
                      <div class="mb-3">
                        <label for="edit-nilai" class="form-label">Nilai Akhir <span class="text-danger" title="Wajib diisi">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" inputmode="decimal" class="form-control" id="edit-nilai" name="nilai_akhir" placeholder="0 - 100" autocomplete="off" />
                        <div class="invalid-feedback" id="edit-nilai-err"></div>
                        <div class="form-text">Nilai huruf dan predikat dihitung ulang otomatis.</div>
                      </div>
                      <div class="form-text"><span class="text-danger">*</span> wajib diisi</div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                      <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!--end::Edit Data Nilai Modal-->

            
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->
      <!--begin::Footer-->
      <footer class="app-footer">
        <!--begin::To the end-->
        <div class="float-end d-none d-sm-inline">
            Pemrograman Web Semester 5 - Desain menggunakan
            <a href="https://adminlte.io" class="text-decoration-none">AdminLTE.io</a>.
        </div>
        <!--end::To the end-->
        <!--begin::Copyright-->
        <strong>
          Hak Cipta &copy; Pada Prodi TI 2026&nbsp;
          <a href="https://adminlte.io" class="text-decoration-none">AdminLTE.io</a>.
        </strong>
        Seluruh Hak Cipta Dilindungi.
        <!--end::Copyright-->
      </footer>
      <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="./js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)-->
    <!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

        // Disable OverlayScrollbars on mobile devices to prevent touch interference
        const isMobile = window.innerWidth <= 992;

        if (
          sidebarWrapper &&
          OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
          !isMobile
        ) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>
    <!--end::OverlayScrollbars Configure-->

    <!--begin::Color Mode Toggle-->
    <!-- The light/dark/auto switcher ships in adminlte.js as the ColorMode
     module (since 4.1) — no page script needed. Only the no-flash snippet
     in <head> stays inline, because it must run before first paint. -->
    <!--end::Color Mode Toggle-->


    <!--begin::Isi Modal Edit-->
    <script>
      document.getElementById('modal-edit-nilai').addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        if (!btn) return;
        document.getElementById('edit-nim').value = btn.dataset.nim;
        document.getElementById('edit-nama').value = btn.dataset.nama;
        document.getElementById('edit-nilai').value = btn.dataset.nilai;
        document.getElementById('edit-photo').value = '';
        document.getElementById('edit-photo-preview').src = btn.dataset.photo;
      });
    </script>
    <!--end::Isi Modal Edit-->

    <!--begin::Validasi Popup (tanpa pindah halaman)-->
    <script>
      (function () {
        const $ = (id) => document.getElementById(id);

        function setError(prefix, field, msg) {
          const input = $(prefix + '-' + field);
          const box = $(prefix + '-' + field + '-err');
          if (!input || !box) return;
          input.classList.toggle('is-invalid', !!msg);
          box.textContent = msg || '';
        }
        function clearAll(prefix) {
          ['nim', 'nama', 'photo', 'nilai'].forEach((f) => setError(prefix, f, ''));
          const u = $(prefix + '-umum');
          u.classList.add('d-none');
          u.textContent = '';
        }
        function cekNim(v) {
          if (v === '') return 'NIM wajib diisi.';
          if (!/^[0-9]+$/.test(v)) return 'NIM harus berupa angka.';
          if (v.length !== 10) return 'NIM harus 10 digit angka (sekarang ' + v.length + ' digit).';
          return '';
        }
        function cekNama(v) {
          return v.trim() === '' ? 'Nama lengkap wajib diisi.' : '';
        }
        function cekNilai(v) {
          v = v.trim().replace(',', '.');
          if (v === '') return 'Nilai akhir wajib diisi.';
          if (!/^[0-9]+(\.[0-9]{1,2})?$/.test(v)) return 'Nilai akhir harus berupa angka (maks. 2 desimal).';
          if (parseFloat(v) < 0 || parseFloat(v) > 100) return 'Nilai akhir harus antara 0 sampai 100.';
          return '';
        }

        // ---- NIM: huruf/simbol langsung dibuang + muncul peringatan ----
        const nim = $('new-nim');
        let nimWarnTimer = null;
        nim.addEventListener('input', function () {
          const bersih = nim.value.replace(/[^0-9]/g, '');
          if (bersih !== nim.value) {
            nim.value = bersih;
            setError('new', 'nim', 'NIM harus berupa angka. Huruf dan simbol tidak diperbolehkan.');
            clearTimeout(nimWarnTimer);
            nimWarnTimer = setTimeout(function () {
              nimWarnTimer = null;
              setError('new', 'nim', nim.value === '' || nim.value.length === 10 ? '' : cekNim(nim.value));
            }, 2500);
          } else if (!nimWarnTimer && nim.classList.contains('is-invalid')) {
            setError('new', 'nim', nim.value.length === 10 ? '' : cekNim(nim.value));
          }
        });
        // ---- Nilai: hanya angka dan satu pemisah desimal ----
        const nilai = $('new-nilai');
        nilai.addEventListener('input', function () {
          const asli = nilai.value;
          let v = asli.replace(/[^0-9.,]/g, '');
          const k = v.search(/[.,]/);
          if (k !== -1) v = v.slice(0, k + 1) + v.slice(k + 1).replace(/[.,]/g, '');
          nilai.value = v;
          if (v !== asli) setError('new', 'nilai', 'Nilai akhir harus berupa angka.');
          else if (nilai.classList.contains('is-invalid')) setError('new', 'nilai', cekNilai(v));
        });
        $('new-nama').addEventListener('input', function () {
          if ($('new-nama').classList.contains('is-invalid')) setError('new', 'nama', cekNama($('new-nama').value));
        });

        // ---- Kirim via fetch: error tampil di dalam popup ----
        function pasang(prefix, validasi) {
          const form = $('form-' + prefix);
          form.addEventListener('submit', async function (e) {
            e.preventDefault();
            clearAll(prefix);

            const err = validasi();
            Object.keys(err).forEach((f) => setError(prefix, f, err[f]));
            const pertama = Object.keys(err)[0];
            if (pertama) { $(prefix + '-' + pertama).focus(); return; }

            const data = new FormData(form);
            data.set('nilai_akhir', String(data.get('nilai_akhir')).trim().replace(',', '.'));
            const tombol = form.querySelector('button[type=submit]');
            tombol.disabled = true;
            try {
              const res = await fetch(form.getAttribute('action'), {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'fetch' },
              });
              const json = await res.json();
              if (json.ok) { window.location.reload(); return; }
              const map = { nim: 'nim', nama: 'nama', photo: 'photo', nilai_akhir: 'nilai' };
              Object.keys(json.errors || {}).forEach(function (k) {
                if (k === 'umum') {
                  const u = $(prefix + '-umum');
                  u.textContent = json.errors[k];
                  u.classList.remove('d-none');
                } else if (map[k]) {
                  setError(prefix, map[k], json.errors[k]);
                }
              });
            } catch (x) {
              const u = $(prefix + '-umum');
              u.textContent = 'Gagal menghubungi server. Coba lagi.';
              u.classList.remove('d-none');
            } finally {
              tombol.disabled = false;
            }
          });
        }

        pasang('new', function () {
          const e = {};
          const a = cekNim($('new-nim').value); if (a) e.nim = a;
          const b = cekNama($('new-nama').value); if (b) e.nama = b;
          const c = cekNilai($('new-nilai').value); if (c) e.nilai = c;
          return e;
        });
        pasang('edit', function () {
          const e = {};
          const b = cekNama($('edit-nama').value); if (b) e.nama = b;
          const c = cekNilai($('edit-nilai').value); if (c) e.nilai = c;
          return e;
        });

        // Bersihkan isian & pesan setiap popup dibuka/ditutup
        $('modal-add-user').addEventListener('hidden.bs.modal', function () {
          $('form-new').reset();
          clearAll('new');
        });
        $('modal-add-user').addEventListener('shown.bs.modal', function () { $('new-nim').focus(); });
        $('modal-edit-nilai').addEventListener('show.bs.modal', function () { clearAll('edit'); });
      })();
    </script>
    <!--end::Validasi Popup-->

    <!--begin::Cari otomatis saat mengetik-->
    <script>
      (function () {
        const form  = document.getElementById('form-cari');
        const input = document.getElementById('user-search');
        let t;
        input.addEventListener('input', function () {
          clearTimeout(t);
          t = setTimeout(function () { form.submit(); }, 400);
        });
        if (input.value !== '') {
          input.focus();
          const n = input.value.length;
          input.setSelectionRange(n, n);
        }
      })();
    </script>
    <!--end::Cari otomatis saat mengetik-->
    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>