<?php
// php/login.php

session_start();
require_once __DIR__ . "/User.php";

$userObj = new User();
$errores = [];
$email   = "";

// 1) Procesar el formulario de inicio de sesión
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $pass  = $_POST["password"] ?? "";

    if ($email === "" || $pass === "") {
        $errores[] = "Debe rellenar email y contraseña.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El email no es válido.";
    } else {
        $userData = $userObj->login($email, $pass);
        if ($userData === false) {
            $errores[] = "Email o contraseña incorrectos.";
        } else {
            $_SESSION["usuario_id"]     = $userData["id"];
            $_SESSION["usuario_nombre"] = $userData["nombre"];
            header("Location: reservas.php");
            exit();
        }
    }
}
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="author" content="Adrian Dumitru" />
    <meta name="description" content="Inicio de sesión en la plataforma de reservas" />
    <meta name="keywords" content="login,sesion,reservas" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Iniciar Sesión – Lena</title>
    <link rel="stylesheet" href="../estilo/estilo.css">
    <link rel="stylesheet" href="../estilo/layout.css">
</head>
<body>
  <header>
    <h1><a href="index.html">Lena</a></h1>
    <nav>
      <a href="../index.html">Inicio</a>
      <a href="../gastronomia.html">Gastronomía</a>
      <a href="../rutas.html">Rutas</a>
      <a href="../meteorología.html">Meteorología</a>
      <a href="../juego.html">Juego</a>
      <a href="reservas.php">Reservas</a>
      <a href="../ayuda.html">Ayuda</a>
    </nav>
  </header>
  <p>Estás en: <a href="../index.html">Inicio</a> &gt;&gt; Iniciar Sesión</p>

  <main>
    <h2>Iniciar Sesión</h2>

    <?php if (!empty($errores)): ?>
    <section role="alert">
      <ul>
        <?php foreach ($errores as $error): ?>
          <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <section>
        <h3>Inicie sesión en su cuenta</h3>
      <form action="login.php" method="POST">
        <fieldset>
          <legend>Credenciales</legend>
          <p>
            <label>
              Email:<br>
              <input type="email" name="email"
                     value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                     required>
            </label>
          </p>
          <p>
            <label>
              Contraseña:<br>
              <input type="password" name="password" required>
            </label>
          </p>
          <p>
            <button type="submit">Entrar</button>
          </p>
        </fieldset>
              <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>.</p>
      </form>

    </section>
  </main>
</body>
</html>
