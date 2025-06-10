<?php
session_start();
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/Resource.php";
require_once __DIR__ . "/Reservation.php";

$resObj        = new Resource();
$rvObj         = new Reservation();
$usuarioId     = $_SESSION["usuario_id"];
$usuarioNombre = $_SESSION["usuario_nombre"];

$error       = "";
$mensaje     = "";
$totalPrecio = null;
$dias        = 1;
$personas    = 1;
$recursos    = $resObj->getAll();
$misReservas = $rvObj->getByUser($usuarioId);

// Anulación
if (isset($_GET["action"], $_GET["reserva"]) && $_GET["action"] === "cancel") {
    $reservaId = intval($_GET["reserva"]);
    if ($rvObj->cancel($reservaId, $usuarioId)) {
        $misReservas = $rvObj->getByUser($usuarioId);
    }
    header("Location: reservas.php");
    exit();
}

// Calcular precio
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["calcular_precio"])) {
    $recursoId  = intval($_POST["recurso_id"]);
    $dias       = max(1, intval($_POST["dias"]));
    $personas   = max(1, intval($_POST["personas"]));
    $recursoSel = $resObj->getById($recursoId);

    if (!$recursoSel) {
        $error = "Recurso no válido.";
    } else {
        $totalPrecio = $recursoSel["precio"] * $dias * $personas;
    }
}

// Confirmar reserva
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["reservar"])) {
    $recursoId   = intval($_POST["recurso_id"]);
    $dias        = max(1, intval($_POST["dias"]));
    $personas    = max(1, intval($_POST["personas"]));
    $totalPrecio = floatval($_POST["total_precio"] ?? 0);
    $recursoSel  = $resObj->getById($recursoId);

    if (!$recursoSel) {
        $error = "Recurso no válido.";
    } else {
        $fechaInicio = (new DateTimeImmutable())->format("Y-m-d");
        $horaEvento  = $recursoSel["hora_inicio"];
        $rvObj->create(
            $usuarioId,
            $recursoId,
            $fechaInicio,
            $dias,
            $horaEvento,
            $totalPrecio,
            $personas
        );
        header("Location: reservas.php");
        exit();
    }
}
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="author" content="Adrian Dumitru" />
  <meta name="description" content="Zona de reservas" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reservas – Lena</title>
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
  <p>Estás en: <a href="../index.html">Inicio</a> &gt;&gt; Reservas</p>

  <main>
    <h2>Bienvenido, <?= htmlspecialchars($usuarioNombre, ENT_QUOTES, 'UTF-8') ?></h2>

    <?php if ($mensaje): ?>
      <section role="alert"><p><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p></section>
    <?php endif; ?>
    <?php if ($error): ?>
      <section role="alert"><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p></section>
    <?php endif; ?>

    <section>
      <h3>Recursos disponibles</h3>
      <?php if (empty($recursos)): ?>
        <p>No hay recursos turísticos disponibles.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Tipo</th>
              <th>Descripción</th>
              <th>Límite</th>
              <th>Inicio</th>
              <th>Fin</th>
              <th>Precio (€)</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($recursos as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r["nombre"], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($r["nombre_tipo"], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($r["descripcion"], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= intval($r["limite_ocupacion"]) ?></td>
              <td><?= htmlspecialchars($r["fecha_inicio"], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($r["hora_inicio"], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($r["fecha_fin"], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($r["hora_fin"], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= number_format($r["precio"], 2) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>

    <section>
      <h3>Reservar recurso</h3>
      <form action="reservas.php" method="POST">
        <p>
          <label>Recurso:<br>
            <select name="recurso_id" required>
              <?php foreach ($recursos as $r): ?>
                <option value="<?= $r["id"] ?>" <?= isset($recursoId) && $recursoId == $r["id"] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($r["nombre"], ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>
        </p>
        <p>
          <label>Días:<br>
            <input type="number" name="dias" value="<?= htmlspecialchars($dias, ENT_QUOTES, 'UTF-8') ?>" min="1" required>
          </label>
        </p>
        <p>
          <label>Personas:<br>
            <input type="number" name="personas" value="<?= htmlspecialchars($personas, ENT_QUOTES, 'UTF-8') ?>" min="1" required>
          </label>
        </p>
        <?php if (isset($recursoId)):
          $rsel = $resObj->getById($recursoId); ?>
          <p>Precio por día y persona: €<?= number_format($rsel["precio"], 2) ?></p>
        <?php endif; ?>
        <?php if ($totalPrecio !== null): ?>
          <p>Total a pagar: €<?= number_format($totalPrecio, 2) ?> por <?= intval($dias) ?> día(s) y <?= intval($personas) ?> persona(s)</p>
        <?php endif; ?>
        <?php if ($totalPrecio === null): ?>
          <button type="submit" name="calcular_precio">Calcular Precio</button>
        <?php else: ?>
          <input type="hidden" name="total_precio" value="<?= number_format($totalPrecio,2,'.','') ?>">
          <input type="hidden" name="personas" value="<?= intval($personas) ?>">
          <button type="submit" name="reservar">Confirmar Reserva</button>
        <?php endif; ?>
      </form>
    </section>

    <section>
  <h3>Mis Reservas</h3>
  <?php if (empty($misReservas)): ?>
    <p>No tienes reservas activas.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Recurso</th>
          <th>Fecha inicio</th>
          <th>Días</th>
          <th>Hora evento</th>
          <th>Personas</th> 
          <th>Precio (€)</th>
          <th>Fecha reserva</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($misReservas as $mr): ?>
        <tr>
          <td><?= htmlspecialchars($mr["recurso_nombre"], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($mr["fecha_inicio_evento"], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= intval($mr["dias"]) ?></td>
          <td><?= htmlspecialchars($mr["hora_evento"], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= intval($mr["numero_personas"]) ?></td> 
          <td><?= number_format($mr["precio_final"], 2) ?></td>
          <td><?= htmlspecialchars($mr["fecha_reserva"], ENT_QUOTES, 'UTF-8') ?></td>
          <td><a href="reservas.php?action=cancel&reserva=<?= $mr["id"] ?>">Anular</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>


    <p><a href="logout.php">Cerrar sesión</a></p>
  </main>
</body>
</html>
