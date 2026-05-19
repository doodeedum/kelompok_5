<?php
require_once 'config/database.php';

// Ambil semua materi dari database
$stmt = $pdo->query("SELECT id_materi, nama_materi, deskripsi FROM materi ORDER BY id_materi ASC");
$daftar_materi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Peta icon
$icon_map = [
    'Matematika'      => 'ASSET/matematika.png',
    'Bahasa Indonesia'=> 'ASSET/bahasa_indonesia.png',
    'IT'              => 'ASSET/it.png',
    'IPS'             => 'ASSET/ips.png',
    'Bahasa Inggris'  => 'ASSET/english.png',
];

$deskripsi_map = [
    'Matematika'      => 'Pelajari konsep, rumus, dan operasi matematika dengan mudah!',
    'Bahasa Indonesia'=> 'Tingkatkan kemampuan membaca, menulis dan memahami teks bahasa Indonesia.',
    'IT'              => 'Pahami konsep IT melalui penjelasan yang mudah dan menarik!',
    'IPS'             => 'Pelajari materi IPS untuk memahami kehidupan sosial dan sejarah dunia!',
    'Bahasa Inggris'  => 'Pelajari kosakata, grammar, dan percakapan bahasa Inggris dengan mudah!',
];

// Langsung ke file mapel — tidak perlu baca-materi.php lagi
$file_map = [
    'Matematika'      => 'matematika.php',
    'Bahasa Indonesia'=> 'bahasa-indonesia.php',
    'IT'              => 'it.php',
    'IPS'             => 'ips.php',
    'Bahasa Inggris'  => 'bahasa-inggris.php',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Materi – Kelompok 5</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=ADLaM+Display&family=Actor&display=swap" rel="stylesheet"/>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      min-height: 100vh;
      background: linear-gradient(180deg, #FFFFFE 0%, #F9F383 50%, #FFFDD1 100%);
      font-family: 'Inter', sans-serif;
    }

    nav {
      width: 100%; height: 90px;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 40px;
    }
    .nav-logo { width: 169px; height: 60px; object-fit: contain; }
    .nav-links { display: flex; align-items: center; gap: 40px; list-style: none; }
    .nav-links a { color: #000; font-size: 20px; font-weight: 500; text-decoration: none; transition: color .2s; }
    .nav-links a.active { color: #FFBE0B; }
    .nav-links a:hover  { color: #FFBE0B; }
    .nav-masuk {
      display: flex; align-items: center; gap: 10px;
      background: #F9F383; border-radius: 30px; padding: 8px 20px 8px 8px; cursor: pointer;
    }
    .nav-masuk img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; }
    .nav-masuk span { font-size: 20px; font-weight: 700; }

    .hero { text-align: center; padding: 30px 0 10px; }
    .hero h1 { font-size: 40px; font-weight: 600; margin-bottom: 20px; }
    .hero-box {
      display: inline-block;
      background: #FFF177; border: 1px solid #000; border-radius: 20px;
      padding: 22px 40px;
      font-family: 'ADLaM Display', sans-serif; font-size: 20px; line-height: 1.6;
    }

    .section { padding: 40px 120px 80px; }
    .section-title {
      font-family: 'ADLaM Display', sans-serif;
      font-size: 32px; font-weight: 400; margin-bottom: 28px;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 28px;
    }

    .card {
      background: #FFFADC; border: 1px solid #000; border-radius: 11px;
      padding: 20px; display: flex; align-items: flex-start; gap: 16px;
      min-height: 160px; transition: box-shadow .2s, transform .2s;
    }
    .card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.10); }

    .card-img { width: 120px; height: 120px; object-fit: contain; flex-shrink: 0; border-radius: 8px; }
    .card-body { display: flex; flex-direction: column; justify-content: space-between; height: 120px; flex: 1; }
    .card-title { font-family: 'ADLaM Display', sans-serif; font-size: 20px; font-weight: 400; color: #000; }
    .card-desc { font-family: 'Actor', sans-serif; font-size: 14px; line-height: 1.55; color: #000; margin-top: 6px; flex: 1; }

    .btn-baca {
      display: inline-block; margin-top: 12px;
      background: #FFF045; border: 1px solid #000; border-radius: 9px;
      padding: 5px 14px; font-family: 'Actor', sans-serif;
      font-size: 14px; color: #000; text-decoration: none;
      transition: background .2s; align-self: flex-start;
    }
    .btn-baca:hover { background: #FFD600; }
  </style>
</head>
<body>

<nav>
  <img class="nav-logo" src="ASSET/logo.png" alt="Logo" onerror="this.style.visibility='hidden'"/>
  <ul class="nav-links">
    <li><a href="home.php">Beranda</a></li>
    <li><a href="index.php">Kuis</a></li>
    <li><a href="leaderboard.php">Leaderboard</a></li>
    <li><a href="materi.php" class="active">Materi</a></li>
    <li><a href="tentang.php">Tentang</a></li>
  </ul>
  <div class="nav-masuk">
    <img src="ASSET/avatar.png" alt="User" onerror="this.style.display='none'"/>
    <span>Masuk</span>
  </div>
</nav>

<div class="hero">
  <h1>Materi</h1>
  <div class="hero-box">
    Pelajari materi pelajaran dengan mudah dan<br/>
    tingkatkan pemahamanmu sebelum quiz!
  </div>
</div>

<section class="section">
  <div class="section-title">Daftar Materi</div>
  <div class="grid">

    <?php foreach ($daftar_materi as $m): ?>
    <?php
      $nama = $m['nama_materi'];
      $icon = $icon_map[$nama]  ?? 'ASSET/default.png';
      $desk = $deskripsi_map[$nama] ?? $m['deskripsi'];
      $file = $file_map[$nama]  ?? null;
      // href langsung ke file mapel, bawa id biar bisa query konten
      $href = $file ? $file . '?id=' . $m['id_materi'] : '#';
    ?>
    <div class="card">
      <img class="card-img"
           src="<?= htmlspecialchars($icon) ?>"
           alt="<?= htmlspecialchars($nama) ?>"
           onerror="this.src='https://placehold.co/120x120'"/>
      <div class="card-body">
        <div>
          <div class="card-title"><?= htmlspecialchars($nama) ?></div>
          <div class="card-desc"><?= htmlspecialchars($desk) ?></div>
        </div>
        <a class="btn-baca" href="<?= htmlspecialchars($href) ?>">
          Baca Materi &nbsp;›
        </a>
      </div>
    </div>
    <?php endforeach; ?>

  </div>
</section>

</body>
</html>