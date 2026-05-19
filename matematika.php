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

// ── PENANDA ASSET ──────────────────────
// Ganti src icon Matematika:
// $icon = '../ASSET/matematika.png';
// Ukuran: 90×90 px
// ───────────────────────────────────────
$icon = 'ASSET/matematika.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Matematika – Kelompok 5</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=ADLaM+Display&family=Actor&display=swap" rel="stylesheet"/>
  <style>
    /*
    ══════════════════════════════════════
     MATEMATIKA — Tema Biru
     Edit warna di sini untuk ganti tema
    ══════════════════════════════════════
    */
    :root {
      --aksen:     #4A90D9;   /* biru */
      --bg-light:  #E8F4FF;
      --bg-card:   #EFF8FF;
      --btn-hover: #3178C6;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      min-height: 100vh;
      background: linear-gradient(180deg, #FFFFFE 0%, var(--bg-light) 50%, #FFFDD1 100%);
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
    .nav-links a.active { color: var(--aksen); }
    .nav-links a:hover  { color: var(--aksen); }
    .nav-masuk {
      display: flex; align-items: center; gap: 10px;
      background: var(--bg-light); border-radius: 30px; padding: 8px 20px 8px 8px;
    }
    .nav-masuk img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; }
    .nav-masuk span { font-size: 20px; font-weight: 700; }

    .wrapper { max-width: 900px; margin: 0 auto; padding: 30px 24px 80px; }

    .back-btn {
      display: inline-flex; align-items: center; gap: 8px;
      background: var(--aksen); border: 1px solid #000; border-radius: 9px;
      padding: 7px 18px; font-family: 'Actor', sans-serif;
      font-size: 15px; color: #fff; text-decoration: none;
      margin-bottom: 28px; transition: background .2s;
    }
    .back-btn:hover { background: var(--btn-hover); }

    .materi-header {
      display: flex; align-items: center; gap: 22px;
      background: var(--bg-light); border: 2px solid var(--aksen); border-radius: 16px;
      padding: 20px 28px; margin-bottom: 36px;
    }
    .materi-header img { width: 90px; height: 90px; object-fit: contain; border-radius: 8px; }
    .materi-header h1 { font-family: 'ADLaM Display', sans-serif; font-size: 32px; color: var(--aksen); }
    .materi-header p  { font-family: 'Actor', sans-serif; font-size: 15px; margin-top: 6px; color: #333; }

    .accordion { display: flex; flex-direction: column; gap: 16px; }
    .acc-item { background: var(--bg-card); border: 1px solid var(--aksen); border-radius: 11px; overflow: hidden; }
    .acc-header {
      display: flex; justify-content: space-between; align-items: center;
      padding: 16px 22px; cursor: pointer; user-select: none; transition: background .2s;
    }
    .acc-header:hover { background: var(--bg-light); }
    .acc-header-left { display: flex; align-items: center; gap: 12px; }
    .acc-num {
      background: var(--aksen); border-radius: 50%;
      width: 30px; height: 30px; display: flex; align-items: center;
      justify-content: center; font-size: 14px; font-weight: 700;
      color: #fff; flex-shrink: 0;
    }
    .acc-title { font-family: 'ADLaM Display', sans-serif; font-size: 18px; color: var(--aksen); }
    .acc-arrow { font-size: 18px; color: var(--aksen); transition: transform .25s; }
    .acc-item.open .acc-arrow { transform: rotate(180deg); }
    .acc-body { max-height: 0; overflow: hidden; transition: max-height .35s ease; padding: 0 22px; }
    .acc-item.open .acc-body { max-height: 800px; padding: 0 22px 20px; }
    .acc-isi {
      font-family: 'Actor', sans-serif; font-size: 15px; line-height: 1.75;
      border-top: 1px dashed var(--aksen); padding-top: 14px;
    }
    .badge-soon {
      display: inline-block; background: #eee; border: 1px solid #ccc;
      border-radius: 6px; padding: 3px 10px; font-size: 13px; color: #888;
    }
    .contoh-label { font-weight: 700; font-size: 13px; color: var(--aksen); margin: 14px 0 6px; text-transform: uppercase; }
    .contoh-list { list-style: none; display: flex; flex-direction: column; gap: 6px; }
    .contoh-list li {
      background: var(--bg-light); border: 1px solid var(--aksen);
      border-radius: 8px; padding: 7px 14px;
      font-family: 'Actor', sans-serif; font-size: 14px;
    }
    .empty-box { text-align: center; padding: 60px 20px; font-family: 'Actor', sans-serif; font-size: 18px; color: #888; }
  </style>
</head>
<body>

<nav>
  <img class="nav-logo" src="assets/logo.png" alt="Logo" onerror="this.style.visibility='hidden'"/>
  <ul class="nav-links">
    <li><a href="home.php">Beranda</a></li>
    <li><a href="index.php">Kuis</a></li>
    <li><a href="leaderboard.php">Leaderboard</a></li>
    <li><a href="materi.php" class="active">Materi</a></li>
    <li><a href="tentang.php">Tentang</a></li>
  </ul>
  <div class="nav-masuk">
    <img src="assets/avatar.png" alt="User" onerror="this.style.display='none'"/>
    <span>Masuk</span>
  </div>
</nav>

<div class="wrapper">
  <a class="back-btn" href="materi.php">← Kembali ke Daftar Materi</a>

  <div class="materi-header">
    <img src="<?= htmlspecialchars($icon) ?>" alt="Matematika" onerror="this.style.display='none'"/>
    <div>
      <h1><?= htmlspecialchars($materi['nama_materi']) ?></h1>
      <p><?= htmlspecialchars($materi['deskripsi']) ?></p>
    </div>
  </div>

  <?php if (empty($konten_list)): ?>
    <div class="empty-box">Konten materi belum tersedia.</div>
  <?php else: ?>
  <div class="accordion">
    <?php foreach ($konten_list as $idx => $k): ?>
    <?php $belum = str_contains($k['isi'], 'Segera hadir') || str_contains($k['isi'], 'dalam persiapan'); ?>
    <div class="acc-item <?= $idx === 0 ? 'open' : '' ?>">
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
            <span class="badge-soon">⏳ Materi ini sedang dalam persiapan</span>
          <?php else: ?>
            <p><?= nl2br(htmlspecialchars($k['isi'])) ?></p>
            <?php if (!empty($k['contoh'])): ?>
              <div class="contoh-label">Contoh:</div>
              <ul class="contoh-list">
                <?php foreach ($k['contoh'] as $c): ?>
                  <li>📌 <?= htmlspecialchars($c['contoh']) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<script>
  document.querySelectorAll('.acc-header').forEach(btn => {
    btn.addEventListener('click', () => {
      const item = btn.parentElement;
      const isOpen = item.classList.contains('open');
      document.querySelectorAll('.acc-item.open').forEach(el => el.classList.remove('open'));
      if (!isOpen) item.classList.add('open');
    });
  });
</script>
</body>
</html>