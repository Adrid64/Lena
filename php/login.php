<?php
// php/login.php

session_start();
require_once "user.php";

class LoginController
{
    private User   $userModel;
    public  string $email   = '';
    public  array  $errores = [];

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
        $this->procesarPeticion();
    }

    /** Atiende la petición POST y, si es correcta, redirige. */
    private function procesarPeticion(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return;                       
        }

        $this->email = trim($_POST["email"] ?? '');
        $pass        = $_POST["password"] ?? '';

        /* Validaciones básicas */
        if ($this->email === '' || $pass === '') {
            $this->errores[] = 'Debe rellenar email y contraseña.';
            return;
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errores[] = 'El email no es válido.';
            return;
        }

        /* Autenticación con el modelo */
        $userData = $this->userModel->login($this->email, $pass);
        if ($userData === false) {
            $this->errores[] = 'Email o contraseña incorrectos.';
            return;
        }

        /* Éxito: se almacena sesión y se redirige */
        $_SESSION["usuario_id"]     = $userData["id"];
        $_SESSION["usuario_nombre"] = $userData["nombre"];
        header("Location: ../reservas.php");
        exit();
    }
}

$login   = new LoginController(new User());
$email   = $login->email;
$errores = $login->errores;
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta name="author" content="Adrian Dumitru"/>
    <meta name="description" content="Inicio de sesión en la plataforma de reservas"/>
    <meta name="keywords" content="login,sesion,reservas"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="icon" href="../multimedia/imagenes/favicon.ico" sizes="16x16"/>

    <title>Iniciar Sesión – Lena</title>
    <link rel="stylesheet" href="../estilo/estilo.css"/>
    <link rel="stylesheet" href="../estilo/layout.css"/>
</head>
<body>
<header>
    <h1><a href="../index.html">Lena</a></h1>
    <nav>
        <a href="../index.html">Inicio</a>
        <a href="../gastronomia.html">Gastronomía</a>
        <a href="../rutas.html">Rutas</a>
        <a href="../meteorologia.html">Meteorología</a>
        <a href="../juego.html">Juego</a>
        <a href="../reservas.php">Reservas</a>
        <a href="../ayuda.html">Ayuda</a>
    </nav>
</header>

<p>Estás en: <a href="../index.html">Inicio</a> &gt;&gt; Iniciar Sesión</p>

<main>
    <h2>Iniciar Sesión</h2>

    <?php if ($errores): ?>
        <section>
            <h3>Error</h3>
            <ul>
                <?php foreach ($errores as $e): ?>
                    <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
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
    <label for="email">Email</label>
    <input  type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
            required>
  </p>

  <p>
    <label for="password">Contraseña</label>
    <input  type="password"
            id="password"
            name="password"
            required>
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
