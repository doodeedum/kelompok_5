<?php 
session_start();
include 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$level_id = 2; // ← Level 2
$user_id  = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM levels WHERE id = ? AND is_active = 1");
$stmt->execute([$level_id]);
$level = $stmt->fetch();

if (!$level) {
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['quiz_session_id'])) {
    $stmt = $pdo->prepare("INSERT INTO quiz_sessions (user_id, level_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $level_id]);
    $_SESSION['quiz_session_id']  = $pdo->lastInsertId();
    $_SESSION['current_question'] = 0;
    $_SESSION['skipped_questions'] = [];
    $_SESSION['total_score'] = 0;
}

if (!isset($_SESSION['quiz_questions'])) {
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE level_id = ? ORDER BY RAND()");
    $stmt->execute([$level_id]);
    $_SESSION['quiz_questions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$all_questions    = $_SESSION['quiz_questions'];
$total_questions  = count($all_questions);
$skipped          = $_SESSION['skipped_questions'] ?? [];

// Handle POST jawaban
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question_id'])) {
    $session_id   = $_SESSION['quiz_session_id'];
    $question_id  = intval($_POST['question_id']);
    $user_answer  = trim($_POST['answer'] ?? '');
    $is_skipped   = isset($_POST['skipped']) && $_POST['skipped'] === '1';
    $is_retry     = isset($_POST['retry'])   && $_POST['retry']   === '1';

    if ($is_skipped) {
        if (!in_array($question_id, $skipped)) {
            $_SESSION['skipped_questions'][] = $question_id;
        }
        $_SESSION['current_question']++;
    } elseif (in_array($user_answer, ['a','b','c','d'])) {
        $stmt = $pdo->prepare("SELECT correct_answer FROM questions WHERE id = ?");
        $stmt->execute([$question_id]);
        $correct    = $stmt->fetchColumn();
        $is_correct = ($user_answer === $correct) ? 1 : 0;

        if ($is_correct) {
            $_SESSION['total_score'] = ($_SESSION['total_score'] ?? 0) + 10;
        }

        $dup = $pdo->prepare("SELECT id FROM quiz_answers WHERE session_id = ? AND question_id = ?");
        $dup->execute([$session_id, $question_id]);
        if (!$dup->fetch()) {
            $ins = $pdo->prepare("INSERT INTO quiz_answers (session_id, question_id, user_answer, is_correct) VALUES (?,?,?,?)");
            $ins->execute([$session_id, $question_id, $user_answer, $is_correct]);
        }

        if ($is_retry) {
            $_SESSION['skipped_questions'] = array_values(
                array_filter($_SESSION['skipped_questions'], fn($id) => $id !== $question_id)
            );
        } else {
            $_SESSION['current_question']++;
        }
    }

    $_SESSION['last_answer_correct'] = $is_correct ?? null;

    header("Location: play2.php");
    exit;
}

$current_q_index = $_SESSION['current_question'] ?? 0;
$skipped         = $_SESSION['skipped_questions'] ?? [];
$total_score     = $_SESSION['total_score'] ?? 0;

$last_correct = $_SESSION['last_answer_correct'] ?? null;
unset($_SESSION['last_answer_correct']);

$main_done = $current_q_index >= $total_questions;

if ($main_done && empty($skipped)) {
    unset($_SESSION['quiz_questions'], $_SESSION['skipped_questions']);
    header("Location: result2.php");
    exit;
}

$is_retry_mode = false;
if ($main_done && !empty($skipped)) {
    $is_retry_mode = true;
    $retry_q_id    = $skipped[0];
    $current_q     = null;
    foreach ($all_questions as $q) {
        if ($q['id'] === $retry_q_id) { $current_q = $q; break; }
    }
    $display_index  = $total_questions;
    $retry_left     = count($skipped);
} else {
    $current_q     = $all_questions[$current_q_index];
    $display_index = $current_q_index;
    $retry_left    = 0;
}

$progress = round(($current_q_index / $total_questions) * 100);

$opts = [
    'a' => $current_q['option_a'],
    'b' => $current_q['option_b'],
    'c' => $current_q['option_c'],
    'd' => $current_q['option_d'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz — <?= htmlspecialchars($level['name']) ?> | TahooGa</title>
    <link href="https://fonts.googleapis.com/css2?family=Joan&family=Inter:wght@400;500;600;700&family=Jockey+One&display=swap" rel="stylesheet">
    <style>
        /* ==============================
           CSS VARIABLES & RESET
           Aksen Level 2: Biru #78D3F4
           (Level 1: Hijau #C5DE96)
        ============================== */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue-btn:  #78D3F4;   /* ← Warna aksen Level 2 (timer + tombol Jawab) */
            --white:     #ffffff;
            --black:     #1a1a1a;
            --radius:    30px;
            --shadow:    0px 4px 4px rgba(0, 0, 0, 0.25);
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;

            /*
            ╔══════════════════════════════════════════════════════════╗
            ║  🖼️  ASSET 1 — BACKGROUND HALAMAN                        ║
            ║  File    : assets/img/bg-play.png                        ║
            ║  Ukuran  : 1440 × 1024 px (atau lebih besar)             ║
            ║  Format  : PNG / JPG / WebP                              ║
            ║  Upload ke folder : /assets/img/                         ║
            ║  (Gambar yang sama dipakai di semua level)               ║
            ╚══════════════════════════════════════════════════════════╝
            */
            background-image: url('assets/img/bg-play.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-color: #F9F383;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: linear-gradient(180deg,
                rgba(255,255,254,0.6) 0%,
                rgba(249,243,131,0.6) 50%,
                rgba(255,253,209,0.6) 100%);
            pointer-events: none;
            z-index: 0;
        }

        .quiz-header, .soal-label, .progress-wrap,
        .question-text, form, .retry-banner, .score-display { position: relative; z-index: 1; }

        /* ==============================
           HEADER
        ============================== */
        .quiz-header {
            padding: 10px 24px 0;
            position: relative;
        }

        .quiz-header-top {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 4px;
        }

        /*
        ╔══════════════════════════════════════════════════════════╗
        ║  🖼️  ASSET 2 — LOGO / MASKOT NAVBAR                      ║
        ║  File    : assets/img/hero-mascot.png                    ║
        ║  Ukuran  : 171 × 153 px (tampil 52px tinggi di navbar)   ║
        ║  Format  : PNG transparan                                ║
        ║  Upload ke folder : /assets/img/                         ║
        ╚══════════════════════════════════════════════════════════╝
        */
        .quiz-header-top img.logo {
            width: 52px;
            height: auto;
            object-fit: contain;
        }

        .header-info h2 {
            font-family: 'Joan', serif;
            font-size: 20px;
            font-weight: 400;
            color: var(--black);
        }

        .header-info p {
            font-size: 13px;
            font-weight: 700;
            color: var(--black);
            margin-top: 1px;
        }

        .header-divider {
            width: 400px;
            height: 1px;
            background: #000;
            margin: 4px 0 0 0;
        }

        /* ==============================
           SKOR
        ============================== */
        .score-display {
            position: absolute;
            top: 24px;
            right: 160px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .score-box {
            background: #fff;
            border: 1px solid #000;
            border-radius: 16px;
            padding: 6px 18px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: var(--shadow);
        }

        .score-icon { font-size: 20px; }

        .score-value {
            font-size: 22px;
            font-weight: 700;
            color: var(--black);
            font-family: 'Inter', sans-serif;
            min-width: 40px;
            text-align: center;
            transition: transform 0.2s;
        }

        .score-value.bump { animation: scoreBump 0.4s ease; }

        @keyframes scoreBump {
            0%   { transform: scale(1); }
            40%  { transform: scale(1.4); color: #006fa3; }
            100% { transform: scale(1); }
        }

        /* ==============================
           TIMER — BIRU sesuai Level 2
        ============================== */
        .timer-wrap {
            position: absolute;
            top: 24px;
            right: 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            z-index: 1;
        }

        .timer-circle {
            width: 63px;
            height: 63px;
            /* ← Biru #78D3F4 sesuai Figma Level 2 */
            background: var(--blue-btn);
            border-radius: 50%;
            border: 1px solid var(--black);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 700;
            color: var(--black);
            font-family: 'Inter', sans-serif;
            transition: background 0.3s;
        }

        /* Saat waktu habis (≤10 detik) tetap merah untuk peringatan */
        .timer-circle.danger { background: #f44336; color: #fff; }

        /* ==============================
           NOMOR SOAL & PROGRESS
        ============================== */
        .soal-label {
            padding: 10px 40px 0;
            font-family: 'Joan', serif;
            font-size: 22px;
            color: var(--black);
        }

        .progress-wrap { padding: 8px 40px 0; }

        .progress-bar {
            width: 100%;
            height: 7px;
            background: rgba(0,0,0,0.15);
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            /* Progress bar biru sesuai Level 2 */
            background: var(--blue-btn);
            border-radius: 10px;
            transition: width 0.4s ease;
        }

        /* ==============================
           BANNER RETRY
        ============================== */
        .retry-banner {
            margin: 12px 40px 0;
            background: #fff3cd;
            border: 1.5px solid #f0a500;
            border-radius: 14px;
            padding: 10px 20px;
            font-size: 15px;
            font-weight: 700;
            color: #7a5000;
        }

        /* ==============================
           PERTANYAAN
        ============================== */
        .question-text {
            text-align: center;
            padding: 20px 100px 10px;
            font-family: 'Joan', serif;
            font-size: 36px;
            font-weight: 400;
            color: var(--black);
            line-height: 1.4;
            position: relative;
            z-index: 1;
        }

        /* ==============================
           KARTU PILIHAN JAWABAN
        ============================== */
        .options-card {
            background: var(--white);
            border: 1px solid #000;
            border-radius: var(--radius);
            margin: 16px 40px;
            padding: 20px 30px 30px;
            position: relative;
            z-index: 1;
        }

        .option-row {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 14px;
        }

        .option-row:last-child { margin-bottom: 0; }

        /* Lingkaran huruf A/B/C/D */
        .option-key-circle {
            width: 62px;
            height: 62px;
            min-width: 62px;
            background: var(--white);
            border: 1px solid #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 500;
            color: var(--black);
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
            user-select: none;
        }

        .option-key-circle:hover { background: #d4f2ff; transform: scale(1.06); }

        /* Kotak jawaban */
        .option-box {
            flex: 1;
            height: 71px;
            background: var(--white);
            border: 1px solid #000;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            padding: 0 24px;
            cursor: pointer;
            transition: background 0.15s, transform 0.12s;
        }

        .option-box:hover { background: #d4f2ff; transform: translateX(4px); }

        /* Terpilih — biru */
        .option-selected .option-key-circle { background: var(--blue-btn); border-color: #006fa3; }
        .option-selected .option-box        { background: var(--blue-btn); border-color: #006fa3; }

        /* Benar */
        .option-correct .option-key-circle { background: #69c94a; border-color: #2e7d00; }
        .option-correct .option-box        { background: #b8f0a0; border-color: #2e7d00; }

        /* Salah */
        .option-wrong .option-key-circle   { background: #f44336; border-color: #b71c1c; }
        .option-wrong .option-box          { background: #ffcdd2; border-color: #b71c1c; }

        .option-text {
            font-size: 20px;
            font-family: 'Jockey One', 'Inter', sans-serif;
            font-weight: 400;
            color: var(--black);
        }

        /* ==============================
           TOMBOL JAWAB — BIRU sesuai Level 2
        ============================== */
        .submit-wrap {
            display: flex;
            justify-content: flex-end;
            padding: 10px 40px 30px;
            position: relative;
            z-index: 1;
        }

        .btn-jawab {
            width: 406px;
            height: 71px;
            /* ← Biru #78D3F4 sesuai Figma Level 2 */
            background: var(--blue-btn);
            border: 1px solid #000;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            font-family: 'Joan', serif;
            font-size: 36px;
            font-weight: 400;
            color: var(--black);
            cursor: pointer;
            transition: background 0.18s, transform 0.14s;
        }

        .btn-jawab:hover  { background: #55c5ef; transform: translateY(-2px); }
        .btn-jawab:active { transform: translateY(0); }
        .btn-jawab:disabled { opacity: 0.6; cursor: not-allowed; }

        /* ==============================
           FLOATING +10 POIN ANIMASI
        ============================== */
        .point-pop {
            position: fixed;
            font-family: 'Joan', serif;
            font-size: 40px;
            font-weight: 700;
            color: #006fa3;
            pointer-events: none;
            z-index: 9999;
            animation: floatUp 1.1s ease forwards;
            text-shadow: 0 2px 8px rgba(0,0,0,0.18);
        }

        @keyframes floatUp {
            0%   { opacity: 1; transform: translateY(0) scale(1); }
            60%  { opacity: 1; transform: translateY(-80px) scale(1.2); }
            100% { opacity: 0; transform: translateY(-140px) scale(0.8); }
        }

        /* ==============================
           RESPONSIVE
        ============================== */
        @media (max-width: 768px) {
            .question-text  { padding: 16px 24px 8px; font-size: 22px; }
            .options-card   { margin: 10px 16px; }
            .submit-wrap    { padding: 8px 16px 24px; }
            .btn-jawab      { width: 100%; font-size: 24px; }
            .timer-wrap     { right: 16px; }
            .score-display  { right: 100px; }
            .header-divider { width: 100%; }
            .retry-banner   { margin: 10px 16px 0; }
        }
    </style>
</head>
<body>

<!-- ══ HEADER ══ -->
<div class="quiz-header" style="position:relative;">
    <div class="quiz-header-top">
        <!--
        ╔══════════════════════════════════════════════════════════╗
        ║  🖼️  ASSET 2 — LOGO / MASKOT NAVBAR                      ║
        ║  File    : assets/img/hero-mascot.png                    ║
        ║  Ukuran  : 171 × 153 px, PNG transparan                  ║
        ║  Upload ke folder : /assets/img/                         ║
        ╚══════════════════════════════════════════════════════════╝
        -->
        <img class="logo"
             src="assets/img/hero-mascot.png"
             alt="TahooGa"
             onerror="this.style.display='none'">

        <div class="header-info">
            <h2>Level <?= $level_id ?><?= $is_retry_mode ? ' — Kesempatan Terakhir!' : '' ?></h2>
            <p>Jawablah pertanyaan dengan menekan tombol a, b, c, d</p>
        </div>
    </div>
    <div class="header-divider"></div>

    <!-- Skor -->
    <div class="score-display">
        <div class="score-box">
            <span class="score-icon">⭐</span>
            <span class="score-value" id="scoreValue"><?= $total_score ?></span>
        </div>
        <small style="font-size:11px; color:#555;">poin</small>
    </div>

    <!-- Timer — lingkaran biru -->
    <div class="timer-wrap">
        <div class="timer-circle" id="timerCircle">
            <span id="timer">30</span>
        </div>
        <small style="font-size:11px; color:#555;">waktu</small>
    </div>
</div>

<!-- Nomor soal & progress -->
<div class="soal-label">
    <?php if ($is_retry_mode): ?>
        ⚠️ Soal Terlewat — <?= $retry_left ?> tersisa
    <?php else: ?>
        Soal <?= $display_index + 1 ?> / <?= $total_questions ?>
    <?php endif; ?>
</div>
<div class="progress-wrap">
    <div class="progress-bar">
        <div class="progress-fill" style="width:<?= $progress ?>%"></div>
    </div>
</div>

<?php if ($is_retry_mode): ?>
<div class="retry-banner">
    ⏰ Waktu habis tadi! Ini kesempatanmu menjawab soal yang terlewat. Jawab sekarang sebelum terlambat!
</div>
<?php endif; ?>

<!-- ══ PERTANYAAN ══ -->
<div class="question-text">
    <?= htmlspecialchars($current_q['question']) ?>
</div>

<!-- ══ FORM JAWABAN ══ -->
<form method="POST" id="quizForm">
    <input type="hidden" name="question_id" value="<?= $current_q['id'] ?>">
    <input type="hidden" name="answer"      id="hiddenAnswer" value="">
    <?php if ($is_retry_mode): ?>
    <input type="hidden" name="retry" value="1">
    <?php endif; ?>

    <div class="options-card">
        <?php foreach ($opts as $val => $label): ?>
        <div class="option-row" id="row-<?= $val ?>">
            <div class="option-key-circle" onclick="pilih('<?= $val ?>')" id="circle-<?= $val ?>">
                <?= strtoupper($val) ?>
            </div>
            <div class="option-box" onclick="pilih('<?= $val ?>')" id="box-<?= $val ?>">
                <span class="option-text"><?= htmlspecialchars($label) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="submit-wrap">
        <button type="submit" class="btn-jawab" id="submitBtn">Jawab</button>
    </div>
</form>

<!-- Form skip (auto-submit saat timeout) -->
<form method="POST" id="skipForm" style="display:none;">
    <input type="hidden" name="question_id" value="<?= $current_q['id'] ?>">
    <input type="hidden" name="answer"   value="">
    <input type="hidden" name="skipped"  value="<?= $is_retry_mode ? '0' : '1' ?>">
    <?php if ($is_retry_mode): ?>
    <input type="hidden" name="retry" value="1">
    <?php endif; ?>
</form>

<script>
// ══════════════════════════════════════════════
//  SOUND ENGINE — Web Audio API
// ══════════════════════════════════════════════
const AudioCtx = window.AudioContext || window.webkitAudioContext;
let audioCtx = null;

function getAudioCtx() {
    if (!audioCtx) audioCtx = new AudioCtx();
    return audioCtx;
}

function playCorrectSound() {
    const ctx = getAudioCtx();
    const notes = [523.25, 659.25, 783.99];
    notes.forEach((freq, i) => {
        const osc  = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.type = 'sine';
        osc.frequency.value = freq;
        const t = ctx.currentTime + i * 0.13;
        gain.gain.setValueAtTime(0, t);
        gain.gain.linearRampToValueAtTime(0.35, t + 0.04);
        gain.gain.exponentialRampToValueAtTime(0.001, t + 0.45);
        osc.start(t);
        osc.stop(t + 0.5);
    });
}

function playWrongSound() {
    const ctx = getAudioCtx();
    const notes = [415.30, 349.23];
    notes.forEach((freq, i) => {
        const osc  = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.type = 'sawtooth';
        osc.frequency.value = freq;
        const t = ctx.currentTime + i * 0.18;
        gain.gain.setValueAtTime(0, t);
        gain.gain.linearRampToValueAtTime(0.25, t + 0.03);
        gain.gain.exponentialRampToValueAtTime(0.001, t + 0.4);
        osc.start(t);
        osc.stop(t + 0.45);
    });
}

// ══════════════════════════════════════════════
//  FLOATING +10 POIN
// ══════════════════════════════════════════════
function showPointPop(x, y) {
    const el = document.createElement('div');
    el.className = 'point-pop';
    el.textContent = '+10';
    el.style.left = (x - 30) + 'px';
    el.style.top  = (y - 20) + 'px';
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 1200);
}

// ══════════════════════════════════════════════
//  FLASH RESULT DARI PHP (jawaban sebelumnya)
// ══════════════════════════════════════════════
<?php if ($last_correct === 1): ?>
window.addEventListener('DOMContentLoaded', () => {
    playCorrectSound();
    showPointPop(window.innerWidth / 2, window.innerHeight / 3);
    const sv = document.getElementById('scoreValue');
    sv.classList.remove('bump');
    void sv.offsetWidth;
    sv.classList.add('bump');
});
<?php elseif ($last_correct === 0): ?>
window.addEventListener('DOMContentLoaded', () => {
    playWrongSound();
});
<?php endif; ?>

// ══════════════════════════════════════════════
//  PILIH JAWABAN
// ══════════════════════════════════════════════
let selected = null;

function pilih(val) {
    ['a','b','c','d'].forEach(v => {
        document.getElementById('row-' + v).classList.remove('option-selected');
    });
    document.getElementById('row-' + val).classList.add('option-selected');
    document.getElementById('hiddenAnswer').value = val;
    selected = val;
}

// ══════════════════════════════════════════════
//  SUBMIT
// ══════════════════════════════════════════════
document.getElementById('quizForm').addEventListener('submit', function(e) {
    if (!selected) {
        e.preventDefault();
        alert('❌ Pilih jawaban dulu!');
        return;
    }
    document.getElementById('submitBtn').disabled = true;
    getAudioCtx().resume();
});

// Keyboard shortcut A B C D + Enter
document.addEventListener('keydown', function(e) {
    const key = e.key.toLowerCase();
    if (['a','b','c','d'].includes(key)) pilih(key);
    if (e.key === 'Enter' && selected) document.getElementById('quizForm').submit();
});

// ══════════════════════════════════════════════
//  COUNTDOWN TIMER 30 DETIK
// ══════════════════════════════════════════════
let seconds = 30;
const timerEl     = document.getElementById('timer');
const timerCircle = document.getElementById('timerCircle');

const interval = setInterval(() => {
    seconds--;
    timerEl.textContent = seconds;
    if (seconds <= 10) timerCircle.classList.add('danger');
    if (seconds <= 0) {
        clearInterval(interval);
        document.getElementById('skipForm').submit();
    }
}, 1000);
</script>

</body>
</html>