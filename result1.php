<?php 
session_start();
include 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$session_id = $_SESSION['quiz_session_id'] ?? 0;
$user_id    = $_SESSION['user_id'];
$level_id   = 1;

$username       = 'Pengguna';
$correct_count  = 0;
$wrong_count    = 0;
$score          = 0;
$max_score      = 100;
$percentage     = 0;
$total_answered = 0;

if ($session_id > 0) {
    try {
        // Ambil data user
        $su = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $su->execute([$user_id]);
        $usr = $su->fetch();
        if ($usr) $username = $usr['username'];

        $stmt = $pdo->prepare("
            SELECT 
                COUNT(qa.id) as total_answered,
                SUM(CASE WHEN qa.is_correct = 1 THEN 1 ELSE 0 END) as correct_count,
                SUM(CASE WHEN qa.is_correct = 0 THEN 1 ELSE 0 END) as wrong_count
            FROM quiz_sessions qs 
            LEFT JOIN quiz_answers qa ON qa.session_id = qs.id
            WHERE qs.id = ? AND qs.user_id = ?
            GROUP BY qs.id
        ");
        $stmt->execute([$session_id, $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $total_answered = (int)$result['total_answered'];
            $correct_count  = (int)$result['correct_count'];
            $wrong_count    = (int)$result['wrong_count'];
            $score          = $correct_count * 10;
            $max_score      = $total_answered * 10;
            $percentage     = $total_answered > 0
                              ? round(($correct_count / $total_answered) * 100)
                              : 0;

            $grade = $percentage >= 80 ? 'A' : ($percentage >= 60 ? 'B' : 'C');

            $upd = $pdo->prepare("
                UPDATE quiz_sessions 
                SET total_questions = ?, correct_answers = ?, wrong_answers = ?,
                    score = ?, percentage = ?, grade = ?,
                    finished_at = CURRENT_TIMESTAMP, status = 'completed'
                WHERE id = ?
            ");
            $upd->execute([
                $total_answered, $correct_count, $wrong_count,
                $score, $percentage, $grade, $session_id
            ]);
        }
    } catch (Exception $e) {
        error_log("Quiz result1 error: " . $e->getMessage());
    }
}

// Cek apakah ada level berikutnya
$next_level_id = $level_id + 1;
$stmt_next = $pdo->prepare("SELECT id FROM levels WHERE id = ? AND is_active = 1");
$stmt_next->execute([$next_level_id]);
$has_next_level = (bool)$stmt_next->fetch();

unset($_SESSION['quiz_session_id'], $_SESSION['current_question'], $_SESSION['quiz_questions'], $_SESSION['skipped_questions'], $_SESSION['total_score']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Quiz Level 1 | TahooGa</title>
    <link href="https://fonts.googleapis.com/css2?family=Joan&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(180deg, #FFFFFE 0%, #F9F383 50%, #FFFDD1 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            overflow-x: hidden;
            position: relative;
        }

        /* Background image overlay */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url('assets/img/bg-level.png');
            background-size: cover;
            background-position: center;
            opacity: 0.6;
            pointer-events: none;
            z-index: 0;
        }

        /* ── NAVBAR ── */
        .navbar {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 40px;
            position: relative;
            z-index: 2;
        }

        .navbar img.logo {
            width: 90px;
            height: auto;
        }

        .nav-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 62px;
            padding: 0 28px;
            background: #F9F383;
            border-radius: 30px;
            font-family: 'Inter', sans-serif;
            font-size: 18px;
            font-weight: 500;
            color: #1a1a1a;
            text-decoration: none;
            border: 1.5px solid #1a1a1a;
            cursor: pointer;
            transition: background 0.15s, transform 0.12s;
        }

        .nav-btn:hover { background: #e9e250; transform: translateY(-2px); }

        .nav-btns { display: flex; gap: 12px; }

        /* ── JUDUL RIWAYAT ── */
        .page-title {
            font-family: 'Joan', serif;
            font-size: 36px;
            font-weight: 400;
            color: #1a1a1a;
            text-align: center;
            margin: 16px 0 24px;
            position: relative;
            z-index: 2;
        }

        /* ── KARTU UTAMA (skor) ── */
        .card-main {
            width: min(716px, 92vw);
            background: linear-gradient(180deg, #ffffff 0%, #F9F383 100%);
            border: 2px solid #000;
            border-radius: 30px;
            padding: 32px 40px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            position: relative;
            z-index: 2;
        }

        .result-name {
            font-family: 'Joan', serif;
            font-size: 36px;
            font-weight: 400;
            color: #1a1a1a;
            text-align: center;
        }

        .result-subtitle {
            font-family: 'Joan', serif;
            font-size: 36px;
            font-weight: 400;
            color: #1a1a1a;
            text-align: center;
        }

        .score-big {
            font-family: 'Inter', sans-serif;
            font-size: 96px;
            font-weight: 700;
            color: #1a1a1a;
            line-height: 1.1;
            text-align: center;
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.2s both;
        }

        @keyframes popIn {
            from { transform: scale(0.4); opacity: 0; }
            to   { transform: scale(1);   opacity: 1; }
        }

        .score-suffix {
            font-family: 'Joan', serif;
            font-size: 36px;
            font-weight: 400;
            color: #1a1a1a;
            text-align: center;
        }

        /* ── KARTU STATS ── */
        .card-stats {
            width: min(716px, 92vw);
            background: #ffffff;
            border: 2px solid #000;
            border-radius: 30px;
            display: grid;
            grid-template-columns: 1fr 1px 1fr 1px 1fr;
            overflow: hidden;
            position: relative;
            z-index: 2;
            margin-top: 16px;
        }

        .stat-divider {
            background: #000;
            align-self: stretch;
        }

        .stat-cell {
            padding: 28px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .stat-number {
            font-family: 'Inter', sans-serif;
            font-size: 64px;
            font-weight: 700;
            color: #1a1a1a;
            line-height: 1;
            animation: countUp 0.6s ease both;
        }

        .stat-number.pct { font-size: 52px; }

        @keyframes countUp {
            from { transform: translateY(20px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }

        .stat-label {
            font-family: 'Inter', sans-serif;
            font-size: 28px;
            font-weight: 400;
            color: #1a1a1a;
        }

        /* ── KARTU TOMBOL ── */
        .card-actions {
            width: min(716px, 92vw);
            background: #ffffff;
            border: 2px solid #000;
            border-radius: 30px;
            display: grid;
            grid-template-columns: 1fr 1px 1fr;
            overflow: hidden;
            position: relative;
            z-index: 2;
            margin-top: 16px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            font-family: 'Inter', sans-serif;
            font-size: 22px;
            font-weight: 400;
            color: #1a1a1a;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.15s;
            background: transparent;
            border: none;
        }

        .action-btn:hover { background: #F9F383; }

        .action-divider {
            background: #000;
            align-self: stretch;
        }

        /* animasi bintang */
        .stars-wrap {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 999;
            overflow: hidden;
        }

        .star {
            position: absolute;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: starFall linear both;
        }

        @keyframes starFall {
            from { transform: translateY(-20px) rotate(0deg); opacity: 1; }
            to   { transform: translateY(110vh) rotate(720deg); opacity: 0; }
        }

        @media (max-width: 600px) {
            .navbar { padding: 10px 16px; }
            .score-big { font-size: 72px; }
            .stat-number { font-size: 48px; }
            .stat-number.pct { font-size: 38px; }
            .stat-label { font-size: 20px; }
            .action-btn { font-size: 16px; padding: 12px 8px; }
        }
    </style>
</head>
<body>

<!-- ── Confetti bintang jika skor bagus ── -->
<?php if ($percentage >= 60): ?>
<div class="stars-wrap" id="starsWrap"></div>
<?php endif; ?>

<!-- ── NAVBAR ── -->
<nav class="navbar">
    <img class="logo" src="assets/img/hero-mascot.png" alt="TahooGa" onerror="this.style.display='none'">
    <div class="nav-btns">
        <a href="profile.php"     class="nav-btn">Profil</a>
        <a href="leaderboard.php" class="nav-btn">Leaderboard</a>
    </div>
</nav>

<!-- ── JUDUL ── -->
<div class="page-title">Riwayat Skor Mu!</div>

<!-- ── KARTU SKOR UTAMA ── -->
<div class="card-main">
    <div class="result-name"><?= htmlspecialchars($username) ?></div>
    <div class="result-subtitle">Kamu Mendapatkan</div>

    <div class="score-big"><?= $score ?></div>

    <div class="score-suffix">poin dari <?= $max_score ?></div>
</div>

<!-- ── KARTU STATS ── -->
<div class="card-stats">
    <div class="stat-cell">
        <span class="stat-number" id="numBenar">0</span>
        <span class="stat-label">Benar</span>
    </div>
    <div class="stat-divider"></div>
    <div class="stat-cell">
        <span class="stat-number" id="numSalah">0</span>
        <span class="stat-label">Salah</span>
    </div>
    <div class="stat-divider"></div>
    <div class="stat-cell">
        <span class="stat-number pct" id="numPct">0%</span>
        <span class="stat-label">Rata-rata</span>
    </div>
</div>

<!-- ── TOMBOL AKSI ── -->
<div class="card-actions" style="margin-bottom: 40px;">
    <a href="play1.php?retry=1" class="action-btn">&lt;&lt; Coba Lagi</a>
    <div class="action-divider"></div>
    <?php if ($has_next_level): ?>
    <a href="play2.php" class="action-btn">Level Berikutnya &gt;&gt;</a>
    <?php else: ?>
    <a href="index.php" class="action-btn">Kembali ke Menu</a>
    <?php endif; ?>
</div>

<script>
// ── Animasi hitung naik
function animateCount(el, target, suffix, duration) {
    let start = 0;
    const step = Math.ceil(target / (duration / 16));
    const tick = () => {
        start = Math.min(start + step, target);
        el.textContent = start + suffix;
        if (start < target) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
}

window.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        animateCount(document.getElementById('numBenar'), <?= $correct_count ?>, '', 700);
        animateCount(document.getElementById('numSalah'), <?= $wrong_count  ?>, '', 700);
        animateCount(document.getElementById('numPct'),   <?= $percentage   ?>, '%', 900);
    }, 300);
});

<?php if ($percentage >= 60): ?>
// ── Confetti bintang
const wrap = document.getElementById('starsWrap');
const colors = ['#F9F383','#C5DE96','#FFD700','#fff','#f0a500'];
for (let i = 0; i < 60; i++) {
    const s = document.createElement('div');
    s.className = 'star';
    s.style.cssText = `
        left: ${Math.random()*100}%;
        background: ${colors[Math.floor(Math.random()*colors.length)]};
        border-radius: ${Math.random()>0.5?'50%':'3px'};
        width: ${4+Math.random()*8}px;
        height: ${4+Math.random()*8}px;
        animation-duration: ${2+Math.random()*3}s;
        animation-delay: ${Math.random()*2}s;
    `;
    wrap.appendChild(s);
}
// Hapus setelah animasi selesai
setTimeout(() => wrap.remove(), 6000);
<?php endif; ?>
</script>

</body>
</html>