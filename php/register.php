<?php
// php/register.php

session_start();
require_once __DIR__ . "/User.php";

$userObj   = new User();
$errores   = [];
$nombre    = "";
$apellidos = "";
$email     = "";

// 1) Procesar el formulario de registro
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre    = trim($_POST["nombre"]   ?? "");
    $apellidos = trim($_POST["apellidos"]?? "");
    $email     = trim($_POST["email"]    ?? "");
    $pass      = $_POST["password"]      ?? "";
    $pass2     = $_POST["confirm_pass"]  ?? "";

    if ($nombre === "" || $apellidos === "" || $email === "" || $pass === "" || $pass2 === "") {
        $errores[] = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El email no es válido.";
    } elseif ($pass !== $pass2) {
        $errores[] = "Las contraseñas no coinciden.";
    } else {
        $newId = $userObj->register($nombre, $apellidos, $email, $pass);
        if ($newId === false) {
            $errores[] = "Ya existe un usuario con ese email.";
        } else {
            $_SESSION["usuario_id"]     = $newId;
            $_SESSION["usuario_nombre"] = $nombre;
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
    <meta name="description" content="Registro de usuario en la plataforma de reservas" />
    <meta name="keywords" content="registro,usuario,reservas" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registro – Lena</title>
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
      <a href="../meteorologia.html">Meteorología</a>
      <a href="../juego.html">Juego</a>
      <a href="reservas.php">Reservas</a>
      <a href="../ayuda.html">Ayuda</a>
    </nav>
  </header>
  <p>Estás en: <a href="../index.html">Inicio</a> &gt;&gt; Registro</p>

  <main>
    <h2>Registro de Usuario</h2>

    <?php if (!empty($errores)): ?>
    <section >
      <ul>
        <?php foreach ($errores as $error): ?>
          <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <section>
       <h3>Datos personales</h3> 
      <form action="register.php" method="POST">
        <fieldset>
          <p>
            <label>
              Nombre:<br>
              <input type="text" name="nombre"
                     value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>"
                     required>
            </label>
          </p>
          <p>
            <label>
              Apellidos:<br>
              <input type="text" name="apellidos"
                     value="<?= htmlspecialchars($apellidos, ENT_QUOTES, 'UTF-8') ?>"
                     required>
            </label>
          </p>
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
            <label>
              Confirmar contraseña:<br>
              <input type="password" name="confirm_pass" required>
            </label>
          </p>
          <p>
            <button type="submit">Registrar</button>
          </p>
        </fieldset>
         <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>.</p>
      </form>
       

    </section>
  </main>
</body>
</html>
