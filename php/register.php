<?php
// php/register.php
session_start();
require_once 'user.php';

class RegisterController
{

    private User   $user;
    private array  $errores   = [];
    private string $nombre    = '';
    private string $apellidos = '';
    private string $email     = '';


    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handleRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;               
        }

        $this->nombre    = trim($_POST['nombre']     ?? '');
        $this->apellidos = trim($_POST['apellidos']  ?? '');
        $this->email     = trim($_POST['email']      ?? '');
        $pass            =        $_POST['password'] ?? '';
        $pass2           =        $_POST['confirm_pass'] ?? '';

        if ($this->nombre === '' || $this->apellidos === '' ||
            $this->email === ''  || $pass === '' || $pass2 === '') {
            $this->errores[] = 'Todos los campos son obligatorios.';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errores[] = 'El email no es válido.';
        } elseif ($pass !== $pass2) {
            $this->errores[] = 'Las contraseñas no coinciden.';
        }

        if (empty($this->errores)) {
            $nuevoId = $this->user->register($this->nombre, $this->apellidos, $this->email, $pass);

            if ($nuevoId === false) {
                $this->errores[] = 'Ya existe un usuario con ese email.';
            } else {                    // registro correcto
                $_SESSION['usuario_id']     = $nuevoId;
                $_SESSION['usuario_nombre'] = $this->nombre;
                header('Location: ../reservas.php');
                exit;
            }
        }
    }

    public function errores(): array  { return $this->errores; }
    public function nombre(): string  { return $this->nombre; }
    public function apellidos(): string { return $this->apellidos; }
    public function email(): string   { return $this->email; }
}

$controller = new RegisterController(new User());
$controller->handleRequest();
$errores   = $controller->errores();    
$nombre    = $controller->nombre();
$apellidos = $controller->apellidos();
$email     = $controller->email();
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="author" content="Adrian Dumitru" />
    <meta name="description" content="Registro de usuario en la plataforma de reservas" />
    <meta name="keywords" content="registro,usuario,reservas" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="../multimedia/imagenes/favicon.ico" sizes="16x16"/>
    <title>Registro – Lena</title>
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
<p>Estás en: <a href="../index.html">Inicio</a> &gt;&gt; Registro</p>

<main>
  <h2>Registro de Usuario</h2>

  <?php if (!empty($errores)): ?>
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
    <h3>Datos personales</h3>
    <form action="register.php" method="POST">
      <fieldset>
        <p>
          <label>
            Nombre:
            <input type="text" name="nombre"
                   value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>"
                   required>
          </label>
        </p>
        <p>
          <label>
            Apellidos:
            <input type="text" name="apellidos"
                   value="<?= htmlspecialchars($apellidos, ENT_QUOTES, 'UTF-8') ?>"
                   required>
          </label>
        </p>
        <p>
          <label>
            Email:
            <input type="email" name="email"
                   value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                   required>
          </label>
        </p>
        <p>
          <label>
            Contraseña:
            <input type="password" name="password" required>
          </label>
        </p>
        <p>
          <label>
            Confirmar contraseña:
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
