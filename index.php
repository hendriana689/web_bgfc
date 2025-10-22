<?php
// ==== FILE PENYIMPANAN ====
$file = "taekwondo.txt";

// ==== LOAD DATA ====
if (!file_exists($file)) {
  $data = ['atlet' => [], 'lari' => [], 'tendangan' => []];
  file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

$data = json_decode(file_get_contents($file), true);
if (!is_array($data)) $data = ['atlet' => [], 'lari' => [], 'tendangan' => []];

// ==== FUNGSI SIMPAN ====
function simpanData($data, $file) {
  file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// ==== TAMBAH ATLET ====
if (isset($_POST['tambah_atlet'])) {
  $nama = trim($_POST['nama']);
  $umur = (int)$_POST['umur'];

  if ($nama !== '' && $umur > 0) {
    $id = count($data['atlet']) ? max(array_column($data['atlet'], 'id_atlet')) + 1 : 1;
    $data['atlet'][] = ['id_atlet' => $id, 'nama' => $nama, 'umur' => $umur];
    simpanData($data, $file);
  }
  header("Location: index.php");
  exit;
}

// ==== HAPUS ATLET ====
if (isset($_GET['hapus_atlet'])) {
  $id = (int)$_GET['hapus_atlet'];
  $data['atlet'] = array_values(array_filter($data['atlet'], fn($a) => $a['id_atlet'] != $id));
  $data['lari'] = array_values(array_filter($data['lari'], fn($l) => $l['id_atlet'] != $id));
  $data['tendangan'] = array_values(array_filter($data['tendangan'], fn($t) => $t['id_atlet'] != $id));
  simpanData($data, $file);
  header("Location: index.php");
  exit;
}

// ==== TAMBAH DATA LARI ====
if (isset($_POST['tambah_lari'])) {
  $atlet_id = (int)$_POST['id_atlet'];
  $tanggal = $_POST['tanggal'];
  $waktu_lari = floatval(str_replace(',', '.', $_POST['waktu_lari']));

  $id = count($data['lari']) ? max(array_column($data['lari'], 'id_lari')) + 1 : 1;
  $data['lari'][] = [
    'id_lari' => $id,
    'id_atlet' => $atlet_id,
    'tanggal' => $tanggal,
    'waktu_lari' => $waktu_lari
  ];

  simpanData($data, $file);
  header("Location: index.php?atlet=$atlet_id");
  exit;
}

// ==== TAMBAH DATA TENDANGAN ====
if (isset($_POST['tambah_tendangan'])) {
  $atlet_id = (int)$_POST['id_atlet'];
  $tanggal = $_POST['tanggal'];
  $jumlah_tendangan = (int)$_POST['jumlah_tendangan'];

  $id = count($data['tendangan']) ? max(array_column($data['tendangan'], 'id_tendangan')) + 1 : 1;
  $data['tendangan'][] = [
    'id_tendangan' => $id,
    'id_atlet' => $atlet_id,
    'tanggal' => $tanggal,
    'jumlah_tendangan' => $jumlah_tendangan
  ];

  simpanData($data, $file);
  header("Location: index.php?atlet=$atlet_id");
  exit;
}

$atlet_aktif = $_GET['atlet'] ?? null;
$atlet_data = $data['atlet'];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BGFC</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <nav class="navbar navbar-dark">
    <div><img src="logo bgfc.png" height="55"> <img src="tulisan BGFC.png" height="55"></div>
    <div><img src="Taekwondo Indonesia.png" height="55"></div>
  </nav>

  <div class="container mt-4">
    <div class="row">
      <!-- SIDEBAR -->
      <div class="col-md-3">
        <h5>Daftar Atlet</h5>
        <ul class="list-group">
          <?php foreach($atlet_data as $i => $a): ?>
            <li class="list-group-item <?= ($atlet_aktif == $a['id_atlet']) ? 'active' : '' ?>">
              <a href="?atlet=<?= $a['id_atlet'] ?>" class="<?= ($atlet_aktif == $a['id_atlet']) ? 'text-white' : '' ?>">
                <strong><?= $i+1 ?>.</strong> <?= htmlspecialchars($a['nama']) ?>
              </a>
              <a href="?hapus_atlet=<?= $a['id_atlet'] ?>" class="btn btn-sm btn-danger float-end">🗑</a>
            </li>
          <?php endforeach; ?>
        </ul>

        <form class="mt-3" method="post">
          <input type="text" class="form-control mb-2" name="nama" placeholder="Nama Atlet" required>
          <input type="number" class="form-control mb-2" name="umur" placeholder="Umur" required>
          <button type="submit" name="tambah_atlet" class="btn btn-primary w-100">Tambah Atlet</button>
        </form>
      </div>

      <!-- AREA GRAFIK -->
      <div class="col-md-9">
        <?php if($atlet_aktif):
          $lari = array_values(array_filter($data['lari'], fn($l) => $l['id_atlet'] == $atlet_aktif));
          $tendangan = array_values(array_filter($data['tendangan'], fn($t) => $t['id_atlet'] == $atlet_aktif));
          $nama_atlet = '';
          foreach($data['atlet'] as $a){ if($a['id_atlet']==$atlet_aktif){$nama_atlet=$a['nama'];break;}}
        ?>
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
          <h5>Data Latihan - <?= htmlspecialchars($nama_atlet) ?></h5>
          <div>
            <button class="btn btn-success btn-sm" onclick="toggleForm('lariForm')">+ Input Lari</button>
            <button class="btn btn-warning btn-sm" onclick="toggleForm('tendanganForm')">+ Input Tendangan</button>
          </div>
        </div>

        <form id="lariForm" class="card card-body mb-3 d-none" method="post">
          <input type="hidden" name="id_atlet" value="<?= $atlet_aktif ?>">
          <input type="date" class="form-control mb-2" name="tanggal" required>
          <input type="text" class="form-control mb-2" name="waktu_lari" placeholder="Format 21.11" required>
          <button type="submit" name="tambah_lari" class="btn btn-success">✔ Simpan</button>
        </form>

        <form id="tendanganForm" class="card card-body mb-3 d-none" method="post">
          <input type="hidden" name="id_atlet" value="<?= $atlet_aktif ?>">
          <input type="date" class="form-control mb-2" name="tanggal" required>
          <input type="number" class="form-control mb-2" name="jumlah_tendangan" placeholder="Jumlah Tendangan" required>
          <button type="submit" name="tambah_tendangan" class="btn btn-warning">✔ Simpan</button>
        </form>

        <div class="row">
          <div class="col-md-6 mb-3">
            <h6>Grafik Waktu Lari (menit.detik)</h6>
            <canvas id="chartLari"></canvas>
          </div>
          <div class="col-md-6 mb-3">
            <h6>Grafik Jumlah Tendangan</h6>
            <canvas id="chartTendangan"></canvas>
          </div>
        </div>
        <?php else: ?>
          <p class="text-muted mt-4">Pilih atlet untuk melihat grafik latihan.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>
