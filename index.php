<?php 
session_start(); 
include 'config/database.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id      = $_SESSION['user_id'];
$stmt         = $pdo->query("SELECT * FROM levels WHERE is_active = 1 ORDER BY id");
$levels       = $stmt->fetchAll();
$total_quiz   = $pdo->query("SELECT COUNT(*) FROM quiz_sessions WHERE user_id = $user_id AND status = 'completed'")->fetchColumn();
$total_points = $pdo->query("SELECT COALESCE(SUM(score),0) FROM quiz_sessions WHERE user_id = $user_id AND status = 'completed'")->fetchColumn();
$user_rank    = $pdo->query("
    SELECT GREATEST(1, (
        SELECT COUNT(*) + 1 
        FROM (
            SELECT SUM(COALESCE(score,0)) as pts 
            FROM quiz_sessions 
            WHERE status = 'completed' 
            GROUP BY user_id 
            HAVING pts > COALESCE((
                SELECT SUM(COALESCE(score,0)) 
                FROM quiz_sessions 
                WHERE user_id = $user_id AND status = 'completed'
            ), 0)
        ) ranked
    )) as rank
")->fetchColumn() ?: 999;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TahooGa — Quiz Interaktif</title>
    <link href="https://fonts.googleapis.com/css2?family=Joan&family=Inter:wght@400;500;600;700&family=Itim&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --yellow-main: #F9F383;
            --yellow-light: #FFFDD1;
            --green-btn: #90AE58;
            --salmon: #E37E7E;
            --white: #ffffff;
            --black: #1a1a1a;
            --gray: #666;
            --card-radius: 30px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(180deg, #FFFFFE 0%, #F9F383 50%, #FFFDD1 100%);
            min-height: 100vh;
            overflow-x: hidden;

            background:
                linear-gradient(180deg,
                    rgba(255,255,254,0.60) 0%,
                    rgba(249,243,131,0.60) 50%,
                    rgba(255,253,209,0.60) 100%),
                url('assets/img/bg-play.png') center center / cover no-repeat fixed;

                    
        }

        

        /* ── NAVBAR ── */
        nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 48px;
            height: 80px;
            position: relative;
            z-index: 10;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-logo img {
            width: 90px;
            height: 90px;
            object-fit: contain;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 8px;
        }

        .nav-links a {
            font-family: 'Joan', serif;
            font-size: 16px;
            color: var(--black);
            text-decoration: none;
            padding: 8px 20px;
            border-radius: 30px;
            background: var(--yellow-main);
            transition: background 0.2s, transform 0.15s;
        }

        .nav-links a:hover {
            background: #e8e26a;
            transform: translateY(-1px);
        }

        .nav-logout {
            font-family: 'Joan', serif;
            font-size: 15px;
            color: var(--black);
            text-decoration: none;
            padding: 8px 18px;
            border-radius: 30px;
            background: var(--yellow-main);
            border: 1px solid var(--black);
            transition: background 0.2s;
        }

        .nav-logout:hover { background: #e8e26a; }

        /* ── HERO SECTION ── */
        .hero {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 80px 20px;
            position: relative;
            min-height: 300px;
        }

        .hero-center {
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .hero-greeting-box {
            background: var(--salmon);
            border: 1px solid var(--black);
            border-radius: var(--card-radius);
            padding: 18px 40px;
            display: inline-block;
            margin-bottom: 16px;
        }

        .hero-greeting-box h1 {
            font-family: 'Joan', serif;
            font-size: 40px;
            font-weight: 400;
            color: var(--black);
            line-height: 1.4;
        }

        .hero-subtitle {
            font-family: 'Itim', cursive;
            font-size: 20px;
            color: var(--black);
            margin-top: 8px;
        }

        /* ── STATS ROW ── */
        .stats-row {
            display: flex;
            justify-content: center;
            gap: 24px;
            padding: 20px 80px;
            flex-wrap: wrap;
        }

        .stat-box {
            background: var(--white);
            border: 1px solid var(--black);
            border-radius: var(--card-radius);
            padding: 16px 40px;
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 220px;
        }

        .stat-number {
            font-family: 'Joan', serif;
            font-size: 32px;
            color: var(--black);
            font-weight: 400;
        }

        .stat-label {
            font-family: 'Joan', serif;
            font-size: 18px;
            color: var(--black);
        }

        /* ── SECTION TITLE ── */
        .section-title-wrap {
            text-align: center;
            padding: 30px 0 10px;
        }

        .section-title {
            font-family: 'Joan', serif;
            font-size: 36px;
            font-weight: 400;
            color: var(--black);
        }

        /* ── LEVEL SLIDER ── */
        .slider-outer {
            position: relative;
            padding: 60px 80px 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .slider-viewport {
    overflow: hidden;
    width: 100%;
}

.slider-track {
    display: flex;
    gap: 28px;
    transition: transform 0.4s ease;
}

        .slider-track:active { cursor: grabbing; }

        /* ── LEVEL CARD ── */
        .level-card {
            background: var(--white);
            border: 1px solid var(--black);
            border-radius: var(--card-radius);
            min-width: 280px;
            flex: 0 0 280px;
            padding: 0 0 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            overflow: visible;
            transition: transform 0.2s;
        }

        .level-card:hover { transform: translateY(-4px); }

        .card-mascot-wrap {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    margin-top: 0;
    margin-bottom: 10px;
    height: 190px;
    overflow: visible;
    padding-top: 20px;
}

        .card-mascot {
            height: 180px;
            width: auto;
            max-width: 220px;
            object-fit: contain;
            filter: drop-shadow(0 8px 16px rgba(0,0,0,0.12));
        }

        .card-mascot-placeholder {
            height: 180px;
            width: 180px;
            background: linear-gradient(135deg, #f0f0f0, #e0e0e0);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 64px;
        }

        .card-body {
            padding: 0 24px;
            width: 100%;
            text-align: center;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .card-level-name {
            font-family: 'Joan', serif;
            font-size: 22px;
            font-weight: 400;
            color: var(--black);
        }

        .card-desc {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: var(--gray);
            line-height: 1.5;
        }

        .card-difficulty {
            display: inline-block;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 4px 14px;
            border-radius: 20px;
            background: #f0f0f0;
            color: #555;
            margin: 4px 0;
        }

        .btn-mulai {
            display: inline-block;
            background: var(--green-btn);
            color: var(--white);
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-size: 18px;
            padding: 10px 0;
            width: 130px;
            border-radius: var(--card-radius);
            border: 1px solid var(--black);
            text-decoration: none;
            text-align: center;
            margin-top: 14px;
            transition: background 0.2s, transform 0.15s;
        }

        .btn-mulai:hover {
            background: #7a9a47;
            transform: scale(1.04);
        }

        /* ── CARD TERKUNCI ── */
        .locked-card {
            opacity: 0.55;
            filter: grayscale(50%);
        }

        .locked-card:hover {
            transform: none;
            cursor: default;
        }

        .btn-locked {
            display: inline-block;
            background: #c0c0c0;
            color: var(--white);
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-size: 16px;
            padding: 10px 0;
            width: 130px;
            border-radius: var(--card-radius);
            border: 1px solid #aaa;
            text-align: center;
            margin-top: 14px;
            cursor: not-allowed;
        }

        /* ── ARROW BUTTONS ── */
        .slider-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 52px;
            height: 52px;
            background: var(--white);
            border: 1.5px solid var(--black);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 5;
            transition: background 0.2s, transform 0.15s;
            font-size: 22px;
        }

        .slider-arrow:hover {
            background: var(--yellow-main);
            transform: translateY(-50%) scale(1.08);
        }

        .slider-arrow.prev { left: 20px; }
        .slider-arrow.next { right: 20px; }
        .slider-arrow.disabled { opacity: 0.3; pointer-events: none; }

        /* ── LEADERBOARD BADGE ── */
        .lb-badge-wrap {
            position: fixed;
            bottom: 30px;
            left: 30px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            z-index: 20;
        }

        .lb-avatars {
            display: flex;
        }

        .lb-avatars img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: 2px solid var(--white);
            object-fit: cover;
            margin-left: -8px;
        }

        .lb-avatars img:first-child { margin-left: 0; }

        .lb-label {
            background: var(--yellow-main);
            border: 1px solid var(--black);
            border-radius: 30px;
            padding: 5px 14px;
            font-family: 'Joan', serif;
            font-size: 14px;
            color: var(--black);
            text-decoration: none;
            display: inline-block;
            margin-top: 4px;
        }

        .lb-label:hover { background: #e8e26a; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            nav { padding: 0 20px; }
            .nav-links { display: none; }
            .hero { padding: 20px; }
            .stats-row { padding: 10px 20px; gap: 12px; }
            .stat-box { padding: 12px 20px; min-width: 0; flex: 1; }
            .slider-outer { padding: 60px 20px 40px; }
            .level-card { min-width: 240px; flex: 0 0 240px; }
        }
    </style>
</head>
<body>

<!-- ══ NAVBAR ══ -->
<nav>
    <div class="nav-logo">
        <img src="assets/img/logo.png" alt="TahooGa Logo"
             onerror="this.style.display='none'">
    </div>
    <ul class="nav-links">
        <li><a href="index.php">Beranda</a></li>
        <li><a href="materi.php">Materi</a></li>
        <li><a href="leaderboard.php">Leaderboard</a></li>
        <li><a href="tentang.php">Tentang</a></li>
    </ul>
    <a href="logout.php" class="nav-logout">🚪 Keluar</a>
</nav>

<!-- ══ HERO ══ -->
<section class="hero">
    <div class="hero-center">
        <div class="hero-greeting-box">
            <h1>Hello, <?= htmlspecialchars($_SESSION['nickname'] ?? 'User') ?>!</h1>
        </div>
        <p class="hero-subtitle">Siap tantang diri hari ini?</p>
    </div>
</section>

<!-- ══ STATS ══ -->
<div class="stats-row">
    <div class="stat-box">
        <span class="stat-number"><?= $total_quiz ?></span>
        <span class="stat-label">Quiz dimainkan</span>
    </div>
    <div class="stat-box">
        <span class="stat-number"><?= number_format($total_points) ?></span>
        <span class="stat-label">Total Poin</span>
    </div>
    <div class="stat-box">
        <span class="stat-number">#<?= $user_rank ?></span>
        <span class="stat-label">Ranking</span>
    </div>
</div>

<!-- ══ SECTION TITLE ══ -->
<div class="section-title-wrap">
    <h2 class="section-title">Cari Level Mu!</h2>
</div>

<!-- ══ LEVEL SLIDER ══ -->
<div class="slider-outer">
    <button class="slider-arrow prev" id="btnPrev" aria-label="Sebelumnya">&#8592;</button>
    <button class="slider-arrow next" id="btnNext" aria-label="Berikutnya">&#8594;</button>

    <div class="slider-viewport">
        <div class="slider-track" id="sliderTrack">

            <?php foreach($levels as $i => $level): ?>
            <div class="level-card">
                <div class="card-mascot-wrap">
                    <img class="card-mascot"
                    src="assets/img/level-<?= $level['id'] ?>.png"
                    alt="<?= htmlspecialchars($level['name']) ?>"
                    style="width:auto; height:170px; object-fit:contain; margin-top:10px;"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="card-mascot-placeholder" style="display:none;">
                        <?= $level['icon'] ?? '🎯' ?>
                    </div>
                </div>
                <div class="card-body">
                    <h3 class="card-level-name"><?= htmlspecialchars($level['name']) ?></h3>
                    <p class="card-desc"><?= htmlspecialchars($level['description']) ?></p>
                    <span class="card-difficulty"><?= ucfirst($level['difficulty']) ?></span>
                    <a href="quiz<?= $level['id'] ?>.php" class="btn-mulai">Mulai</a>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Card Level 4 - Terkunci (hanya tampilan) -->
            <div class="level-card locked-card">
                <div class="card-mascot-wrap">
                    <div class="card-mascot-placeholder" style="display:flex; font-size:72px;">
                        🔒
                    </div>
                </div>
                <div class="card-body">
                    <h3 class="card-level-name" style="color:#aaa;">Level 4</h3>
                    <p class="card-desc">Level ini belum tersedia. Nantikan update selanjutnya!</p>
                    <span class="card-difficulty" style="background:#f0f0f0; color:#bbb; border: 1px dashed #ccc;">SEGERA HADIR</span>
                    <button class="btn-locked" disabled>🔒 Terkunci</button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ══ LEADERBOARD BADGE ══ -->
<div class="lb-badge-wrap">
    <div class="lb-avatars">
        <img src="assets/images/lb-avatar1.png" alt="Top 1"
             onerror="this.style.display='none'">
        <img src="assets/images/lb-avatar2.png" alt="Top 2"
             onerror="this.style.display='none'">
    </div>
    <a href="leaderboard.php" class="lb-label">Leaderboard</a>
</div>

<script>
(function () {
    const track = document.getElementById('sliderTrack');
    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');

    const cards = Array.from(track.querySelectorAll('.level-card'));

    let currentIndex = 0;

    function getCardWidth() {
        const card = cards[0];
        const style = window.getComputedStyle(track);
        const gap = parseInt(style.gap) || 28;

        return card.offsetWidth + gap;
    }

    function updateSlider() {
        const moveX = currentIndex * getCardWidth();

        track.style.transform = `translateX(-${moveX}px)`;

        btnPrev.classList.toggle('disabled', currentIndex === 0);

        btnNext.classList.toggle(
            'disabled',
            currentIndex >= cards.length - 1
        );
    }

    btnNext.addEventListener('click', () => {
        if (currentIndex < cards.length - 1) {
            currentIndex++;
            updateSlider();
        }
    });

    btnPrev.addEventListener('click', () => {
        if (currentIndex > 0) {
            currentIndex--;
            updateSlider();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight' && currentIndex < cards.length - 1) {
            currentIndex++;
            updateSlider();
        }

        if (e.key === 'ArrowLeft' && currentIndex > 0) {
            currentIndex--;
            updateSlider();
        }
    });

    window.addEventListener('resize', updateSlider);

    updateSlider();
})();
</script>
</body>
</html>