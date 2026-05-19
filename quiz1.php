<?php 
session_start();
include 'config/database.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$level_id = 1; // ← Level 1

$stmt = $pdo->prepare("SELECT * FROM levels WHERE id = ? AND is_active = 1");
$stmt->execute([$level_id]);
$level = $stmt->fetch();

if (!$level) {
    header("Location: index.php");
    exit;
}

$total_soal = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE level_id = ?");
$total_soal->execute([$level_id]);
$jumlah_soal = $total_soal->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz — <?= htmlspecialchars($level['name']) ?> | TahooGa</title>
    <link href="https://fonts.googleapis.com/css2?family=Jockey+One&family=Lakki+Reddy&family=Joan&display=swap" rel="stylesheet">
    <style>
        /* ==============================
           CSS VARIABLES & RESET
        ============================== */
        :root {
            --yellow-main: #F9F383;
            --yellow-light: #FFFDD1;
            --green-soft: #C5DE96;
            --red-btn: #E63B01;
            --black: #000000;
            --white: #FFFFFE;
            --shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            font-family: 'Joan', serif;

            /* 
            =============================================
            BACKGROUND IMAGE
            Letakkan file gambar background Anda di:
              assets/images/bg-level.png
            (atau ganti ekstensi sesuai file Anda: .jpg, .webp, dsb.)
            =============================================
            */
            background:
                linear-gradient(180deg, rgba(255,255,254,0.6) 0%, rgba(249,243,131,0.6) 50%, rgba(255,253,209,0.6) 100%),
                url('assets/img/bg.real.png') center center / cover no-repeat fixed;

            overflow-x: hidden;
        }

        /* ==============================
           NAVBAR
        ============================== */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 36px;
            background: rgba(249, 243, 131, 0.85);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(0,0,0,0.08);
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* 
        =============================================
        LOGO IMAGE
        Letakkan file logo Anda di:
          assets/images/logo.png
        Ukuran ideal: 171x153 px (akan di-scale otomatis)
        =============================================
        */
        .nav-logo-img {
            height: 52px;
            width: auto;
            object-fit: contain;
            /* Tampil hanya jika gambar berhasil dimuat */
        }

        .nav-logo-text {
            font-family: 'Jockey One', sans-serif;
            font-size: 24px;
            color: var(--black);
            letter-spacing: 0.5px;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 8px;
        }

        .nav-links li a {
            display: inline-block;
            padding: 10px 22px;
            background: var(--yellow-main);
            border-radius: 30px;
            color: var(--black);
            font-family: 'Joan', serif;
            font-size: 16px;
            text-decoration: none;
            transition: background 0.2s, box-shadow 0.2s, transform 0.15s;
        }

        .nav-links li a:hover {
            background: var(--green-soft);
            box-shadow: var(--shadow);
            transform: translateY(-2px);
        }

        .nav-masuk {
            display: inline-block;
            padding: 10px 26px;
            background: var(--red-btn);
            border-radius: 30px;
            color: var(--white);
            font-family: 'Jockey One', sans-serif;
            font-size: 16px;
            text-decoration: none;
            box-shadow: var(--shadow);
            transition: opacity 0.2s, transform 0.15s;
        }

        .nav-masuk:hover {
            opacity: 0.85;
            transform: translateY(-2px);
        }

        /* ==============================
           MAIN LAYOUT
        ============================== */
        .page-wrapper {
            min-height: 100vh;
            padding-top: 90px; /* offset navbar */
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 48px;
            padding-left: 40px;
            padding-right: 40px;
            padding-bottom: 48px;
        }

        /* ==============================
           MASCOT / KARAKTER KIRI
        ============================== */

        /* 
        =============================================
        GAMBAR KARAKTER / MASKOT
        Letakkan file gambar di:
          assets/images/mascot-level.png
        Ukuran ideal: 313x386 px
        =============================================
        */
        .mascot-col {
            flex: 0 0 auto;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .mascot-img {
            width: 313px;
            height: 386px;
            object-fit: contain;
            filter: drop-shadow(0px 8px 16px rgba(0,0,0,0.15));
            animation: mascotFloat 3s ease-in-out infinite;
        }

        @keyframes mascotFloat {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-12px); }
        }

        /* Placeholder jika gambar belum ada */
        .mascot-placeholder {
            width: 313px;
            height: 386px;
            background: rgba(197, 222, 150, 0.4);
            border-radius: 20px;
            border: 2px dashed var(--green-soft);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: rgba(0,0,0,0.4);
            font-size: 14px;
            text-align: center;
            padding: 20px;
            animation: mascotFloat 3s ease-in-out infinite;
        }

        .mascot-placeholder .icon-ph { font-size: 48px; }

        /* ==============================
           CARD KANAN
        ============================== */
        .quiz-intro-card {
            flex: 0 0 auto;
            width: 454px;
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(12px);
            border-radius: 30px;
            border: 1px solid var(--black);
            box-shadow: var(--shadow);
            padding: 36px 32px 32px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            animation: cardSlideUp 0.6s cubic-bezier(.23,1,.32,1) both;
        }

        @keyframes cardSlideUp {
            from { opacity: 0; transform: translateY(32px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Level title */
        .quiz-intro-title {
            font-family: 'Lakki Reddy', cursive;
            font-size: 56px;
            line-height: 1.1;
            color: var(--black);
            text-align: center;
        }

        /* Description row */
        .quiz-intro-desc-row {
            background: linear-gradient(90deg, var(--green-soft) 0%, var(--white) 50%, var(--green-soft) 100%);
            border-radius: 30px;
            border: 1px solid var(--black);
            box-shadow: var(--shadow);
            padding: 14px 20px;
            font-family: 'Joan', serif;
            font-size: 15px;
            color: var(--black);
            text-align: center;
            line-height: 1.5;
        }

        /* Divider */
        .quiz-divider {
            width: 100%;
            height: 1px;
            background: var(--black);
            opacity: 0.15;
        }

        /* Info stats row */
        .quiz-intro-info {
            background: var(--white);
            border-radius: 30px;
            border: 1px solid var(--black);
            box-shadow: var(--shadow);
            padding: 16px 24px;
            display: flex;
            justify-content: space-around;
            gap: 8px;
        }

        .quiz-info-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }

        .quiz-info-num {
            font-family: 'Jockey One', sans-serif;
            font-size: 28px;
            color: var(--black);
            line-height: 1;
        }

        .quiz-info-label {
            font-family: 'Joan', serif;
            font-size: 13px;
            color: rgba(0,0,0,0.55);
        }

        .quiz-info-sep {
            width: 1px;
            background: rgba(0,0,0,0.15);
            align-self: stretch;
        }

        /* Difficulty badge */
        .quiz-intro-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 12px 20px;
            border-radius: 30px;
            box-shadow: var(--shadow);
        }

        .quiz-intro-icon {
            font-size: 32px;
        }

        .quiz-intro-level-name {
            font-family: 'Jockey One', sans-serif;
            font-size: 20px;
            color: var(--black);
        }

        .quiz-intro-difficulty {
            font-family: 'Joan', serif;
            font-size: 13px;
            color: rgba(0,0,0,0.6);
        }

        /* ==============================
           BUTTONS
        ============================== */
        .btn-row {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
        }

        /* "Mulai" button — green bar style dari desain */
        .btn-mulai {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 60px;
            background: var(--green-soft);
            border-radius: 30px;
            border: 1px solid var(--black);
            box-shadow: var(--shadow);
            font-family: 'Jockey One', sans-serif;
            font-size: 36px;
            color: var(--white);
            text-shadow: var(--shadow);
            text-decoration: none;
            letter-spacing: 1px;
            transition: transform 0.18s, box-shadow 0.18s, background 0.2s;
        }

        .btn-mulai:hover {
            background: #b0d07c;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0px 8px 18px rgba(0,0,0,0.18);
        }

        /* "Kembali" button — red style dari desain */
        .btn-kembali {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 160px;
            padding: 10px 28px;
            height: 48px;
            background: var(--red-btn);
            border-radius: 30px;
            border: 1px solid var(--black);
            box-shadow: var(--shadow);
            font-family: 'Jockey One', sans-serif;
            font-size: 24px;
            color: var(--white);
            text-shadow: var(--shadow);
            text-decoration: none;
            transition: transform 0.18s, opacity 0.18s;
        }

        .btn-kembali:hover {
            opacity: 0.85;
            transform: translateY(-2px);
        }

        /* ==============================
           RESPONSIVE
        ============================== */
        @media (max-width: 900px) {
            .page-wrapper {
                flex-direction: column;
                align-items: center;
            }
            .mascot-img,
            .mascot-placeholder {
                width: 200px;
                height: 240px;
            }
            .quiz-intro-card {
                width: 100%;
                max-width: 480px;
            }
        }

        @media (max-width: 600px) {
            nav { padding: 10px 16px; }
            .nav-links { display: none; }
            .quiz-intro-title { font-size: 40px; }
        }
    </style>
</head>
<body>

<!-- ==============================
     NAVBAR
============================== -->
<nav>
    <div class="nav-left">
        <!-- 
            LOGO IMAGE
            Path: assets/images/logo.png
        -->
        <img 
            class="nav-logo-img" 
            src="assets/img/hero-mascot.png" 
            alt="TahooGa Logo"
            onerror="this.style.display='none'"
        >
        <span class="nav-logo-text">TahooGa</span>
    </div>

    <ul class="nav-links">
        <li><a href="home.php">Beranda</a></li>
        <li><a href="leaderboard.php">Leaderboard</a></li>
        <li><a href="#">Tentang</a></li>
        <li><a href="#">Materi</a></li>
    </ul>

    <a href="logout.php" class="nav-masuk">Keluar</a>
</nav>

<!-- ==============================
     MAIN CONTENT
============================== -->
<div class="page-wrapper">

    <!-- KIRI: Gambar maskot / karakter -->
    <div class="mascot-col">
        <!-- 
            GAMBAR KARAKTER / MASKOT
            Path: assets/images/mascot-level.png
            Ukuran: 313 x 386 px
            Ganti src di bawah dengan path gambar Anda.
            Jika gambar belum ada, placeholder akan tampil otomatis.
        -->
        <img 
            class="mascot-img"
            src="assets/img/mascot-level.png"
            alt="Karakter Quiz"
            onerror="this.outerHTML = 
                '<div class=\'mascot-placeholder\'>' +
                '<span class=\'icon-ph\'>🎭</span>' +
                '<span>Letakkan gambar maskot di:<br><b>assets/images/mascot-level.png</b><br>(313 × 386 px)</span>' +
                '</div>'"
        >
    </div>

    <!-- KANAN: Card info level -->
    <div class="quiz-intro-card">

        <!-- Judul Level -->
        <div class="quiz-intro-title"><?= htmlspecialchars($level['name']) ?></div>

        <!-- Badge icon + nama + difficulty -->
        <div class="quiz-intro-badge" style="
            background: linear-gradient(135deg, <?= $level['color'] ?>, rgba(255,255,255,0.7));
            border: 3px solid <?= $level['color'] ?>;
        ">
            <span class="quiz-intro-icon"><?= $level['icon'] ?></span>
            <div>
                <div class="quiz-intro-level-name"><?= htmlspecialchars($level['name']) ?></div>
                <div class="quiz-intro-difficulty"><?= ucfirst($level['difficulty']) ?></div>
            </div>
        </div>

        <!-- Deskripsi -->
        <div class="quiz-intro-desc-row">
            <?= htmlspecialchars($level['description']) ?>
        </div>

        <div class="quiz-divider"></div>

        <!-- Info soal & poin -->
        <div class="quiz-intro-info">
            <div class="quiz-info-item">
                <span class="quiz-info-num"><?= $jumlah_soal ?></span>
                <span class="quiz-info-label">Soal</span>
            </div>
            <div class="quiz-info-sep"></div>
            <div class="quiz-info-item">
                <span class="quiz-info-num">10</span>
                <span class="quiz-info-label">Poin/Soal</span>
            </div>
            <div class="quiz-info-sep"></div>
            <div class="quiz-info-item">
                <span class="quiz-info-num"><?= $jumlah_soal * 10 ?></span>
                <span class="quiz-info-label">Maks Poin</span>
            </div>
        </div>

        <div class="quiz-divider"></div>

        <!-- Tombol aksi -->
        <div class="btn-row">
            <!-- Tombol MULAI (hijau, lebar penuh) -->
            <a href="play1.php" class="btn-mulai">Mulai</a>
            <!-- Tombol KEMBALI (merah) -->
            <a href="index.php" class="btn-kembali">Kembali</a>
        </div>

    </div>
</div>

</body>
</html>