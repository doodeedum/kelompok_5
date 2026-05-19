<?php
session_start();
include 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = trim($_POST['nickname'] ?? '');

    if (empty($nickname)) {
        $error = 'Nama tidak boleh kosong.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE nickname = ?");
        $stmt->execute([$nickname]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['nickname'] = $user['nickname'];
            $_SESSION['kelas']    = $user['kelas'];
            header("Location: index.php");
            exit;
        } else {
            $error = 'Nama tidak ditemukan. Belum punya akun?';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — TahooGa</title>
  <link href="https://fonts.googleapis.com/css2?family=Joan&family=Itim&family=Just+Me+Again+Down+Here&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    :root {
      --yellow:      #F9F383;
      --yellow-pale: #FFFDD1;
      --cream:       #FFFFFA;
      --black:       #0d0d0d;
      --gray:        #666;
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

    a { text-decoration: none; color: inherit; }

    /* ── NAVBAR ─────────────────────────────────────── */
    nav {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      padding: 32px 56px;
      gap: 40px;
      position: relative;
      z-index: 10;
    }

    .nav-beranda {
      font-family: 'Joan', serif;
      font-size: 24px;
      color: var(--black);
      transition: opacity 0.2s;
    }
    .nav-beranda:hover { opacity: 0.6; }

    .nav-keluar {
      font-family: 'Joan', serif;
      font-size: 24px;
      color: var(--black);
      background: var(--yellow);
      border-radius: 30px;
      padding: 12px 36px;
      transition: transform 0.18s, box-shadow 0.18s;
      display: inline-block;
    }
    .nav-keluar:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(249,243,131,0.8);
    }

    /* ── BLUR STRIP (dari Figma) ────────────────────── */
    .page-strip {
      position: fixed;
      left: 0; right: 0;
      top: 252px;
      height: 627px;
      background: rgba(255,255,255,0.04);
      box-shadow: 4px 4px 4px rgba(0,0,0,0.04),
                  0 4px 4px var(--yellow);
      outline: 2px solid rgba(255,250,250,0.6);
      backdrop-filter: blur(2px);
      -webkit-backdrop-filter: blur(2px);
      z-index: 0;
      pointer-events: none;
    }

    /* ── HALAMAN UTAMA ──────────────────────────────── */
    .page {
      position: relative;
      z-index: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      padding: 0 20px 120px;
      min-height: calc(100vh - 100px);
    }

    /* ── CARD LOGIN ─────────────────────────────────── */
    .login-card {
      background: #fff;
      border: 1px solid var(--black);
      border-radius: 30px;
      width: 100%;
      max-width: 508px;
      padding: 48px 52px 52px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.08);
      animation: fadeUp 0.5s ease both;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .card-greeting {
      font-family: 'Joan', serif;
      font-size: 32px;
      font-weight: 400;
      color: var(--black);
      margin-bottom: 32px;
      text-align: left;
    }

    /* Label input */
    .input-label {
      font-family: 'Itim', cursive;
      font-size: 32px;
      color: var(--black);
      margin-bottom: 10px;
      display: block;
    }

    /* Input field */
    .input-field {
      width: 100%;
      height: 80px;
      background: var(--cream);
      border: 1px solid var(--black);
      border-radius: 30px;
      padding: 0 28px;
      font-size: 20px;
      font-family: 'Inter', sans-serif;
      color: var(--black);
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
      margin-bottom: 24px;
    }

    .input-field:focus {
      border-color: #c8a000;
      box-shadow: 0 0 0 3px rgba(249,243,131,0.5);
      background: #fff;
    }

    .input-field::placeholder { color: #bbb; }

    /* Error */
    .error-box {
      background: #fff0f0;
      border: 1.5px solid #ffaaaa;
      border-radius: 14px;
      padding: 12px 20px;
      color: #cc0000;
      font-size: 15px;
      margin-bottom: 20px;
      text-align: center;
    }
    .error-box a { color: #cc0000; font-weight: 700; text-decoration: underline; }

    /* Tombol Login */
    .btn-login {
      width: 100%;
      height: 80px;
      background: var(--yellow);
      border: 1px solid var(--black);
      border-radius: 30px;
      font-family: 'Itim', cursive;
      font-size: 48px;
      color: var(--black);
      cursor: pointer;
      transition: transform 0.18s, box-shadow 0.18s;
      display: flex;
      align-items: center;
      justify-content: center;
      line-height: 1;
    }

    .btn-login:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 32px rgba(249,243,131,0.75);
    }

    /* ── DAFTAR LINK ────────────────────────────────── */
    .daftar-link {
      margin-top: 36px;
      text-align: center;
    }

    .daftar-link a {
      font-family: 'Itim', cursive;
      font-size: 36px;
      color: var(--black);
      position: relative;
      transition: opacity 0.2s;
    }

    .daftar-link a::after {
      content: '';
      position: absolute;
      bottom: -4px; left: 0;
      width: 0; height: 3px;
      background: var(--yellow);
      border-radius: 2px;
      transition: width 0.25s;
    }

    .daftar-link a:hover { opacity: 0.7; }
    .daftar-link a:hover::after { width: 100%; }

    /* ── FOOTER DEKORATIF (dari Figma: logo kiri bawah) */
    .footer-deco {
      position: fixed;
      bottom: 0; left: 0;
      display: flex;
      align-items: flex-end;
      gap: 8px;
      padding: 0 0 20px 12px;
      z-index: 0;
      pointer-events: none;
    }

    .footer-deco img {
      width: 169px;
      height: 151px;
      object-fit: contain;
      opacity: 0.85;
    }

    .footer-logo-text {
      font-family: 'Just Me Again Down Here', cursive;
      font-size: 48px;
      color: var(--black);
      padding-bottom: 16px;
    }

    /* ── RESPONSIVE ─────────────────────────────────── */
    @media (max-width: 768px) {
      nav { padding: 20px 24px; gap: 24px; }
      .nav-beranda { font-size: 18px; }
      .nav-keluar  { font-size: 18px; padding: 10px 24px; }

      .page { padding: 0 16px 80px; }
      .login-card { padding: 36px 28px 40px; }
      .card-greeting { font-size: 24px; }
      .input-label { font-size: 24px; }
      .input-field { height: 64px; font-size: 17px; padding: 0 20px; }
      .btn-login { height: 64px; font-size: 36px; }
      .daftar-link a { font-size: 26px; }
      .page-strip { display: none; }
      .footer-deco { display: none; }
    }

    @media (max-width: 480px) {
      .login-card { padding: 28px 20px 32px; border-radius: 20px; }
      .card-greeting { font-size: 20px; margin-bottom: 24px; }
    }
  </style>
</head>
<body>

  <!-- BLUR STRIP dekoratif -->
  <div class="page-strip"></div>

  <!-- ══ NAVBAR ════════════════════════════════════ -->
  <nav>
    <a href="home.php" class="nav-beranda">Beranda</a>
    <a href="home.php" class="nav-keluar">Keluar</a>
  </nav>

  <!-- ══ KONTEN UTAMA ══════════════════════════════ -->
  <div class="page">

    <div class="login-card">

      <p class="card-greeting">Selamat Datang kembali!</p>

      <?php if ($error): ?>
        <div class="error-box">
          <?= $error ?>
          <?php if (str_contains($error, 'tidak ditemukan')): ?>
            <br><a href="signup.php">Daftar di sini →</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <form method="POST">

        <label class="input-label" for="nickname">Nama Lengkap</label>
        <input
          class="input-field"
          type="text"
          id="nickname"
          name="nickname"
          placeholder="Masukkan nickname kamu..."
          value="<?= htmlspecialchars($_POST['nickname'] ?? '') ?>"
          required
          autofocus
        >

        <button type="submit" class="btn-login">Login</button>

      </form>

    </div>

    <!-- Link daftar -->
    <div class="daftar-link">
      <a href="signup.php">Belum Punya Akun? Daftar Disini</a>
    </div>

  </div>

  <!-- ══ FOOTER DECO (logo kiri bawah dari Figma) ═══ -->
  <div class="footer-deco">
    <img
      src="assets/logo.png"
      alt="TahooGa"
      onerror="this.style.display='none'"
    >
    <span class="footer-logo-text">TahooGa</span>
  </div>

</body>
</html>