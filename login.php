<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $user = $_POST['username'];
  $pass = $_POST['password'];

  if ($user === "admin" && $pass === "kine1234") {
    $_SESSION['admin'] = true;
    header("Location: admin.php");
    exit();
  } else {
    $erreur = "Identifiants incorrects.";
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>PhysioActiv - Connexion Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #0d0d1a;
      color: white;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
      position: relative;
      overflow: hidden;
    }

    /* Effet décoratif en arrière-plan */
    body::before {
      content: '';
      position: absolute;
      top: -100px;
      right: -100px;
      width: 400px;
      height: 400px;
      background: radial-gradient(circle, rgba(26, 107, 181, 0.15), transparent 70%);
      border-radius: 50%;
      pointer-events: none;
    }

    body::after {
      content: '';
      position: absolute;
      bottom: -150px;
      left: -150px;
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(26, 107, 181, 0.1), transparent 70%);
      border-radius: 50%;
      pointer-events: none;
    }

    .login-container {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 420px;
    }

    /* Logo / Branding */
    .login-brand {
      text-align: center;
      margin-bottom: 30px;
    }

    .login-brand .icon {
      width: 64px;
      height: 64px;
      background: linear-gradient(135deg, #1a6bb5, #155a9a);
      border-radius: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px;
      box-shadow: 0 10px 30px rgba(26, 107, 181, 0.4);
    }

    .login-brand .icon i {
      font-size: 28px;
      color: white;
    }

    .login-brand h1 {
      font-size: 22px;
      font-weight: 600;
      margin-bottom: 6px;
    }

    .login-brand h1 span {
      color: #1a6bb5;
    }

    .login-brand p {
      color: #888;
      font-size: 13px;
    }

    /* Boîte de connexion */
    .login-box {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(26, 107, 181, 0.2);
      backdrop-filter: blur(10px);
      padding: 40px 36px;
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    }

    .login-box h2 {
      font-size: 20px;
      margin-bottom: 8px;
      text-align: center;
    }

    .login-box .subtitle {
      color: #888;
      font-size: 13px;
      margin-bottom: 30px;
      text-align: center;
    }

    /* Erreur */
    .erreur {
      background: rgba(231, 76, 60, 0.1);
      border: 1px solid rgba(231, 76, 60, 0.3);
      color: #e74c3c;
      padding: 12px 16px;
      border-radius: 10px;
      font-size: 13px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .erreur i {
      font-size: 16px;
    }

    /* Champs */
    .form-group {
      margin-bottom: 18px;
    }

    .form-group label {
      display: block;
      color: #1a6bb5;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-bottom: 8px;
    }

    .input-wrapper {
      position: relative;
    }

    .input-wrapper i {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: #1a6bb5;
      font-size: 14px;
    }

    input {
      width: 100%;
      padding: 14px 18px 14px 44px;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(26, 107, 181, 0.3);
      border-radius: 10px;
      font-size: 14px;
      outline: none;
      color: white;
      transition: all 0.3s;
    }

    input::placeholder {
      color: #666;
    }

    input:focus {
      border-color: #1a6bb5;
      background: rgba(26, 107, 181, 0.08);
      box-shadow: 0 0 0 3px rgba(26, 107, 181, 0.15);
    }

    /* Bouton */
    button {
      width: 100%;
      padding: 14px;
      background: #1a6bb5;
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-top: 10px;
      box-shadow: 0 4px 16px rgba(26, 107, 181, 0.3);
    }

    button:hover {
      background: #155a9a;
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(26, 107, 181, 0.5);
    }

    /* Footer */
    .login-footer {
      text-align: center;
      margin-top: 24px;
      color: #666;
      font-size: 12px;
    }

    .login-footer a {
      color: #1a6bb5;
      text-decoration: none;
    }

    .login-footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="login-container">

    <div class="login-brand">
      <div class="icon">
        <i class="fas fa-user-shield"></i>
      </div>
      <h1>PhysioActiv <span>By SS</span></h1>
      <p>Espace administration</p>
    </div>

    <div class="login-box">
      <h2>Connexion</h2>
      <p class="subtitle">Veuillez entrer vos identifiants pour accéder à l'espace</p>

      <?php if (isset($erreur)): ?>
        <div class="erreur">
          <i class="fas fa-exclamation-circle"></i>
          <span><?= $erreur ?></span>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label for="username">Nom d'utilisateur</label>
          <div class="input-wrapper">
            <i class="fas fa-user"></i>
            <input type="text" id="username" name="username" placeholder="Votre identifiant" required autofocus />
          </div>
        </div>

        <div class="form-group">
          <label for="password">Mot de passe</label>
          <div class="input-wrapper">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" placeholder="••••••••" required />
          </div>
        </div>

        <button type="submit">
          <i class="fas fa-sign-in-alt"></i> Se connecter
        </button>
      </form>
    </div>

    <div class="login-footer">
      <a href="index.html"><i class="fas fa-arrow-left"></i> Retour au site</a>
    </div>

  </div>

</body>
</html>