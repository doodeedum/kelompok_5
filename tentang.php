<?php
// ============================================================
// KONEKSI DATABASE
// ============================================================
$host = "localhost";
$user = "root";
$pass = "";
$db   = "kelompok_5";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("<p style='color:red;font-family:sans-serif;padding:20px'>
         Koneksi gagal: " . $conn->connect_error . "</p>");
}

// ── Ambil fitur utama ─────────────────────────────────────
$fitur = [];
$res = $conn->query("SELECT * FROM fitur ORDER BY urutan ASC");
if ($res) while ($row = $res->fetch_assoc()) $fitur[] = $row;

// ── Ambil tim kelompok ────────────────────────────────────
$tim = [];
$res = $conn->query("SELECT * FROM tim_kelompok ORDER BY no_absen ASC");
if ($res) while ($row = $res->fetch_assoc()) $tim[] = $row;

// ── Ambil visi ────────────────────────────────────────────
$visi = null;
$res  = $conn->query("SELECT * FROM visi_misi WHERE tipe='visi' LIMIT 1");
if ($res) $visi = $res->fetch_assoc();

// ── Ambil misi ────────────────────────────────────────────
$misi = null;
$res  = $conn->query("SELECT * FROM visi_misi WHERE tipe='misi' LIMIT 1");
if ($res) $misi = $res->fetch_assoc();

// ── Ambil total materi & konten dari DB ───────────────────
$total_materi = 0;
$total_konten = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM materi");
if ($res) $total_materi = $res->fetch_assoc()['total'];
$res = $conn->query("SELECT COUNT(*) AS total FROM konten_materi");
if ($res) $total_konten = $res->fetch_assoc()['total'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>TahooGa — Tentang</title>
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --cream:     #FAEEDA;
      --cream-mid: #F5E4C0;
      --yellow:    #EFC93D;
      --gold:      #C49A0A;
      --brown:     #4A2C06;
      --brown-mid: #7A4F18;
      --brown-lt:  #9A6728;
      --white:     #FFFFFF;
      --shadow:    rgba(74,44,6,.10);
      --fh:        'Fredoka', sans-serif;
      --fb:        'Plus Jakarta Sans', sans-serif;
      --radius:    16px;
    }

    html { scroll-behavior: smooth; }

    body {
      background: var(--cream);
      font-family: var(--fb);
      color: var(--brown);
      min-height: 100vh;
      overflow-x: hidden;
    }

    /* DOODLE BG */
    .doodle-bg {
      position: fixed; inset: 0; z-index: 0;
      pointer-events: none; overflow: hidden; opacity: .06;
    }
    .doodle-bg svg { width: 100%; height: 100%; }

    /* NAVBAR */
    nav {
      position: sticky; top: 0; z-index: 100;
      background: var(--cream);
      border-bottom: 1.5px solid #EFC93D44;
      padding: 0 48px; height: 62px;
      display: flex; align-items: center; justify-content: space-between;
      box-shadow: 0 2px 16px var(--shadow);
    }
    .nav-logo {
      display: flex; align-items: center; gap: 10px;
      text-decoration: none;
    }
    .nav-logo-icon {
      width: 36px; height: 36px;
      background: var(--yellow); border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px; box-shadow: 0 2px 8px var(--shadow);
    }
    .nav-logo-text {
      font-family: var(--fh); font-size: 22px; font-weight: 600;
      color: var(--brown); letter-spacing: -.3px;
    }
    .nav-logo-text span { color: var(--gold); }

    .nav-links {
      display: flex; align-items: center; gap: 28px; list-style: none;
    }
    .nav-links a {
      text-decoration: none; font-size: 14px; font-weight: 500;
      color: var(--brown-mid); transition: color .2s;
    }
    .nav-links a:hover { color: var(--brown); }
    .nav-links a.active { color: var(--gold); font-weight: 600; }

    .nav-btn {
      background: var(--yellow); color: var(--brown);
      border: none; border-radius: 20px; padding: 7px 22px;
      font-size: 14px; font-weight: 600; font-family: var(--fb);
      cursor: pointer; transition: background .2s, transform .1s;
    }
    .nav-btn:hover { background: var(--gold); color: var(--white); }
    .nav-btn:active { transform: scale(.97); }

    /* MAIN */
    main {
      position: relative; z-index: 1;
      max-width: 980px; margin: 0 auto;
      padding: 52px 28px 100px;
    }

    /* HERO */
    .hero {
      display: grid; grid-template-columns: 1fr 200px;
      gap: 32px; align-items: center;
      margin-bottom: 44px;
      animation: fadeUp .5s ease both;
    }
    .hero h1 {
      font-family: var(--fh); font-size: clamp(30px,4.5vw,50px);
      font-weight: 600; line-height: 1.15;
      color: var(--brown); margin-bottom: 18px;
    }
    .hero h1 .brand { color: var(--gold); font-style: italic; }
    .hero p { font-size: 15px; line-height: 1.85; color: var(--brown-mid); }
    .hero-img { animation: float 3.5s ease-in-out infinite; }
    .hero-img svg { width: 100%; }

    /* STATS */
    .stats-strip {
      display: grid; grid-template-columns: repeat(3,1fr);
      gap: 16px; margin-bottom: 44px;
      animation: fadeUp .5s .1s ease both;
    }
    .stat-card {
      background: var(--white);
      border: 1.5px solid #EFC93D55; border-radius: var(--radius);
      padding: 20px; text-align: center;
      box-shadow: 0 2px 12px var(--shadow);
    }
    .stat-num {
      font-family: var(--fh); font-size: 38px; font-weight: 600;
      color: var(--gold); display: block; line-height: 1; margin-bottom: 5px;
    }
    .stat-lbl { font-size: 13px; color: var(--brown-lt); }

    /* SECTION TITLE */
    .section-title {
      font-family: var(--fh); font-size: 28px; font-weight: 600;
      color: var(--brown); margin-bottom: 22px;
    }

    .divider {
      height: 2px;
      background: linear-gradient(90deg,transparent,var(--yellow),transparent);
      margin: 44px 0; opacity: .4; border-radius: 2px;
    }

    /* FITUR CARDS */
    .fitur-grid {
      display: grid; grid-template-columns: repeat(3,1fr); gap: 18px;
      animation: fadeUp .5s .15s ease both;
    }
    .fitur-card {
      background: var(--white);
      border: 1.5px solid #EFC93D44; border-radius: var(--radius);
      padding: 28px 22px 22px;
      position: relative; overflow: hidden;
      transition: transform .25s, box-shadow .25s, border-color .25s;
    }
    .fitur-card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0;
      height: 4px; border-radius: var(--radius) var(--radius) 0 0;
      background: var(--top-color, #EF9F27);
    }
    .fitur-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 14px 36px rgba(74,44,6,.13);
      border-color: var(--yellow);
    }
    .fitur-icon {
      width: 52px; height: 52px; border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      font-size: 26px; margin-bottom: 14px;
      background: var(--icon-bg, #FFF3DC);
    }
    .fitur-card h3 {
      font-family: var(--fh); font-size: 18px; font-weight: 600;
      color: var(--brown); margin-bottom: 8px;
    }
    .fitur-card p { font-size: 13.5px; line-height: 1.65; color: var(--brown-lt); }

    /* TIM KELOMPOK */
    .tim-grid {
      display: grid; grid-template-columns: repeat(4,1fr); gap: 18px;
      animation: fadeUp .5s .2s ease both;
    }
    .tim-card {
      background: var(--white);
      border: 1.5px solid #EFC93D44; border-radius: var(--radius);
      padding: 28px 16px 22px; text-align: center;
      transition: transform .25s, box-shadow .25s, border-color .25s;
      position: relative; overflow: hidden;
    }
    .tim-card::after {
      content: ''; position: absolute; bottom: 0; left: 0; right: 0;
      height: 3px; background: var(--yellow);
      border-radius: 0 0 var(--radius) var(--radius);
      transform: scaleX(0); transform-origin: center;
      transition: transform .3s ease;
    }
    .tim-card:hover::after { transform: scaleX(1); }
    .tim-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 14px 32px rgba(74,44,6,.13);
      border-color: var(--yellow);
    }
    .tim-avatar {
      width: 68px; height: 68px;
      background: var(--cream-mid); border-radius: 50%;
      border: 2.5px solid var(--yellow);
      display: flex; align-items: center; justify-content: center;
      font-size: 30px; margin: 0 auto 14px;
      box-shadow: 0 4px 12px var(--shadow);
    }
    .tim-nama {
      font-family: var(--fh); font-size: 15px; font-weight: 600;
      color: var(--brown); display: block; margin-bottom: 4px;
      line-height: 1.3;
    }
    .tim-absen {
      font-size: 12px; color: var(--brown-lt);
      display: block; margin-bottom: 10px; font-weight: 500;
    }
    .tim-peran {
      display: inline-block;
      background: var(--cream-mid); color: var(--brown-mid);
      font-size: 11.5px; font-weight: 600;
      padding: 4px 12px; border-radius: 20px;
      border: 1px solid #EFC93D66;
    }

    /* VISI MISI */
    .vm-grid {
      display: grid; grid-template-columns: 1fr 1fr; gap: 18px;
      animation: fadeUp .5s .2s ease both;
    }
    .vm-card {
      background: var(--white);
      border: 1.5px solid #EFC93D44; border-radius: var(--radius);
      padding: 28px 24px;
    }
    .vm-badge {
      display: inline-flex; align-items: center; gap: 6px;
      background: var(--cream-mid); color: var(--brown);
      font-size: 12px; font-weight: 600; font-family: var(--fh);
      padding: 4px 14px; border-radius: 20px; margin-bottom: 12px;
    }
    .vm-card h3 {
      font-family: var(--fh); font-size: 20px;
      color: var(--brown); margin-bottom: 10px;
    }
    .vm-card p, .vm-card li {
      font-size: 14px; line-height: 1.8; color: var(--brown-lt);
    }
    .vm-card ul { padding-left: 18px; }
    .vm-card li { margin-bottom: 5px; }

    /* FOOTER */
    footer {
      position: relative; z-index: 1;
      background: var(--brown); color: var(--cream);
      padding: 26px 48px;
      display: flex; align-items: center; justify-content: space-between;
      font-size: 13px;
    }
    .ft-logo { font-family: var(--fh); font-size: 18px; color: var(--yellow); }
    footer p { color: #C4A47A; }

    /* ANIMATIONS */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(22px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes float {
      0%,100% { transform: translateY(0); }
      50%     { transform: translateY(-10px); }
    }

    /* RESPONSIVE */
    @media (max-width: 820px) {
      nav { padding: 0 18px; }
      .nav-links { display: none; }
      main { padding: 32px 16px 60px; }
      .hero { grid-template-columns: 1fr; }
      .hero-img { display: none; }
      .fitur-grid, .vm-grid { grid-template-columns: 1fr; }
      .tim-grid { grid-template-columns: repeat(2,1fr); }
      .stats-strip { grid-template-columns: repeat(2,1fr); }
    }
  </style>
</head>
<body>

<!-- DOODLE BG -->
<div class="doodle-bg" aria-hidden="true">
  <svg viewBox="0 0 1200 800" xmlns="http://www.w3.org/2000/svg">
    <text x="40"  y="120" font-size="80"  fill="#C49A0A" transform="rotate(-15 40 120)">?</text>
    <text x="900" y="80"  font-size="60"  fill="#C49A0A" transform="rotate(10 900 80)">&#9999;</text>
    <text x="200" y="620" font-size="70"  fill="#C49A0A" transform="rotate(20 200 620)">&#128218;</text>
    <text x="1060"y="500" font-size="65"  fill="#C49A0A" transform="rotate(-10 1060 500)">&#127942;</text>
    <text x="580" y="760" font-size="55"  fill="#C49A0A" transform="rotate(5 580 760)">&#11088;</text>
    <text x="490" y="190" font-size="50"  fill="#C49A0A" transform="rotate(-20 490 190)">?</text>
    <text x="90"  y="390" font-size="45"  fill="#C49A0A" transform="rotate(12 90 390)">&#9999;</text>
    <text x="790" y="690" font-size="48"  fill="#C49A0A" transform="rotate(-8 790 690)">&#128214;</text>
  </svg>
</div>

<!-- NAVBAR -->
<nav>
  <a href="beranda.php" class="nav-logo">
    <div class="nav-logo-icon">&#10067;</div>
    <span class="nav-logo-text">Tahoo<span>Ga</span></span>
  </a>
  <ul class="nav-links">
    <li><a href="beranda.php">Beranda</a></li>
    <li><a href="kuis.php">Kuis</a></li>
    <li><a href="leaderboard.php">Leaderboard</a></li>
    <li><a href="materi.php">Materi</a></li>
    <li><a href="tentang.php" class="active">Tentang</a></li>
  </ul>
  <button class="nav-btn" onclick="window.location.href='login.php'">Keluar</button>
</nav>

<!-- MAIN -->
<main>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-text">
      <h1>Mengapa <span class="brand">TahooGa</span> ?</h1>
      <p>
        Belajar nggak harus membosankan. TahooGa hadir sebagai solusi untuk menciptakan
        pengalaman belajar yang lebih interaktif dan menyenangkan di dalam kelas.<br><br>
        Terinspirasi dari semangat belajar sambil bermain, kami membangun platform kuis
        yang menggabungkan konsep OOP (Object-Oriented Programming) dan sistem basis
        data yang solid — agar setiap pertanyaan tersimpan rapi, setiap jawaban terukur,
        dan setiap sesi belajar terasa seperti petualangan.
      </p>
    </div>
    <div class="hero-img" aria-hidden="true">
      <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
        <circle cx="100" cy="100" r="92" fill="#FFF3DC" stroke="#EFC93D" stroke-width="2"/>
        <text x="100" y="128" text-anchor="middle" font-size="80">&#129489;&#8205;&#128187;</text>
      </svg>
    </div>
  </section>

  <!-- STATS -->
  <div class="stats-strip">
    <div class="stat-card">
      <span class="stat-num"><?= (int)$total_materi ?></span>
      <span class="stat-lbl">Mata Pelajaran</span>
    </div>
    <div class="stat-card">
      <span class="stat-num"><?= (int)$total_konten ?>+</span>
      <span class="stat-lbl">Sub-topik Materi</span>
    </div>
    <div class="stat-card">
      <span class="stat-num"><?= count($tim) ?></span>
      <span class="stat-lbl">Anggota Tim</span>
    </div>
  </div>

  <!-- FITUR UTAMA -->
  <h2 class="section-title">Fitur Utama</h2>
  <div class="fitur-grid">
    <?php foreach ($fitur as $f): ?>
    <div class="fitur-card"
         style="--top-color:<?= htmlspecialchars($f['warna_top']) ?>;
                --icon-bg:<?= htmlspecialchars($f['warna_icon_bg']) ?>;">
      <div class="fitur-icon"><?= $f['icon'] ?></div>
      <h3><?= htmlspecialchars($f['judul']) ?></h3>
      <p><?= htmlspecialchars($f['deskripsi']) ?></p>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="divider"></div>

  <!-- TIM KELOMPOK -->
  <h2 class="section-title">Tim Kami — Kelompok 5</h2>
  <div class="tim-grid">
    <?php foreach ($tim as $anggota): ?>
    <div class="tim-card">
      <div class="tim-avatar"><?= $anggota['avatar'] ?></div>
      <span class="tim-nama"><?= htmlspecialchars($anggota['nama']) ?></span>
      <span class="tim-absen">No. Absen <?= (int)$anggota['no_absen'] ?></span>
      <span class="tim-peran"><?= htmlspecialchars($anggota['peran']) ?></span>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="divider"></div>

  <!-- VISI MISI -->
  <h2 class="section-title">Visi &amp; Misi</h2>
  <div class="vm-grid">

    <div class="vm-card">
      <span class="vm-badge">&#128301; Visi</span>
      <?php if ($visi): ?>
        <h3><?= htmlspecialchars($visi['judul']) ?></h3>
        <p><?= htmlspecialchars($visi['isi']) ?></p>
      <?php endif; ?>
    </div>

    <div class="vm-card">
      <span class="vm-badge">&#128640; Misi</span>
      <?php if ($misi): ?>
        <h3><?= htmlspecialchars($misi['judul']) ?></h3>
        <ul>
          <?php foreach (explode('|', $misi['isi']) as $poin): ?>
            <li><?= htmlspecialchars(trim($poin)) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

  </div>

</main>

<!-- FOOTER -->
<footer>
  <span class="ft-logo">TahooGa</span>
  <p>&copy; 2025 Kelompok 5 &mdash; Dibuat dengan &#128155; untuk tugas sekolah</p>
</footer>

</body>
</html>