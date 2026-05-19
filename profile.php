<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id  = $_SESSION['user_id'];
$nickname = $_SESSION['nickname'] ?? 'Pengguna';

// Ambil inisial dari nickname (maks 2 huruf)
$words    = explode(' ', trim($nickname));
$inisial  = strtoupper(mb_substr($words[0], 0, 1));
if (count($words) > 1) $inisial .= strtoupper(mb_substr($words[1], 0, 1));

// Ambil kelas
$stmt_kelas = $pdo->prepare("SELECT kelas FROM users WHERE id = ?");
$stmt_kelas->execute([$user_id]);
$kelas = $stmt_kelas->fetchColumn() ?? '-';

// Riwayat per level
$stmt = $pdo->prepare("
    SELECT l.name        AS level_name,
           l.icon,
           l.color,
           COUNT(qs.id)      AS total_played,
           AVG(qs.percentage) AS avg_score,
           MAX(qs.percentage) AS best_score,
           SUM(qs.score)      AS total_points
    FROM quiz_sessions qs
    JOIN levels l ON qs.level_id = l.id
    WHERE qs.user_id = ? AND qs.status = 'completed'
    GROUP BY l.id
    ORDER BY l.id
");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total statistik
$stmt_total = $pdo->prepare("
    SELECT COUNT(*)        AS total_quiz,
           SUM(score)      AS total_points,
           AVG(percentage) AS avg_percentage
    FROM quiz_sessions
    WHERE user_id = ? AND status = 'completed'
");
$stmt_total->execute([$user_id]);
$totals = $stmt_total->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Profil — <?= htmlspecialchars($nickname) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=Itim&family=Jersey+25&family=Josefin+Sans:wght@400;600&family=Jockey+One&display=swap" rel="stylesheet"/>
  <style>
    /* ══════════════════════════════════════
       RESET & BASE
    ══════════════════════════════════════ */
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      min-height: 100vh;
      background: linear-gradient(180deg,
        rgba(255,250,250,.20) 0%,
        rgba(249,243,131,.20) 50%,
        rgba(255,252,209,.20) 100%), #FFFDD1;
      font-family: 'Inter', sans-serif;
      overflow-x: hidden;
    }

    /* ══════════════════════════════════════
       NAVBAR
    ══════════════════════════════════════ */
    nav {
      width: 100%; height: 90px;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 40px;
      position: relative; z-index: 10;
    }

    .nav-logo {
      display: flex; align-items: center; gap: 10px; text-decoration: none;
    }
    /* ── PENANDA ASSET ──────────────────────
       Ganti src logo di sini:
       <img src="ASSET/logo.png" ...>
    ─────────────────────────────────────── */
    .nav-logo img {
      width: 164px; height: 60px; object-fit: contain;
    }

    .nav-links {
      display: flex; align-items: center; gap: 40px; list-style: none;
    }
    .nav-links a {
      color: #000; font-size: 24px; font-weight: 500;
      text-decoration: none; transition: color .2s;
    }
    .nav-links a.active { color: #FFC300; }
    .nav-links a:hover  { color: #FFC300; }

    /* ── PENANDA ASSET ──────────────────────
       Tombol keluar / avatar — ganti href logout
    ─────────────────────────────────────── */
    .nav-keluar {
      background: #F9F383; border-radius: 30px;
      padding: 8px 24px; font-size: 20px; font-weight: 700;
      color: #000; text-decoration: none; transition: background .2s;
    }
    .nav-keluar:hover { background: #FFD600; }

    /* ══════════════════════════════════════
       LAYOUT UTAMA
    ══════════════════════════════════════ */
    .page-wrap {
      max-width: 1340px; margin: 0 auto;
      padding: 0 40px 60px;
      display: flex; flex-direction: column; gap: 28px;
    }

    /* ══════════════════════════════════════
       KARTU PROFIL + STATISTIK
    ══════════════════════════════════════ */
    .top-card {
      background: #fff;
      border: 1px solid #000;
      border-radius: 30px;
      padding: 32px 48px;
      display: flex;
      align-items: center;
      gap: 48px;
      box-shadow: 0px 4px 4px #F9F383;
    }

    /* Avatar lingkaran dengan inisial */
    .avatar-circle {
      width: 189px; height: 189px; flex-shrink: 0;
      background: #F9F383;
      border: 1px solid #000;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-family: 'Itim', cursive;
      font-size: 72px;
      color: #000;
      user-select: none;
    }

    .profile-info {
      display: flex; flex-direction: column; gap: 6px;
    }
    .profile-info .name {
      font-family: 'Itim', cursive;
      font-size: 32px; color: #000;
    }
    .profile-info .kelas {
      font-family: 'Itim', cursive;
      font-size: 24px; color: #444;
    }

    /* Divider vertikal */
    .vdiv {
      width: 1px; align-self: stretch;
      background: #000; flex-shrink: 0;
    }

    /* Stat kolom */
    .stats-group {
      display: flex; gap: 0; flex: 1;
    }
    .stat-col {
      flex: 1; display: flex; flex-direction: column;
      align-items: center; justify-content: center; gap: 8px;
      padding: 16px;
      border-right: 1px solid #000;
    }
    .stat-col:last-child { border-right: none; }
    .stat-label {
      font-family: 'Itim', cursive;
      font-size: 28px; color: #000;
    }
    .stat-value {
      font-family: 'Jersey 25', cursive;
      font-size: 64px; color: #000; line-height: 1;
    }

    /* ══════════════════════════════════════
       RIWAYAT PER LEVEL
    ══════════════════════════════════════ */
    .history-card {
      background: #fff;
      border: 1px solid #000;
      border-radius: 30px;
      padding: 32px 48px;
      box-shadow: 0px 4px 4px #F9F383;
    }

    .history-title {
      font-family: 'Jersey 25', cursive;
      font-size: 40px; font-weight: 400;
      margin-bottom: 24px;
    }

    /* Baris per level */
    .level-row {
      background: #FCFCFC;
      border: 1px solid #FF8D8D;
      border-radius: 30px;
      padding: 24px 32px;
      display: flex;
      align-items: center;
      gap: 24px;
      margin-bottom: 20px;
    }

    /* Divider vertikal di dalam row */
    .row-vdiv {
      width: 1px; height: 77px;
      background: #000; flex-shrink: 0;
    }

    /* Icon / gambar level */
    .level-icon-wrap {
      width: 105px; height: 70px; flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
      font-size: 48px;
    }
    /* ── PENANDA ASSET ──────────────────────
       Ganti icon level dengan gambar nyata:
       <img src="ASSET/icon_pemula.png" ...>
       <img src="ASSET/icon_ahli.png"   ...>
       Ukuran: 105×70 px
    ─────────────────────────────────────── */

    /* Label level */
    .level-label {
      display: flex; flex-direction: column; justify-content: center;
      min-width: 130px;
    }
    .level-label .lv-name {
      font-family: 'Jersey 25', cursive;
      font-size: 20px; color: #000;
    }

    /* Stat-stat dalam baris */
    .row-stats {
      display: flex; gap: 48px; flex: 1; align-items: center;
    }
    .row-stat {
      display: flex; flex-direction: column; gap: 2px;
    }
    .row-stat .rs-label {
      font-family: 'Josefin Sans', sans-serif;
      font-size: 20px; color: #000;
    }
    .row-stat .rs-value {
      font-family: 'Jockey One', sans-serif;
      font-size: 24px; color: #000;
    }

    /* Pencapaian */
    .row-pencapaian {
      display: flex; flex-direction: column; gap: 4px; flex: 1;
    }
    .pencapaian-wrap {
      display: flex; align-items: center; gap: 12px;
    }
    /* ── PENANDA ASSET ──────────────────────
       Ganti src ikon pencapaian:
       <img src="ASSET/icon_pencapaian.png" ...>
       Ukuran: 51×51 px
    ─────────────────────────────────────── */
    .pencapaian-wrap img {
      width: 51px; height: 51px; object-fit: contain;
    }
    .pencapaian-wrap .pc-icon-placeholder {
      width: 51px; height: 51px;
      background: #F9F383; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 24px;
    }
    .pencapaian-text .pc-title {
      font-family: 'Josefin Sans', sans-serif;
      font-size: 20px; font-weight: 600;
    }
    .pencapaian-text .pc-desc {
      font-family: 'Josefin Sans', sans-serif;
      font-size: 16px; color: #333;
    }

    /* Tombol main lagi */
    .btn-main-lagi {
      display: inline-flex; align-items: center; justify-content: center;
      width: 137px; height: 44px; border-radius: 24px;
      font-family: 'Josefin Sans', sans-serif;
      font-size: 18px; color: #000;
      text-decoration: none; flex-shrink: 0;
      transition: opacity .2s;
    }
    .btn-main-lagi:hover { opacity: .8; }
    .btn-main-lagi.yellow { background: #FFF045; }
    .btn-main-lagi.red    { background: #F48480; }

    /* ── PENANDA ASSET ──────────────────────
       Gambar dekorasi pojok kanan atas kartu level:
       <img src="ASSET/dekorasi_pemula.png" ...>   207×138 px
       <img src="ASSET/dekorasi_ahli.png"   ...>   106×106 px
    ─────────────────────────────────────── */
    .row-deco {
      width: 105px; flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
    }
    .row-deco .deco-placeholder {
      width: 105px; height: 70px;
      background: #FFFDD1; border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 32px;
    }

    /* Kosong state */
    .empty-state {
      text-align: center; padding: 40px 0;
      font-family: 'Josefin Sans', sans-serif;
      font-size: 18px; color: #666;
    }
    .empty-state a {
      color: #000; font-weight: 700;
      border-bottom: 1.5px solid #F9F383;
      text-decoration: none;
    }

    /* ── Overlay blur dekorasi bg (sesuai Figma) ── */
    .bg-blur-deco {
      position: fixed; inset: 0; pointer-events: none; z-index: 0;
    }
    .bg-blur-deco::after {
      content: '';
      position: absolute;
      left: 22px; top: 271px;
      width: 1434px; height: 627px;
      box-shadow: 0px 4px 4px #F9F383, 0px 4px 4px #F9F383;
      outline: 2px solid #FFFAFA;
      filter: blur(2px);
      border-radius: 4px;
    }
  </style>
</head>
<body>

<div class="bg-blur-deco"></div>

<!-- ════════════════════════════════
     NAVBAR
════════════════════════════════ -->
<nav>
  <!-- ── PENANDA ASSET: ganti src ke ASSET/logo.png ── -->
  <a class="nav-logo" href="index.php">
    <img src="ASSET/logo.png" alt="Logo" onerror="this.style.display='none'"/>
  </a>

  <ul class="nav-links">
    <li><a href="index.php">Beranda</a></li>
    <li><a href="kuis.php">Kuis</a></li>
    <li><a href="leaderboard.php">Leaderboard</a></li>
    <li><a href="materi.php">Materi</a></li>
    <li><a href="tentang.php">Tentang</a></li>
  </ul>

  <a class="nav-keluar" href="logout.php">Keluar</a>
</nav>

<!-- ════════════════════════════════
     KONTEN
════════════════════════════════ -->
<div class="page-wrap">

  <!-- ── KARTU PROFIL + STATISTIK ── -->
  <div class="top-card">

    <!-- Avatar dengan inisial -->
    <div class="avatar-circle">
      <?= htmlspecialchars($inisial) ?>
    </div>

    <!-- Nama & Kelas -->
    <div class="profile-info">
      <div class="name"><?= htmlspecialchars($nickname) ?></div>
      <div class="kelas">Kelas <?= htmlspecialchars($kelas) ?></div>
    </div>

    <!-- Divider -->
    <div class="vdiv"></div>

    <!-- Tiga kolom statistik -->
    <div class="stats-group">
      <div class="stat-col">
        <div class="stat-label">Total Quiz</div>
        <div class="stat-value"><?= (int)($totals['total_quiz'] ?? 0) ?></div>
      </div>
      <div class="vdiv"></div>
      <div class="stat-col">
        <div class="stat-label">Total Point</div>
        <div class="stat-value"><?= number_format($totals['total_points'] ?? 0) ?></div>
      </div>
      <div class="vdiv"></div>
      <div class="stat-col">
        <div class="stat-label">Rata - rata</div>
        <div class="stat-value"><?= round($totals['avg_percentage'] ?? 0, 0) ?>%</div>
      </div>
    </div>

  </div><!-- /top-card -->

  <!-- ── RIWAYAT PER LEVEL ── -->
  <div class="history-card">
    <div class="history-title">Riwayat Per level</div>

    <?php if (empty($history)): ?>
      <div class="empty-state">
        Belum ada riwayat quiz 😢<br/>
        <a href="index.php">Main quiz sekarang!</a>
      </div>

    <?php else: ?>
      <?php
      // Warna tombol bergantian: kuning → merah → kuning → ...
      $btn_colors = ['yellow', 'red'];

      // Teks pencapaian sederhana berdasarkan best_score
      function pencapaian_text($best) {
          if ($best >= 80) return ['Pencapaian Terbaik', 'Selesaikan 1 level dengan poin 80%'];
          if ($best >= 40) return ['Pencapaian Terbaik', 'Selesaikan 1 level dengan poin 40%'];
          return ['Pencapaian Terbaik', 'Selesaikan 1 level di Quiz ini'];
      }
      ?>

      <?php foreach ($history as $i => $level): ?>
      <?php [$pc_title, $pc_desc] = pencapaian_text($level['best_score']); ?>

      <div class="level-row">

        <!-- Icon / gambar level -->
        <div class="level-icon-wrap">
          <?php if (!empty($level['icon'])): ?>
            <!-- ── PENANDA ASSET: icon level dari database atau folder ASSET/ ── -->
            <img src="ASSET/<?= htmlspecialchars($level['icon']) ?>"
                 alt="<?= htmlspecialchars($level['level_name']) ?>"
                 style="max-width:105px; max-height:70px; object-fit:contain;"
                 onerror="this.style.display='none'"/>
          <?php else: ?>
            <span style="font-size:40px;">🎮</span>
          <?php endif; ?>
        </div>

        <!-- Divider -->
        <div class="row-vdiv"></div>

        <!-- Nama level -->
        <div class="level-label">
          <div class="lv-name"><?= htmlspecialchars($level['level_name']) ?></div>
        </div>

        <!-- Divider -->
        <div class="row-vdiv"></div>

        <!-- Statistik baris -->
        <div class="row-stats">
          <div class="row-stat">
            <span class="rs-label">Dimainkan</span>
            <span class="rs-value"><?= (int)$level['total_played'] ?>x</span>
          </div>
          <div class="row-stat">
            <span class="rs-label">Skor Terbaik</span>
            <span class="rs-value"><?= round($level['best_score'], 0) ?>%</span>
          </div>
          <div class="row-stat">
            <span class="rs-label">Rata Rata</span>
            <span class="rs-value"><?= round($level['avg_score'], 0) ?>%</span>
          </div>
          <div class="row-stat">
            <span class="rs-label">Total Point</span>
            <span class="rs-value"><?= number_format($level['total_points']) ?> pts</span>
          </div>
        </div>

        <!-- Divider -->
        <div class="row-vdiv"></div>

        <!-- Pencapaian + tombol -->
        <div style="display:flex; flex-direction:column; gap:16px; min-width:260px;">

          <!-- Pencapaian -->
          <div class="pencapaian-wrap">
            <div class="pc-icon-placeholder">
              <!-- ── PENANDA ASSET ──────────────────────
                   Ganti dengan:
                   <img src="ASSET/icon_pencapaian.png" ...>
                   Ukuran: 51×51 px
              ─────────────────────────────────────── -->
              🏅
            </div>
            <div class="pencapaian-text">
              <div class="pc-title"><?= htmlspecialchars($pc_title) ?></div>
              <div class="pc-desc"><?= htmlspecialchars($pc_desc) ?></div>
            </div>
          </div>

          <!-- Tombol main lagi -->
          <a href="kuis.php?level=<?= urlencode($level['level_name']) ?>"
             class="btn-main-lagi <?= $btn_colors[$i % 2] ?>">
            Main lagi &nbsp;›
          </a>

        </div>

        <!-- Dekorasi pojok kanan -->
        <div class="row-deco">
          <?php if (!empty($level['icon'])): ?>
            <!-- ── PENANDA ASSET ──────────────────────
                 Ganti dengan gambar dekorasi per level:
                 <img src="ASSET/deko_<?= $level['icon'] ?>" ...>
                 Ukuran: 105×70 px (pemula) atau 106×106 px (ahli)
            ─────────────────────────────────────── -->
          <?php endif; ?>
          <div class="deco-placeholder">
            <?= $i === 0 ? '⭐' : '🔥' ?>
          </div>
        </div>

      </div><!-- /level-row -->
      <?php endforeach; ?>

    <?php endif; ?>

  </div><!-- /history-card -->

</div><!-- /page-wrap -->

</body>
</html>