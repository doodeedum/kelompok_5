<?php 
session_start();
include 'config/database.php'; 

$leaderboard = $pdo->query("
    SELECT u.nickname, u.kelas,
           SUM(qs.score)          as total_points,
           COUNT(qs.id)           as total_quiz,
           AVG(qs.percentage)     as avg_percentage,
           MAX(qs.percentage)     as best_score,
           GROUP_CONCAT(DISTINCT l.name ORDER BY l.id SEPARATOR ', ') as levels_played
    FROM users u
    LEFT JOIN quiz_sessions qs ON u.id = qs.user_id AND qs.status = 'completed'
    LEFT JOIN levels l ON qs.level_id = l.id
    GROUP BY u.id
    HAVING total_points > 0
    ORDER BY total_points DESC, avg_percentage DESC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);

$session_user_rank = null;
if (isset($_SESSION['user_id'])) {
    $rank_stmt = $pdo->prepare("
        SELECT FIND_IN_SET(total_points, GROUP_CONCAT(total_points ORDER BY total_points DESC)) as rank,
               total_points
        FROM (
            SELECT u.id, SUM(qs.score) as total_points
            FROM users u
            LEFT JOIN quiz_sessions qs ON u.id = qs.user_id AND qs.status = 'completed'
            GROUP BY u.id
            HAVING total_points > 0
        ) ranked
        WHERE id = ?
    ");
    $rank_stmt->execute([$_SESSION['user_id']]);
    $session_user_rank = $rank_stmt->fetch();
}

function rankDisplay(int $i): string {
    return match($i) {
        0 => '🥇',
        1 => '🥈',
        2 => '🥉',
        default => (string)($i + 1),
    };
}

/* ── Avatar initials helper ── */
function initials(string $name): string {
    $parts = explode(' ', trim($name));
    $i = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) $i .= strtoupper(substr(end($parts), 0, 1));
    return $i;
}

/* ── Avatar colour classes (cycles by index) ── */
$avatarColors = ['av-blue','av-coral','av-teal','av-purple','av-amber','av-pink','av-green'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏆 Leaderboard — TahooGa</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kaisei+HarunoUmi:wght@400;700&family=Inter:wght@400;500;700&family=Itim&display=swap" rel="stylesheet">

    <!-- ══════════════════════════════════════════════════════════════════
         ASSET REFERENCES (update paths to match your project structure)
         ══════════════════════════════════════════════════════════════════
         • assets/logo.png           — TahooGa navbar logo (≈280×137 px)
         • assets/img/podium-bg.svg  — decorative podium illustration / hero BG
         • assets/img/star-deco.svg  — small star/sparkle scattered decoration
         • assets/img/trophy3d.png   — optional 3-D trophy illustration top-center
         • assets/css/style.css      — your global stylesheet (buttons, nav, etc.)
         • assets/css/leaderboard.css — this page's stylesheet (inline below, can be extracted)
    ══════════════════════════════════════════════════════════════════ -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* ═══════════════════════════════════════════════════════
           LEADERBOARD PAGE — inline styles
           (extract to assets/css/leaderboard.css if preferred)
           ═══════════════════════════════════════════════════════ */

        /* ── Page background gradient (matches Figma: #FFFFFE → #F9F383 → #FFFDD1) ── */
        body {
            background: linear-gradient(180deg, #FFFFFE 0%, #F9F383 50%, #FFFDD1 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }

        /* ── Scattered star decorations (pure CSS, no asset needed) ── */
        .lb-stars-deco {
            position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden;
        }
        .lb-stars-deco span {
            position: absolute;
            font-size: 20px;
            opacity: .18;
            animation: floatStar 8s ease-in-out infinite;
        }
        /* positions — add more to taste */
        .lb-stars-deco span:nth-child(1)  { left:5%;   top:12%; animation-delay:0s;   font-size:28px; }
        .lb-stars-deco span:nth-child(2)  { left:18%;  top:55%; animation-delay:1.2s; font-size:16px; }
        .lb-stars-deco span:nth-child(3)  { left:88%;  top:8%;  animation-delay:2.4s; font-size:22px; }
        .lb-stars-deco span:nth-child(4)  { left:93%;  top:60%; animation-delay:0.7s; font-size:18px; }
        .lb-stars-deco span:nth-child(5)  { left:50%;  top:5%;  animation-delay:3.1s; font-size:14px; }
        .lb-stars-deco span:nth-child(6)  { left:72%;  top:80%; animation-delay:1.9s; font-size:26px; }
        .lb-stars-deco span:nth-child(7)  { left:35%;  top:90%; animation-delay:0.4s; font-size:12px; }
        @keyframes floatStar {
            0%,100% { transform: translateY(0) rotate(0deg); }
            50%      { transform: translateY(-18px) rotate(20deg); }
        }

        /* ── Wrapper ── */
        .lb-page {
            position: relative; z-index: 1;
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 24px 80px;
        }

        /* ── Page header ── */
        .lb-page-header {
            text-align: center;
            margin-bottom: 48px;
        }
        .lb-page-header h1 {
            font-family: 'Kaisei HarunoUmi', serif;
            font-size: 56px;
            font-weight: 700;
            color: #111;
            margin: 0 0 8px;
            letter-spacing: -1px;
        }
        .lb-page-header p {
            font-size: 17px;
            color: #555;
            margin: 0;
        }

        /* ── PODIUM (top 3) ── */
        .lb-podium {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: 12px;
            margin-bottom: 48px;
        }
        .lb-podium-slot {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }
        /* avatar circle */
        .lb-podium-avatar {
            width: 72px; height: 72px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Kaisei HarunoUmi', serif;
            font-size: 22px; font-weight: 700;
            color: #fff;
            border: 3px solid rgba(0,0,0,.12);
            position: relative;
            box-shadow: 0 4px 12px rgba(0,0,0,.15);
        }
        .lb-podium-slot:nth-child(2) .lb-podium-avatar { width:90px; height:90px; font-size:28px; }
        /* crown on 1st place */
        .lb-podium-slot:nth-child(2) .lb-podium-avatar::before {
            content: '👑';
            position: absolute;
            top: -26px;
            font-size: 22px;
        }
        .lb-podium-block {
            display: flex; flex-direction: column; align-items: center;
            border-radius: 14px 14px 0 0;
            padding: 14px 22px 8px;
            border: 1.5px solid rgba(0,0,0,.1);
            min-width: 130px;
        }
        /* heights */
        .lb-podium-slot:nth-child(1) .lb-podium-block { height: 110px; background: #E8E8E8; }
        .lb-podium-slot:nth-child(2) .lb-podium-block { height: 150px; background: #FFF385; }
        .lb-podium-slot:nth-child(3) .lb-podium-block { height: 85px;  background: #F3C87C; }
        .lb-podium-block .medal { font-size: 28px; }
        .lb-podium-block .pod-name {
            font-family: 'Kaisei HarunoUmi', serif;
            font-size: 15px; font-weight: 700;
            color: #111; margin: 2px 0 0;
            max-width: 120px; text-align: center;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .lb-podium-block .pod-pts {
            font-size: 13px; color: #555; margin-top: 2px;
        }
        .lb-podium-block .pod-kelas {
            font-size: 11px; color: #888;
        }

        /* ── Your rank banner ── */
        .lb-your-rank {
            display: flex; align-items: center; gap: 14px;
            background: #FFF9B1;
            border: 1.5px solid #FFC300;
            border-radius: 14px;
            padding: 14px 24px;
            margin-bottom: 28px;
            font-family: 'Kaisei HarunoUmi', serif;
        }
        .lb-your-rank-label { font-size: 16px; color: #555; }
        .lb-your-rank-num   { font-size: 28px; font-weight: 700; color: #111; margin-left: auto; }
        .lb-your-rank-pts   { font-size: 16px; color: #555; }

        /* ── Main table card ── */
        .lb-card {
            background: linear-gradient(90deg, #FFF385 0%, #FFF7C0 50%, #FFFBFB 100%);
            border: 1.5px solid #000;
            border-radius: 35px;
            overflow: hidden;
            box-shadow: 4px 4px 0 rgba(0,0,0,.1);
        }

        /* ── Table header bar ── */
        .lb-table-header {
            background: #FFF9B1;
            border-bottom: 1px solid #000;
            border-radius: 14px 14px 0 0;
            padding: 14px 28px;
            display: grid;
            grid-template-columns: 70px 1fr 120px 120px 100px 100px minmax(0,160px);
            gap: 12px;
            font-family: 'Kaisei HarunoUmi', serif;
            font-size: 18px;
            font-weight: 700;
        }
        .lb-table-header .th-trophy { display:flex; align-items:center; gap:6px; }

        /* ── Table rows ── */
        .lb-table { width: 100%; border-collapse: collapse; }
        .lb-table tbody tr {
            display: grid;
            grid-template-columns: 70px 1fr 120px 120px 100px 100px minmax(0,160px);
            gap: 12px;
            align-items: center;
            padding: 12px 28px;
            border-bottom: 1px solid rgba(0,0,0,.08);
            transition: background .15s;
        }
        .lb-table tbody tr:last-child { border-bottom: none; }
        .lb-table tbody tr:hover { background: rgba(255,255,255,.5); }

        /* top 3 row highlight */
        .lb-table tbody tr.lb-top-1 { background: rgba(255,240,50,.35); }
        .lb-table tbody tr.lb-top-2 { background: rgba(230,230,230,.5); }
        .lb-table tbody tr.lb-top-3 { background: rgba(242,190,80,.25); }

        .lb-table td {
            font-family: 'Kaisei HarunoUmi', serif;
            font-size: 16px;
            color: #111;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }

        /* rank cell */
        .lb-rank-cell { text-align: center; font-size: 20px; font-weight: 700; }

        /* avatar + name */
        .lb-nick-cell { display: flex; align-items: center; gap: 10px; }
        .lb-nick-avatar {
            width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: #fff;
        }
        .lb-nick { font-size: 15px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* kelas badge */
        .lb-kelas {
            background: rgba(255,195,0,.25);
            border: 1px solid rgba(255,195,0,.6);
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 13px;
        }
        /* points */
        .lb-pts { font-weight: 700; font-size: 17px; color: #222; }

        /* levels */
        .lb-levels { font-size: 12px; color: #666; }

        /* ── Avatar colour palette ── */
        .av-blue   { background: #378ADD; }
        .av-coral  { background: #D85A30; }
        .av-teal   { background: #1D9E75; }
        .av-purple { background: #7F77DD; }
        .av-amber  { background: #BA7517; }
        .av-pink   { background: #D4537E; }
        .av-green  { background: #639922; }

        /* ── Stats strip ── */
        .lb-stats-strip {
            display: flex; gap: 16px; flex-wrap: wrap;
            margin-bottom: 28px;
        }
        .lb-stat-card {
            flex: 1; min-width: 140px;
            background: #FFF9B1;
            border: 1.5px solid rgba(0,0,0,.15);
            border-radius: 14px;
            padding: 16px 20px;
            text-align: center;
        }
        .lb-stat-card .sc-val {
            font-family: 'Kaisei HarunoUmi', serif;
            font-size: 32px; font-weight: 700; color: #111;
        }
        .lb-stat-card .sc-lbl {
            font-size: 13px; color: #666; margin-top: 2px;
        }

        /* ── Action buttons ── */
        .lb-actions {
            display: flex; gap: 14px; flex-wrap: wrap;
            justify-content: center;
            margin-top: 36px;
        }
        .btn-action {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 13px 28px;
            border-radius: 30px;
            font-size: 16px; font-weight: 600;
            text-decoration: none;
            border: 1.5px solid #000;
            transition: transform .15s, box-shadow .15s;
            box-shadow: 3px 3px 0 #000;
        }
        .btn-action:hover { transform: translate(-2px,-2px); box-shadow: 5px 5px 0 #000; }
        .btn-action:active { transform: translate(0,0); box-shadow: 1px 1px 0 #000; }
        .btn-action.primary { background: #FFC300; color: #111; }
        .btn-action.success { background: #fff;    color: #111; }

        /* ── Empty state ── */
        .lb-empty {
            text-align: center;
            padding: 60px 20px;
            font-family: 'Kaisei HarunoUmi', serif;
            font-size: 20px; color: #555;
        }



        /* ── "Congratulation!" title (mirrors Figma) ── */
        .lb-congrats-title {
            font-family: 'Kaisei HarunoUmi', serif;
            font-size: clamp(32px, 5vw, 56px);
            font-weight: 700;
            text-align: center;
            color: #111;
            margin: 0 0 32px;
        }

        /* ══════════════════════════════════════════════════════
           NAVBAR — centered layout
           ══════════════════════════════════════════════════════ */
        .lb-navbar {
            position: relative; z-index: 10;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            padding: 14px 32px;
            background: rgba(255,255,255,0.55);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,.08);
        }

        /* Logo — kiri */
        .lb-nav-logo {
            display: inline-flex; align-items: center;
            text-decoration: none;
        }
        /* ASSET: assets/img/logo-tahooga.png
           Atur height sesuai logo aslimu; lebar akan proporsional */
        .lb-nav-logo-img {
            height: 48px;
            width: auto;
            object-fit: contain;
            display: block;
        }

        /* Nav links — tengah (kolom 2 grid) */
        .lb-nav-links {
            list-style: none;
            margin: 0; padding: 0;
            display: flex; align-items: center; gap: 36px;
        }
        .lb-nav-links a {
            font-family: 'Inter', sans-serif;
            font-size: 16px; font-weight: 500;
            color: #111;
            text-decoration: none;
            transition: color .15s;
        }
        .lb-nav-links a:hover { color: #FFC300; }
        .lb-nav-links a.lb-nav-active {
            color: #FFC300; font-weight: 700;
        }

        /* Tombol kanan — justify ke kanan */
        .lb-nav-right {
            display: flex; align-items: center; gap: 10px;
            justify-content: flex-end;
        }
        .lb-nav-btn {
            font-family: 'Inter', sans-serif;
            font-size: 15px; font-weight: 500;
            color: #111;
            text-decoration: none;
            padding: 8px 20px;
            border: 1.5px solid #000;
            border-radius: 30px;
            background: #fff;
            transition: box-shadow .15s, transform .15s;
            white-space: nowrap;
        }
        .lb-nav-btn:hover {
            box-shadow: 2px 2px 0 #000;
            transform: translate(-1px,-1px);
        }
        .lb-nav-btn-back { background: #fff; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .lb-table-header,
            .lb-table tbody tr {
                grid-template-columns: 50px 1fr 90px 90px;
            }
            /* hide avg, best, levels on small screens */
            .lb-table-header > *:nth-child(5),
            .lb-table-header > *:nth-child(6),
            .lb-table-header > *:nth-child(7),
            .lb-table tbody td:nth-child(5),
            .lb-table tbody td:nth-child(6),
            .lb-table tbody td:nth-child(7) { display: none; }
            .lb-page-header h1 { font-size: 36px; }
            .lb-podium { gap: 6px; }
            .lb-podium-block { min-width: 90px; padding: 10px 10px 8px; }
        }
    </style>
</head>
<body>

<!-- ── Floating star decorations (pure CSS, no image asset) ── -->
<div class="lb-stars-deco" aria-hidden="true">
    <span>✦</span><span>★</span><span>✦</span><span>★</span>
    <span>✦</span><span>★</span><span>✦</span>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     NAVBAR
     ─────────────────────────────────────────────────────────────
     ASSET: assets/img/logo-tahooga.png
       → Ganti "assets/img/logo-tahooga.png" dengan path logo aslimu.
       → Disarankan ukuran: lebar maks 160px, tinggi proporsional.
       → Format PNG/SVG dengan background transparan.
     ═══════════════════════════════════════════════════════════════ -->
<nav class="lb-navbar">
    <!-- Logo kiri -->
    <a href="index.php" class="lb-nav-logo">
        <img
            src="assets/img/logo-tahooga.png"
            alt="TahooGa"
            class="lb-nav-logo-img"
            onerror="this.style.display='none'"
        >
    </a>

    <!-- Nav links — tengah -->
    <ul class="lb-nav-links">
        <li><a href="home.php">Beranda</a></li>
        <li><a href="index.php">Kuis</a></li>
        <li><a href="leaderboard.php" class="lb-nav-active">Leaderboard</a></li>
        <li><a href="materi.php">Materi</a></li>
        <li><a href="tentang.php">Tentang</a></li>
    </ul>

    <!-- Tombol kanan -->
    <div class="lb-nav-right">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php" class="lb-nav-btn">Keluar</a>
        <?php else: ?>
            <a href="login.php"  class="lb-nav-btn">Masuk</a>
        <?php endif; ?>
        <a href="javascript:history.back()" class="lb-nav-btn lb-nav-btn-back">← Kembali</a>
    </div>
</nav>


<div class="lb-page">

    <!-- ── Page header ── -->
    <div class="lb-page-header">
        <h1>🏆 Leaderboard</h1>
        <p>Pemain dengan total poin terbanyak</p>
    </div>

<?php if (!empty($leaderboard)): ?>

    <!-- ══════════════════════════════════════════════════
         "Congratulation!" banner (mirrors Figma card)
         Shown only when there are results
    ══════════════════════════════════════════════════ -->
    <div class="lb-congrats-title">Congratulation!</div>

    <!-- ══════════════════════════════════════════════════
         PODIUM  (top 3 players)
         Order: 2nd left · 1st centre · 3rd right
    ══════════════════════════════════════════════════ -->
    <div class="lb-podium">
        <?php
        /* reorder for podium: 2nd, 1st, 3rd */
        $podiumOrder = [];
        if (isset($leaderboard[1])) $podiumOrder[] = [1, $leaderboard[1]];
        if (isset($leaderboard[0])) $podiumOrder[] = [0, $leaderboard[0]];
        if (isset($leaderboard[2])) $podiumOrder[] = [2, $leaderboard[2]];
        $medals = ['🥇','🥈','🥉'];
        $podAvColors = ['#FFF385','#D3D1C7','#F3C87C'];
        foreach ($podiumOrder as [$rank, $p]): ?>
        <div class="lb-podium-slot">
            <div class="lb-podium-avatar"
                 style="background:<?= $podAvColors[$rank] ?>; color:#111;">
                <?= initials($p['nickname']) ?>
            </div>
            <div class="lb-podium-block">
                <div class="medal"><?= $medals[$rank] ?></div>
                <div class="pod-name"><?= htmlspecialchars($p['nickname']) ?></div>
                <div class="pod-pts"><?= number_format($p['total_points']) ?> pts</div>
                <div class="pod-kelas"><?= htmlspecialchars($p['kelas']) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ══════════════════════════════════════════════════
         STATS STRIP — quick aggregate numbers
    ══════════════════════════════════════════════════ -->
    <?php
    $totalPlayers = count($leaderboard);
    $topScore     = $leaderboard[0]['total_points'] ?? 0;
    $totalQuizzes = array_sum(array_column($leaderboard, 'total_quiz'));
    $avgBest      = round(array_sum(array_column($leaderboard, 'best_score')) / max($totalPlayers,1), 1);
    ?>
    <div class="lb-stats-strip">
        <div class="lb-stat-card">
            <div class="sc-val"><?= $totalPlayers ?></div>
            <div class="sc-lbl">👥 Pemain aktif</div>
        </div>
        <div class="lb-stat-card">
            <div class="sc-val"><?= number_format($topScore) ?></div>
            <div class="sc-lbl">🏆 Skor tertinggi</div>
        </div>
        <div class="lb-stat-card">
            <div class="sc-val"><?= $totalQuizzes ?></div>
            <div class="sc-lbl">📝 Total kuis dimainkan</div>
        </div>
        <div class="lb-stat-card">
            <div class="sc-val"><?= $avgBest ?>%</div>
            <div class="sc-lbl">⭐ Rata-rata best score</div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════
         YOUR RANK BANNER (only when logged-in & has points)
    ══════════════════════════════════════════════════ -->
    <?php if (isset($_SESSION['user_id']) && $session_user_rank && $session_user_rank['total_points'] > 0): ?>
    <div class="lb-your-rank">
        <span class="lb-your-rank-label">📍 Posisi kamu</span>
        <span class="lb-your-rank-pts"><?= number_format($session_user_rank['total_points']) ?> poin</span>
        <span class="lb-your-rank-num">#<?= $session_user_rank['rank'] ?></span>
    </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════
         FULL LEADERBOARD TABLE CARD
    ══════════════════════════════════════════════════ -->
    <div class="lb-card">

        <!-- Header row (mirrors Figma: Rank · User · Kelas · Skor · Rata-rata · Best · Level) -->
        <div class="lb-table-header">
            <div>Rank</div>
            <div>User</div>
            <div>Kelas</div>
            <div class="th-trophy">🏆 Skor</div>
            <div>📊 Rata-rata</div>
            <div>⭐ Best</div>
            <!-- ASSET: assets/img/trophy-icon.svg (small, ~51×51 px) placed beside Skor header in Figma -->
            <div>Level</div>
        </div>

        <table class="lb-table" aria-label="Tabel Leaderboard TahooGa">
            <tbody>
                <?php foreach ($leaderboard as $i => $p):
                    $avClass = $avatarColors[$i % count($avatarColors)];
                    $initStr = initials($p['nickname']);
                ?>
                <tr class="<?= $i < 3 ? 'lb-top-' . ($i+1) : '' ?>">
                    <td class="lb-rank-cell"><?= rankDisplay($i) ?></td>
                    <td>
                        <div class="lb-nick-cell">
                            <div class="lb-nick-avatar <?= $avClass ?>"><?= $initStr ?></div>
                            <span class="lb-nick"><?= htmlspecialchars($p['nickname']) ?></span>
                        </div>
                    </td>
                    <td><span class="lb-kelas"><?= htmlspecialchars($p['kelas']) ?></span></td>
                    <td><span class="lb-pts"><?= number_format($p['total_points']) ?></span></td>
                    <td><?= round($p['avg_percentage'], 1) ?>%</td>
                    <td><?= round($p['best_score'],    1) ?>%</td>
                    <td class="lb-levels"><?= htmlspecialchars($p['levels_played'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php else: ?>

    <!-- ── Empty state ── -->
    <div class="lb-card">
        <div class="lb-empty">
            Belum ada pemain 😢<br>
            <a href="index.php" class="btn-action primary" style="margin-top:24px;">🎯 Main Quiz Pertama!</a>
        </div>
    </div>

<?php endif; ?>

    <!-- ── Action buttons ── -->
    <div class="lb-actions">
        <a href="index.php" class="btn-action primary">🎯 Main Quiz</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php" class="btn-action success">👤 Profile Saya</a>
        <?php else: ?>
            <a href="login.php"   class="btn-action success">👤 Login</a>
        <?php endif; ?>
    </div>

</div><!-- /.lb-page -->

</body>
</html>