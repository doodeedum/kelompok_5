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
    $kelas    = $_POST['kelas'] ?? '';

    if (empty($nickname)) {
        $error = 'Nama tidak boleh kosong.';
    } elseif (strlen($nickname) < 3) {
        $error = 'Nama minimal 3 karakter.';
    } elseif (empty($kelas)) {
        $error = 'Pilih kelas terlebih dahulu.';
    } else {
        $cek = $pdo->prepare("SELECT id FROM users WHERE nickname = ?");
        $cek->execute([$nickname]);

        if ($cek->rowCount() > 0) {
            $error = 'Nama sudah dipakai, coba nama lain.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (nickname, kelas) VALUES (?, ?)");
            $stmt->execute([$nickname, $kelas]);

            $_SESSION['user_id']  = $pdo->lastInsertId();
            $_SESSION['nickname'] = $nickname;
            $_SESSION['kelas']    = $kelas;

            header("Location: index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up — TahooGa</title>
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

    /* ── BLUR STRIP dekoratif (dari Figma) ──────────── */
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

    /* ── PAGE WRAPPER ───────────────────────────────── */
    .page {
      position: relative;
      z-index: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 0 20px 140px;
    }

    /* ── CARD SIGNUP ────────────────────────────────── */
    .signup-card {
      background: #fff;
      border: 1px solid var(--black);
      border-radius: 30px;
      width: 100%;
      max-width: 508px;
      padding: 44px 52px 52px;
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
    }

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

    /* Label */
    .input-label {
      font-family: 'Itim', cursive;
      font-size: 32px;
      color: var(--black);
      margin-bottom: 10px;
      display: block;
    }

    /* Input teks */
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
      margin-bottom: 28px;
    }

    .input-field:focus {
      border-color: #c8a000;
      box-shadow: 0 0 0 3px rgba(249,243,131,0.5);
      background: #fff;
    }

    .input-field::placeholder { color: #bbb; }

    /* Select kelas — sama styling dengan input */
    .select-wrap { position: relative; margin-bottom: 36px; }

    .select-wrap::after {
      content: '▾';
      position: absolute;
      right: 28px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 20px;
      color: #999;
      pointer-events: none;
    }

    .input-select {
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
      appearance: none;
      cursor: pointer;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    .input-select:focus {
      border-color: #c8a000;
      box-shadow: 0 0 0 3px rgba(249,243,131,0.5);
      background: #fff;
    }

    /* Tombol Sign up */
    .btn-signup {
      width: 100%;
      height: 80px;
      background: var(--yellow);
      border: 1px solid var(--black);
      border-radius: 30px;
      font-family: 'Itim', cursive;
      font-size: 32px;
      color: var(--black);
      cursor: pointer;
      transition: transform 0.18s, box-shadow 0.18s;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .btn-signup:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 32px rgba(249,243,131,0.75);
    }

    /* ── LINK LOGIN ─────────────────────────────────── */
    .login-link {
      margin-top: 36px;
      text-align: center;
    }

    .login-link a {
      font-family: 'Itim', cursive;
      font-size: 36px;
      color: var(--black);
      position: relative;
      transition: opacity 0.2s;
    }

    .login-link a::after {
      content: '';
      position: absolute;
      bottom: -4px; left: 0;
      width: 0; height: 3px;
      background: var(--yellow);
      border-radius: 2px;
      transition: width 0.25s;
    }

    .login-link a:hover { opacity: 0.7; }
    .login-link a:hover::after { width: 100%; }

    /* ── FOOTER DECO (logo kiri bawah dari Figma) ────── */
    .footer-deco {
      position: fixed;
      bottom: 0; left: 0;
      display: flex;
      align-items: flex-end;
      gap: 0;
      padding: 0 0 16px 0;
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
      padding-bottom: 20px;
      padding-left: 4px;
    }

    /* ── RESPONSIVE ─────────────────────────────────── */
    @media (max-width: 768px) {
      nav { padding: 20px 24px; gap: 20px; }
      .nav-beranda { font-size: 18px; }
      .nav-keluar  { font-size: 18px; padding: 10px 24px; }

      .page { padding: 0 16px 100px; }
      .signup-card { padding: 36px 28px 40px; }
      .card-greeting { font-size: 24px; margin-bottom: 24px; }
      .input-label { font-size: 24px; }
      .input-field,
      .input-select { height: 64px; font-size: 17px; padding: 0 20px; border-radius: 20px; }
      .btn-signup { height: 64px; font-size: 26px; border-radius: 20px; }
      .login-link a { font-size: 26px; }
      .page-strip { display: none; }
      .footer-deco { display: none; }
    }

    @media (max-width: 480px) {
      .signup-card { padding: 28px 20px 32px; border-radius: 20px; }
      .card-greeting { font-size: 20px; }
    }
  </style>
</head>
<body>

  <!-- Blur strip dekoratif -->
  <div class="page-strip"></div>

  <!-- ══ NAVBAR ════════════════════════════════════ -->
  <nav>
    <a href="home.php" class="nav-beranda">Beranda</a>
    <a href="home.php" class="nav-keluar">Keluar</a>
  </nav>

  <!-- ══ KONTEN UTAMA ══════════════════════════════ -->
  <div class="page">

    <div class="signup-card">

      <p class="card-greeting">Silahkan isi data berikut</p>

      <?php if ($error): ?>
        <div class="error-box"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">

        <!-- Nama Lengkap -->
        <label class="input-label" for="nickname">Nama Lengkap</label>
        <input
          class="input-field"
          type="text"
          id="nickname"
          name="nickname"
          placeholder="Masukkan nickname kamu..."
          value="<?= htmlspecialchars($_POST['nickname'] ?? '') ?>"
          maxlength="50"
          required
          autofocus
        >

        <!-- Kelas -->
        <label class="input-label" for="kelas">Kelas</label>
        <div class="select-wrap">
          <select class="input-select" id="kelas" name="kelas" required>
            <option value="">-- Pilih Kelas --</option>
            <?php
            $kelas_list = ['X PPLG','X TJKT','XI PPLG','XI TJKT','XII PPLG','XII TJKT'];
            foreach ($kelas_list as $k):
                $sel = (($_POST['kelas'] ?? '') === $k) ? 'selected' : '';
            ?>
            <option value="<?= $k ?>" <?= $sel ?>><?= $k ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Tombol -->
        <button type="submit" class="btn-signup">Sign up for free</button>

      </form>

    </div>

    <!-- Link ke login -->
    <div class="login-link">
      <a href="login.php">Sudah Punya Akun? Login</a>
    </div>

  </div>

  <!-- ══ FOOTER DECO (kiri bawah dari Figma) ════════ -->
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