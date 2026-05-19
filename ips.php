<?php
require_once 'config/database.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) { header('Location: materi.php'); exit; }

$stmt = $pdo->prepare("SELECT id_materi, nama_materi, deskripsi FROM materi WHERE id_materi = ?");
$stmt->execute([$id]);
$materi = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$materi) { header('Location: materi.php'); exit; }

$stmt2 = $pdo->prepare("SELECT id_konten, judul, isi, urutan FROM konten_materi WHERE id_materi = ? ORDER BY urutan ASC");
$stmt2->execute([$id]);
$konten_list = $stmt2->fetchAll(PDO::FETCH_ASSOC);

foreach ($konten_list as &$k) {
    $stmt3 = $pdo->prepare("SELECT id_contoh, contoh FROM contoh_materi WHERE id_konten = ? ORDER BY id_contoh ASC");
    $stmt3->execute([$k['id_konten']]);
    $k['contoh'] = $stmt3->fetchAll(PDO::FETCH_ASSOC);
}
unset($k);

// ══════════════════════════════════════════════════════════════
//  PENANDA ASSET — sesuaikan path jika nama file berbeda
// ══════════════════════════════════════════════════════════════
//
//  ASSET 1 — LOGO navbar (kiri atas)
//  Ukuran tampil : 169 × 60 px  |  Format : PNG / SVG
//  Letakkan file di: assets/logo.png
$asset_logo = 'assets/logo.png';
//
//  ASSET 2 — BACKGROUND halaman (overlay opacity 0.6, full-page, posisi: fixed inset 0)
//  Ukuran ideal  : 1440 × 1024 px  |  Format : JPG / PNG / WebP
//  Letakkan file di: assets/background.jpg
$asset_bg = 'assets/background.jpg';
//
//  ASSET 3 — IKON mata pelajaran IPS
//  Ukuran tampil : 54×54 px (sidebar lingkaran), 44×44 px (title bar), 69×69 px (item sidebar)
//  Format        : PNG transparan
//  Letakkan file di: assets/ips.png
$asset_icon = 'assets/ips.png';
//
//  ASSET 4 — AVATAR pengguna (opsional, belum digunakan di halaman ini)
//  Ukuran tampil : 50 × 50 px  |  Format : JPG / PNG
//  Letakkan file di: assets/avatar.png
$asset_avatar = 'assets/avatar.png';
//
// ══════════════════════════════════════════════════════════════

$total_konten = count($konten_list);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($materi['nama_materi']) ?> – Kelompok 5</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=ADLaM+Display&family=Actor&family=Joan&display=swap" rel="stylesheet"/>
  <style>
    /* ═══════════════════════════════════════════
       CSS VARIABLES — Tema Oranye (IPS)
    ═══════════════════════════════════════════ */
    :root {
      --aksen:       #E67E22;
      --aksen-dark:  #CA6F1E;
      --bg-light:    #FFF8F0;
      --bg-yellow:   #F9F383;
      --bg-cream:    #FDF7C5;
      --bg-kartu:    #C5DE96;
      --black:       #000000;
      --white:       #FFFFFF;
      --nav-h:       90px;
      --sidebar-w:   322px;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    /* ═══════════════════════════════════════════
       BODY — Figma gradient: #FFFFFE → #F9F383 → #FFFDD1
    ═══════════════════════════════════════════ */
    body {
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
      background: linear-gradient(180deg, #FFFFFE 0%, #F9F383 50%, #FFFDD1 100%);
      position: relative;
      overflow-x: hidden;
    }

    /* ── ASSET 2: Background image overlay ─────────────────
       Figma: 1440×1024, opacity 0.60, fixed, full-page
       FILE : ASSET/background.jpg  (1440×1024 px, JPG/PNG/WebP) */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      z-index: 0;
      background: url('<?= htmlspecialchars($asset_bg) ?>') center / cover no-repeat;
      opacity: 0.60;
      pointer-events: none;
    }

    nav, .page-body { position: relative; z-index: 1; }

    /* ═══════════════════════════════════════════
       NAVBAR — Figma: 1440×90, white bg
       Logo kiri 169×60 | Links tengah Inter 24px 500
       Kembali kanan 186×62 #F9F383 radius 30
    ═══════════════════════════════════════════ */
    nav {
      width: 100%;
      height: var(--nav-h);           /* 90px */
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 40px;
      background: var(--white);
      position: sticky;
      top: 0;
      z-index: 100;
      border-bottom: 1px solid rgba(0,0,0,0.06);
    }

    /* ── ASSET 1: Logo navbar ───────────────────
       Area tampil: 169 × 60 px
       FILE : ASSET/logo.png  (PNG/SVG, min 338×120 px untuk retina) */
    .nav-logo-wrap {
      width: 169px;
      height: 60px;
      display: flex;
      align-items: center;
      flex-shrink: 0;
    }
    .nav-logo {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
    /* Fallback teks jika gambar tidak ditemukan */
    .nav-logo-fallback {
      display: none;
      font-family: 'ADLaM Display', sans-serif;
      font-size: 22px;
      color: var(--aksen);
      font-weight: 700;
    }

    /* Links — Figma: Inter 24px 500, hitam; aktif/hover: warna aksen */
    .nav-links {
      display: flex;
      align-items: center;
      gap: 38px;
      list-style: none;
    }
    .nav-links a {
      color: var(--black);
      font-size: 24px;
      font-weight: 500;
      font-family: 'Inter', sans-serif;
      text-decoration: none;
      transition: color 0.2s;
    }
    .nav-links a.active { color: var(--aksen); font-weight: 600; }
    .nav-links a:hover  { color: var(--aksen); }

    /* Tombol Kembali — Figma: 186×62, #F9F383, radius 30, Inter 20px 700 */
    .nav-kembali {
      width: 186px;
      height: 62px;
      background: var(--bg-yellow);
      border-radius: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Inter', sans-serif;
      font-size: 20px;
      font-weight: 700;
      color: var(--black);
      text-decoration: none;
      border: none;
      cursor: pointer;
      flex-shrink: 0;
      transition: background 0.2s, transform 0.15s;
    }
    .nav-kembali:hover { background: #f0e96e; transform: translateY(-1px); }

    /* ═══════════════════════════════════════════
       LAYOUT UTAMA — sidebar 322px + konten flex
    ═══════════════════════════════════════════ */
    .page-body {
      display: flex;
      align-items: flex-start;
      min-height: calc(100vh - var(--nav-h));
    }

    /* ═══════════════════════════════════════════
       SIDEBAR KIRI
       Figma: 322×785, white, border-radius kanan 16px
    ═══════════════════════════════════════════ */
    .sidebar {
      width: var(--sidebar-w);        /* 322px */
      flex-shrink: 0;
      background: var(--white);
      border-radius: 0 16px 16px 0;
      min-height: calc(100vh - var(--nav-h));
      padding: 28px 16px 28px 12px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      box-shadow: 2px 0 8px rgba(0,0,0,0.07);
    }

    /* Header sidebar: lingkaran ikon + nama pelajaran */
    .sidebar-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 14px;
      padding: 0 4px;
    }

    /* Lingkaran ikon — Figma: 71×69, #FDF7C5, radius 9999, border 1px hitam */
    .sidebar-icon-wrap {
      width: 71px;
      height: 69px;
      flex-shrink: 0;
      background: var(--bg-cream);
      border-radius: 9999px;
      border: 1px solid var(--black);
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }
    /* ASSET 3 dalam lingkaran sidebar — tampil 54×54px */
    .sidebar-icon-wrap img { width: 54px; height: 54px; object-fit: contain; }

    /* Nama pelajaran — Figma: Inter 24px 600, hitam */
    .sidebar-nama {
      font-family: 'Inter', sans-serif;
      font-size: 24px;
      font-weight: 600;
      color: var(--black);
      line-height: 1.3;
    }

    /* Daftar item sidebar */
    .sidebar-list { display: flex; flex-direction: column; gap: 10px; }

    /* Item bab — Figma: 280×66, white, border 1px hitam, radius 16 */
    .sidebar-item {
      width: 280px;
      min-height: 66px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: var(--white);
      border: 1px solid var(--black);
      border-radius: 16px;
      padding: 10px 10px 10px 14px;
      cursor: pointer;
      text-decoration: none;
      transition: background 0.2s, border-color 0.2s;
    }
    .sidebar-item:hover, .sidebar-item.active {
      background: var(--bg-light);
      border-color: var(--aksen);
    }
    .sidebar-item-left { display: flex; align-items: center; gap: 10px; min-width: 0; }

    /* Nomor bulat — pink */
    .sidebar-num {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: var(--aksen);
      color: var(--white);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      font-weight: 700;
      flex-shrink: 0;
    }
    .sidebar-item.active .sidebar-num { background: var(--aksen-dark); }

    /* Judul bab teks */
    .sidebar-judul {
      font-size: 13px;
      font-weight: 500;
      color: #111;
      line-height: 1.35;
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
    }

    /* ASSET 3: ikon kecil kanan item — Figma: 69×69px */
    .sidebar-item-icon {
      width: 69px;
      height: 69px;
      object-fit: contain;
      flex-shrink: 0;
    }

    /* ═══════════════════════════════════════════
       AREA KONTEN KANAN
    ═══════════════════════════════════════════ */
    .content-area {
      flex: 1;
      min-width: 0;
      padding: 18px 24px 40px 16px;
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    /* Wrapper putih besar — Figma: 1069×813, white, radius 16 */
    .content-outer {
      background: var(--white);
      border-radius: 16px;
      padding: 22px 18px 18px;
      display: flex;
      flex-direction: column;
      gap: 0;
    }

    /* Baris judul materi — Figma: Inter 24px 600, hitam */
    .materi-title-bar {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 0 6px 16px;
    }
    /* ASSET 3 di title bar — 44×44px */
    .materi-title-bar img { width: 44px; height: 44px; object-fit: contain; }
    .materi-title-bar h1 {
      font-family: 'Inter', sans-serif;
      font-size: 24px;
      font-weight: 600;
      color: var(--black);
    }

    /* Kartu border hitam — Figma: 1041×644, white, border 1px black, radius 16
       Grid: kiri=teks panjang | kanan=judul+accordion */
    .content-card {
      background: var(--white);
      border: 1px solid var(--black);
      border-radius: 16px;
      padding: 28px 24px 24px;
      display: block;
    }

    /* ── KOLOM KIRI: teks isi materi ── */
    .materi-isi-wrap { display: flex; flex-direction: column; gap: 0; }

    /* Figma: Inter 20px 400, hitam, line-height 1.7 */
    .materi-isi-text {
      font-family: 'Inter', sans-serif;
      font-size: 20px;
      font-weight: 400;
      color: var(--black);
      line-height: 1.7;
    }

    /* ── KOLOM KANAN: disembunyikan ── */
    .materi-kanan { display: none; }

    .materi-judul-kanan { display: none; }

    /* Accordion */
    .acc-item {
      background: var(--white);
      border: 1px solid var(--aksen);
      border-radius: 16px;
      overflow: hidden;
    }
    .acc-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 14px 18px;
      cursor: pointer;
      user-select: none;
      transition: background 0.18s;
    }
    .acc-header:hover { background: var(--bg-light); }
    .acc-header-left { display: flex; align-items: center; gap: 10px; }
    .acc-num {
      width: 30px; height: 30px;
      border-radius: 50%;
      background: var(--aksen);
      display: flex; align-items: center; justify-content: center;
      font-size: 13px; font-weight: 700; color: var(--white);
      flex-shrink: 0;
    }
    .acc-title {
      font-family: 'Inter', sans-serif;
      font-size: 15px; font-weight: 700;
      color: var(--aksen);
    }
    .acc-arrow { font-size: 11px; color: var(--aksen); transition: transform 0.25s; }
    .acc-item.open .acc-arrow { transform: rotate(180deg); }
    .acc-body { max-height: 0; overflow: hidden; transition: max-height 0.35s ease; }
    .acc-item.open .acc-body { max-height: 800px; }
    .acc-isi {
      padding: 12px 18px 18px;
      border-top: 1px dashed var(--aksen);
      font-family: 'Actor', sans-serif;
      font-size: 14px; line-height: 1.75; color: #222;
    }
    .badge-soon {
      display: inline-block;
      background: #eee; border: 1px solid #ccc;
      border-radius: 6px; padding: 3px 10px;
      font-size: 13px; color: #888;
    }

    /* ═══════════════════════════════════════════
       4 KARTU BAWAH
       Figma: 207×353, white, border 1px hitam, radius 13
       Nomor: 52×50, #C5DE96, radius 9999, border hitam, Inter 20px 400
    ═══════════════════════════════════════════ */
    .bottom-cards {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 14px;
    }

    .bottom-card {
      background: var(--white);
      border: 1px solid var(--black);
      border-radius: 13px;
      min-height: 353px;
      padding: 0 0 20px 0;
      display: flex;
      flex-direction: column;
      position: relative;
      overflow: hidden;
    }

    /* Lingkaran nomor — Figma: 52×50, #C5DE96, radius 9999, border 1px hitam, pojok kiri atas */
    .bottom-card-num-wrap {
      position: absolute;
      top: -10px;
      left: -8px;
      width: 52px;
      height: 50px;
      background: var(--bg-kartu);      /* #C5DE96 */
      border-radius: 9999px;
      border: 1px solid var(--black);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Inter', sans-serif;
      font-size: 20px;
      font-weight: 400;
      color: var(--black);
    }

    .bottom-card-body {
      padding: 52px 16px 0;   /* ruang untuk lingkaran nomor */
      display: flex;
      flex-direction: column;
      gap: 10px;
      flex: 1;
    }

    .bottom-card-title {
      font-family: 'Inter', sans-serif;
      font-size: 15px; font-weight: 600;
      color: var(--aksen);
    }

    .bottom-card-text {
      font-family: 'Actor', sans-serif;
      font-size: 13px; line-height: 1.65;
      color: #333; flex: 1;
    }

    /* ═══════════════════════════════════════════
       NAVIGASI BAWAH
       Figma: 123×41, #EAEAEA, radius 30, Inter 15px 500
    ═══════════════════════════════════════════ */
    .nav-bottom {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .nav-bottom-btn {
      width: 123px;
      height: 41px;
      background: #EAEAEA;
      border-radius: 30px;
      border: none;
      font-family: 'Inter', sans-serif;
      font-size: 15px; font-weight: 500;
      color: var(--black);
      cursor: pointer;
      transition: background 0.18s, transform 0.15s;
    }
    .nav-bottom-btn:hover    { background: #d0d0d0; transform: translateY(-1px); }
    .nav-bottom-btn:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }

    .empty-box {
      text-align: center;
      padding: 60px 20px;
      font-family: 'Actor', sans-serif;
      font-size: 18px; color: #888;
    }

    /* ─── Responsive ──────────────────────────── */
    @media (max-width: 1024px) {
      .content-card { grid-template-columns: 1fr; }
      .bottom-cards { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
      .page-body { flex-direction: column; }
      .sidebar { width: 100%; min-height: auto; border-radius: 0 0 16px 16px; }
      .sidebar-item { width: 100%; }
      .bottom-cards { grid-template-columns: 1fr 1fr; }
      .nav-links { gap: 18px; }
      .nav-links a { font-size: 16px; }
    }
    @media (max-width: 480px) {
      .bottom-cards { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<!-- ══════════════════════════════════════════════════════════════
     NAVBAR — Figma: 1440×90, putih
     Kiri  : ASSET 1 Logo 169×60
     Tengah: Links Inter 24px 500
     Kanan : Tombol Kembali 186×62 #F9F383 radius 30 Inter 20px 700
══════════════════════════════════════════════════════════════ -->
<nav>
  <!-- ── ASSET 1: Logo navbar ────────────────────────────────
       Tampil: 169 × 60 px
       File  : ASSET/logo.png  (letakkan di folder ASSET/) -->
  <div class="nav-logo-wrap">
    <img class="nav-logo"
         src="<?= htmlspecialchars($asset_logo) ?>"
         alt="Logo"
         onerror="this.style.display='none'; this.nextElementSibling.style.display='block'"/>
    <span class="nav-logo-fallback">LOGO</span>
  </div>

  <!-- Links tengah -->
  <ul class="nav-links">
    <li><a href="home.php">Beranda</a></li>
    <li><a href="index.php">Kuis</a></li>
    <li><a href="leaderboard.php">Leaderboard</a></li>
    <li><a href="materi.php" class="active">Materi</a></li>
    <li><a href="tentang.php">Tentang</a></li>
  </ul>

  <!-- Tombol Kembali -->
  <a class="nav-kembali" href="materi.php">Kembali</a>
</nav>

<!-- ══════════════════════════════════════════════════════════════
     BODY UTAMA
══════════════════════════════════════════════════════════════ -->
<div class="page-body">

  <!-- ── SIDEBAR KIRI — Figma: 322×785, white, radius kanan 16 ── -->
  <aside class="sidebar">
    <!-- Header: lingkaran ikon (#FDF7C5, 71×69) + nama pelajaran (Inter 24px 600) -->
    <div class="sidebar-header">
      <!-- ASSET 3 dalam lingkaran — tampil 54×54px -->
      <div class="sidebar-icon-wrap">
        <img src="<?= htmlspecialchars($asset_icon) ?>"
             alt="Ikon Mapel"
             onerror="this.style.display='none'"/>
      </div>
      <!-- Nama pelajaran — Figma: Inter 24px 600, hitam -->
      <div class="sidebar-nama"><?= htmlspecialchars($materi['nama_materi']) ?></div>
    </div>

    <!-- Daftar bab — Figma: 280×66, border 1px hitam, radius 16 -->
    <div class="sidebar-list">
      <?php foreach ($konten_list as $idx => $k): ?>
      <a class="sidebar-item <?= $idx === 0 ? 'active' : '' ?>"
         href="#konten-<?= $idx ?>"
         data-idx="<?= $idx ?>">
        <div class="sidebar-item-left">
          <div class="sidebar-num"><?= $idx + 1 ?></div>
          <div class="sidebar-judul"><?= htmlspecialchars($k['judul']) ?></div>
        </div>
        <!-- ASSET 3: ikon kanan sidebar — Figma: 69×69px -->
        <img class="sidebar-item-icon"
             src="<?= htmlspecialchars($asset_icon) ?>"
             alt=""
             onerror="this.style.display='none'"/>
      </a>
      <?php endforeach; ?>
    </div>
  </aside>

  <!-- ── AREA KONTEN KANAN ── -->
  <main class="content-area">

    <!-- Wrapper putih besar — Figma: 1069×813, white, radius 16 -->
    <div class="content-outer">

      <!-- Judul materi — Figma: Inter 24px 600 + ASSET 3 44×44 -->
      <div class="materi-title-bar">
        <!-- ASSET 3 di title bar — 44×44px -->
        <img src="<?= htmlspecialchars($asset_icon) ?>"
             alt="<?= htmlspecialchars($materi['nama_materi']) ?>"
             onerror="this.style.display='none'"/>
        <h1><?= htmlspecialchars($materi['nama_materi']) ?></h1>
      </div>

      <!-- Kartu border hitam — Figma: 1041×644, border 1px black, radius 16
           Kiri: teks isi panjang (Inter 20px 400) | Kanan: judul + accordion -->
      <div class="content-card">

        <!-- KOLOM KIRI: isi materi aktif -->
        <div class="materi-isi-wrap">
          <?php if (empty($konten_list)): ?>
            <div class="empty-box">Konten materi belum tersedia.</div>
          <?php else: ?>
            <?php foreach ($konten_list as $idx => $k):
              $belum = str_contains($k['isi'], 'Segera hadir') || str_contains($k['isi'], 'dalam persiapan');
            ?>
            <div class="materi-isi-block"
                 data-idx="<?= $idx ?>"
                 style="<?= $idx !== 0 ? 'display:none;' : '' ?>">
              <?php if ($belum): ?>
                <span class="badge-soon">⏳ Materi ini sedang dalam persiapan</span>
              <?php else: ?>
                <!-- Figma: teks isi Inter 20px 400, hitam, center di kolom kiri -->
                <p class="materi-isi-text"><?= htmlspecialchars($k['isi']) ?></p>

                <!-- Tombol Contoh — Figma: 204×41, white, border 1px #0B0A0A, radius 30, Inter 20px 400 -->
                <?php if (!empty($k['contoh'])): ?>
                <div class="contoh-wrap" style="margin-top:20px;">
                  <button class="contoh-toggle-btn" type="button"
                    style="display:inline-flex;align-items:center;justify-content:center;
                           width:204px;height:41px;background:white;border:1px solid #0B0A0A;
                           border-radius:30px;font-family:'Inter',sans-serif;font-size:20px;
                           font-weight:400;color:#000;cursor:pointer;transition:background .2s;">
                    Contoh
                  </button>
                  <div class="contoh-section" style="display:none;margin-top:12px;">
                    <div style="font-family:'Inter',sans-serif;font-size:12px;font-weight:700;
                                color:var(--aksen);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">
                      Contoh:
                    </div>
                    <ul style="list-style:none;display:flex;flex-direction:column;gap:7px;">
                      <?php foreach ($k['contoh'] as $c): ?>
                        <li style="background:var(--bg-light);border:1px solid var(--aksen);
                                   border-radius:8px;padding:8px 14px;
                                   font-family:'Actor',sans-serif;font-size:14px;">
                          📌 <?= htmlspecialchars($c['contoh']) ?>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                </div>
                <?php endif; ?>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- KOLOM KANAN: judul aktif + accordion -->
        <div class="materi-kanan">
          <!-- Judul bab aktif — Figma: Inter 24px 600, hitam, pojok kanan atas kartu -->
          <div class="materi-judul-kanan" id="judul-aktif">
            <?= htmlspecialchars($konten_list[0]['judul'] ?? '') ?>
          </div>

          <!-- Accordion daftar bab -->
          <?php foreach ($konten_list as $idx => $k):
            $belum = str_contains($k['isi'], 'Segera hadir') || str_contains($k['isi'], 'dalam persiapan');
          ?>
          <div class="acc-item <?= $idx === 0 ? 'open' : '' ?>"
               id="konten-<?= $idx ?>">
            <div class="acc-header">
              <div class="acc-header-left">
                <div class="acc-num"><?= $idx + 1 ?></div>
                <div class="acc-title"><?= htmlspecialchars($k['judul']) ?></div>
              </div>
              <span class="acc-arrow">▾</span>
            </div>
            <div class="acc-body">
              <div class="acc-isi">
                <?php if ($belum): ?>
                  <span class="badge-soon">⏳ Sedang dalam persiapan</span>
                <?php else: ?>
                  <?= htmlspecialchars(mb_substr($k['isi'], 0, 120)) ?>…
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

      </div><!-- /content-card -->
    </div><!-- /content-outer -->

    <!-- ══════════════════════════════════════════════════════════
         4 KARTU BAWAH
         Figma: 207×353, white, border 1px hitam, radius 13
         Lingkaran nomor: 52×50, #C5DE96, radius 9999, border hitam
    ══════════════════════════════════════════════════════════ -->
    <div class="bottom-cards">
      <?php foreach ($konten_list as $idx => $k): if ($idx >= 4) break; ?>
      <div class="bottom-card">
        <!-- Lingkaran nomor — #C5DE96, 52×50, pojok kiri atas -->
        <div class="bottom-card-num-wrap"><?= $idx + 1 ?></div>
        <div class="bottom-card-body">
          <div class="bottom-card-title"><?= htmlspecialchars($k['judul']) ?></div>
          <div class="bottom-card-text">
            <?= htmlspecialchars(mb_substr($k['isi'], 0, 160)) ?>…
          </div>
        </div>
      </div>
      <?php endforeach; ?>

      <?php for ($i = count($konten_list); $i < 4; $i++): ?>
      <div class="bottom-card">
        <div class="bottom-card-num-wrap"><?= $i + 1 ?></div>
        <div class="bottom-card-body">
          <div class="bottom-card-title" style="color:#ccc;">—</div>
          <div class="bottom-card-text" style="color:#ddd;">Belum tersedia</div>
        </div>
      </div>
      <?php endfor; ?>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         NAVIGASI BAWAH
         Figma: 123×41, #EAEAEA, radius 30, Inter 15px 500
         Kiri: sebelumnya | Kanan: selanjutnya
    ══════════════════════════════════════════════════════════ -->
    <div class="nav-bottom">
      <button class="nav-bottom-btn" id="btn-prev"
              onclick="navigasiKonten(-1)" disabled>
        sebelumnya
      </button>
      <button class="nav-bottom-btn" id="btn-next"
              onclick="navigasiKonten(1)">
        selanjutnya
      </button>
    </div>

  </main>
</div><!-- /page-body -->

<script>
  const totalKonten = <?= $total_konten ?>;
  let currentIdx = 0;

  /* Klik accordion → tampilkan isi di kolom kiri */
  document.querySelectorAll('.acc-header').forEach((btn, i) => {
    btn.addEventListener('click', () => {
      const item = btn.parentElement;
      const isOpen = item.classList.contains('open');
      document.querySelectorAll('.acc-item.open').forEach(el => el.classList.remove('open'));
      if (!isOpen) { item.classList.add('open'); updateActive(i); }
    });
  });

  /* Toggle tombol Contoh */
  document.querySelectorAll('.contoh-toggle-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const sec = btn.nextElementSibling;
      const visible = sec.style.display !== 'none';
      sec.style.display = visible ? 'none' : 'block';
      btn.textContent = visible ? 'Contoh' : 'Sembunyikan';
    });
  });

  /* Update state aktif */
  function updateActive(idx) {
    currentIdx = idx;

    /* Sidebar highlight */
    document.querySelectorAll('.sidebar-item').forEach((el, i) =>
      el.classList.toggle('active', i === idx));

    /* Prev/next */
    document.getElementById('btn-prev').disabled = idx === 0;
    document.getElementById('btn-next').disabled = idx === totalKonten - 1;

    /* Tampilkan blok isi kolom kiri */
    document.querySelectorAll('.materi-isi-block').forEach((el, i) => {
      el.style.display = i === idx ? 'block' : 'none';
      const btn = el.querySelector('.contoh-toggle-btn');
      const sec = el.querySelector('.contoh-section');
      if (btn) btn.textContent = 'Contoh';
      if (sec) sec.style.display = 'none';
    });

    /* Update judul kanan atas */
    const judulEls  = document.querySelectorAll('.acc-title');
    const judulAktif = document.getElementById('judul-aktif');
    if (judulAktif && judulEls[idx]) judulAktif.textContent = judulEls[idx].textContent;

    /* Accordion */
    const items = document.querySelectorAll('.acc-item');
    items.forEach(el => el.classList.remove('open'));
    if (items[idx]) items[idx].classList.add('open');
  }

  /* Klik sidebar */
  document.querySelectorAll('.sidebar-item').forEach((el, i) => {
    el.addEventListener('click', e => {
      e.preventDefault();
      updateActive(i);
      document.querySelectorAll('.acc-item')[i]
        ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  /* Tombol prev/next */
  function navigasiKonten(arah) {
    const next = currentIdx + arah;
    if (next >= 0 && next < totalKonten) {
      updateActive(next);
      document.querySelectorAll('.acc-item')[next]
        ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  /* Init */
  updateActive(0);
</script>
</body>
</html>