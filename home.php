<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TahooGa — Quiz Interaktif</title>
  <link href="https://fonts.googleapis.com/css2?family=Joan&family=Itim&family=Just+Me+Again+Down+Here&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    :root {
      --yellow:      #F9F383;
      --yellow-pale: #FFFDD1;
      --cream:       #FFFFFA;
      --black:       #0d0d0d;
      --gray:        #555;
    }

    html, body {
      width: 100%;
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
      background: linear-gradient(180deg,
        rgba(255,251,251,0.20) 0%,
        rgba(249,243,131,0.20) 50%,
        rgba(255,253,209,0.20) 100%),
        var(--yellow-pale);
      overflow-x: hidden;
      color: var(--black);
    }

    /* ─── NAVBAR ────────────────────────────────────── */
    nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 28px 56px;
      position: relative;
      z-index: 10;
    }

    .nav-logo {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
    }

    .nav-logo-img {
      width: 52px;
      height: 52px;
      object-fit: contain;
    }

    .nav-logo-text {
      font-family: 'Itim', cursive;
      font-size: 28px;
      color: var(--black);
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 44px;
      list-style: none;
    }

    .nav-links a {
      font-family: 'Joan', serif;
      font-size: 20px;
      color: var(--black);
      text-decoration: none;
      position: relative;
      transition: opacity 0.2s;
    }

    .nav-links a::after {
      content: '';
      position: absolute;
      bottom: -4px; left: 0;
      width: 0; height: 2px;
      background: var(--yellow);
      border-radius: 2px;
      transition: width 0.25s ease;
    }

    .nav-links a:hover { opacity: 0.65; }
    .nav-links a:hover::after { width: 100%; }

    .nav-masuk {
      background: var(--yellow);
      border: none;
      border-radius: 30px;
      padding: 13px 36px;
      font-family: 'Joan', serif;
      font-size: 20px;
      color: var(--black);
      cursor: pointer;
      text-decoration: none;
      transition: transform 0.18s, box-shadow 0.18s;
      display: inline-block;
    }

    .nav-masuk:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(249,243,131,0.8);
    }

    /* ─── HERO ──────────────────────────────────────── */
    .hero {
      position: relative;
      min-height: calc(100vh - 112px);
      display: flex;
      align-items: center;
      padding: 0 56px 0 80px;
      gap: 0;
    }

    /* Gambar kiri */
    .hero-image {
      flex-shrink: 0;
      width: 340px;
      height: 340px;
      position: relative;
      z-index: 2;
      margin-top: -20px;
    }

    .hero-image img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      border-radius: 24px;
      display: block;
    }

    /* Placeholder saat logo belum ada */
    .hero-image-placeholder {
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, rgba(249,243,131,0.35), rgba(255,253,209,0.6));
      border: 2px dashed rgba(249,243,131,0.7);
      border-radius: 24px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 10px;
      color: rgba(13,13,13,0.3);
      font-size: 13px;
      font-family: 'Inter', sans-serif;
    }

    .hero-image-placeholder svg {
      width: 64px; height: 64px;
      opacity: 0.25;
    }

    /* Teks tengah */
    .hero-content {
      flex: 1;
      padding-left: 60px;
      position: relative;
      z-index: 2;
    }

    .hero-title {
      font-family: 'Joan', serif;
      font-size: 68px;
      font-weight: 400;
      color: var(--black);
      line-height: 1.08;
      margin-bottom: 0;
    }

    .hero-title-accent {
      font-family: 'Just Me Again Down Here', cursive;
      font-size: 90px;
      font-weight: 95;
      color: var(--black);
      line-height: 0.95;
      display: inline-block;
      position: inherit;
    }

    .hero-subtitle {
      margin-top: 32px;
      font-family: 'Inter', sans-serif;
      font-size: 22px;
      font-weight: 500;
      color: var(--black);
      line-height: 1.65;
      opacity: 0.72;
    }

    /* Tombol CTA */
    .hero-cta {
      display: flex;
      align-items: center;
      gap: 22px;
      margin-top: 52px;
      flex-wrap: wrap;
    }

    .btn-signup {
      width: 256px;
      height: 72px;
      background: var(--yellow);
      border: 1.5px solid var(--black);
      border-radius: 30px;
      font-family: 'Itim', cursive;
      font-size: 26px;
      color: var(--black);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.18s, box-shadow 0.18s;
      cursor: pointer;
    }

    .btn-signup:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 32px rgba(249,243,131,0.7);
    }

    .btn-mulai {
      width: 256px;
      height: 72px;
      background: var(--cream);
      border: 1.5px solid var(--black);
      border-radius: 30px;
      font-family: 'Itim', cursive;
      font-size: 26px;
      color: var(--black);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.18s, box-shadow 0.18s;
      cursor: pointer;
    }

    .btn-mulai:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 32px rgba(0,0,0,0.10);
    }

    /* ─── BLUR STRIP (dekoratif, persis dari Figma) ──── */
    .hero-strip {
      position: absolute;
      left: 0; right: 0;
      bottom: 0;
      height: 420px;
      background: rgba(255,255,255,0.06);
      box-shadow:
        0 4px 4px rgba(0,0,0,0.06),
        0 4px 4px var(--yellow),
        0 4px 4px var(--yellow);
      outline: 2px solid rgba(255,250,250,0.6);
      backdrop-filter: blur(2px);
      -webkit-backdrop-filter: blur(2px);
      z-index: 1;
      pointer-events: none;
    }

    /* ─── ANIMASI MASUK ─────────────────────────────── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(28px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .nav-logo    { animation: fadeUp 0.5s ease both; }
    .nav-links   { animation: fadeUp 0.5s 0.08s ease both; }
    .nav-masuk   { animation: fadeUp 0.5s 0.14s ease both; }
    .hero-image  { animation: fadeUp 0.65s 0.20s ease both; }
    .hero-title  { animation: fadeUp 0.65s 0.28s ease both; }
    .hero-subtitle { animation: fadeUp 0.65s 0.38s ease both; }
    .hero-cta    { animation: fadeUp 0.65s 0.46s ease both; }

    /* ─── RESPONSIVE ────────────────────────────────── */
    @media (max-width: 1024px) {
      nav { padding: 22px 36px; }
      .hero { padding: 0 36px 0 48px; gap: 0; }
      .hero-title { font-size: 52px; }
      .hero-title-accent { font-size: 70px; }
      .hero-content { padding-left: 40px; }
      .hero-image { width: 280px; height: 280px; }
    }

    @media (max-width: 768px) {
      nav { padding: 18px 24px; }
      .nav-links { display: none; }

      .hero {
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 40px 24px 80px;
        min-height: auto;
        gap: 36px;
      }

      .hero-content { padding-left: 0; }
      .hero-title { font-size: 38px; }
      .hero-title-accent { font-size: 52px; }
      .hero-subtitle { font-size: 18px; margin-top: 20px; }

      .hero-cta { justify-content: center; gap: 16px; }
      .btn-signup, .btn-mulai { width: 220px; height: 62px; font-size: 22px; }

      .hero-image { width: 220px; height: 220px; margin-top: 0; }
      .hero-strip { display: none; }
    }

    @media (max-width: 480px) {
      nav { padding: 16px 18px; }
      .nav-logo-text { font-size: 22px; }
      .nav-masuk { padding: 10px 24px; font-size: 17px; }
      .hero-title { font-size: 30px; }
      .hero-title-accent { font-size: 42px; }
      .hero-cta { flex-direction: column; align-items: center; }
      .btn-signup, .btn-mulai { width: 200px; }
    }
  </style>
</head>
<body>

  <!-- ══ NAVBAR ══════════════════════════════════════ -->
  <nav>
    <a href="home.php" class="nav-logo">
      <img
        class="nav-logo-img"
        src="assets/logo.png"
        onerror="this.style.display='none'"
        alt="TahooGa"
      >
      <span class="nav-logo-text">TahooGa</span>
    </a>

    <ul class="nav-links">
      <li><a href="home.php">Beranda</a></li>
      <li><a href="index.php">Level</a></li>
      <li><a href="leaderboard.php">Leaderboard</a></li>
      <li><a href="materi.php">Materi</a></li>
      <li><a href="tentang.php">Tentang</a></li>
    </ul>

    <a href="login.php" class="nav-masuk">Masuk</a>
  </nav>

  <!-- ══ HERO ════════════════════════════════════════ -->
  <section class="hero">

    <!-- Strip blur dekoratif (dari Figma) -->
    <div class="hero-strip"></div>

    <!-- Gambar / Ilustrasi kiri -->
    <div class="hero-image">
      <img
        src="assets/img/hero-mascot.png"
        alt="Tahooga Illustration"
        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
      >
      <div class="hero-image-placeholder" style="display:none;">
        <!-- Icon placeholder -->
        <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="8" y="8" width="48" height="48" rx="12" stroke="#0d0d0d" stroke-width="2"/>
          <circle cx="32" cy="26" r="8" stroke="#0d0d0d" stroke-width="2"/>
          <path d="M16 52c0-8.837 7.163-16 16-16s16 7.163 16 16" stroke="#0d0d0d" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span>Ilustrasi Hero</span>
      </div>
    </div>

    <!-- Teks konten -->
    <div class="hero-content">
      <h1 class="hero-title">
        TahooGa here to make you
        <span class="hero-title-accent">Tahoo</span>
      </h1>

      <p class="hero-subtitle">
        Tantang dirimu dengan kuis interaktif<br>
        dan raih skor terbaikmu.
      </p>

      <div class="hero-cta">
        <!--
          Tombol "Sign up for free" → ke register.php
          Tombol "Mulai"           → ke login.php (user yang sudah daftar langsung mulai)
          Keduanya di halaman terpisah sesuai permintaan
        -->
        <a href="signup.php" class="btn-signup">Sign up for free</a>
        <a href="login.php"    class="btn-mulai">Mulai</a>
      </div>
    </div>

  </section>

</body>
</html>