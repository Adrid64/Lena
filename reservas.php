<?php

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: php/login.php');
    exit();
}

require_once 'php/resource.php';
require_once 'php/reservation.php';


class ReservasController
{
    /* ===== modelos ===== */
    private Resource    $resourceModel;
    private Reservation $reservationModel;

    /* ===== sesión ===== */
    public int    $usuarioId;
    public string $usuarioNombre;

    /* ===== datos de trabajo ===== */
    public array  $recursos       = [];
    public array  $misReservas    = [];
    public ?int   $recursoId      = null;
    public ?int   $reservaSelId   = null;

    /* Precio / formulario */
    public int    $dias           = 1;
    public int    $personas       = 1;
    public ?float $totalPrecio    = null;

    /* Mensajes */
    public bool   $reservaOK      = false;
    public ?array $reservaHecha   = null;
    public string $error          = '';
    public string $mensaje        = '';

    /* ===== CONSTRUCTOR ===== */
    public function __construct()
    {
        $this->resourceModel    = new Resource();
        $this->reservationModel = new Reservation();

        $this->usuarioId     = (int)$_SESSION['usuario_id'];
        $this->usuarioNombre = $_SESSION['usuario_nombre'];

        /* recursos y reservas iniciales */
        $this->recursos    = $this->resourceModel->getAll();
        $this->misReservas = $this->reservationModel->getByUser($this->usuarioId);

        /* filtros GET */
        $this->recursoId    = isset($_GET['recurso_id']) ? (int)$_GET['recurso_id'] : null;
        $this->reservaSelId = isset($_GET['reserva_id']) ? (int)$_GET['reserva_id'] : null;

        /* procesar acciones */
        $this->procesarPeticion();
    }

    /* ===== procesa POST y acciones GET ===== */
    private function procesarPeticion(): void
    {
        /* cancelar reserva (GET) */
        if (isset($_GET['action'], $_GET['reserva']) && $_GET['action'] === 'cancel') {
            $reservaId = (int)$_GET['reserva'];
            if ($this->reservationModel->cancel($reservaId, $this->usuarioId)) {
                $this->misReservas = $this->reservationModel->getByUser($this->usuarioId);
            }
            header('Location: reservas.php');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        /* ---------- calcular precio ---------- */
        if (isset($_POST['calcular_precio'])) {
            $this->dias      = max(1, (int)$_POST['dias']);
            $this->personas  = max(1, (int)$_POST['personas']);
            $this->recursoId = (int)($_GET['recurso_id'] ?? 0);

            $rec = $this->resourceModel->getById($this->recursoId);
            if (!$rec) {
                $this->error = 'Recurso no válido.';
                return;
            }
            $this->totalPrecio = (float)$rec['precio'] * $this->dias * $this->personas;
            return; 
        }

        /* ---------- confirmar reserva ---------- */
        if (isset($_POST['confirmar_reserva'])) {
            $this->dias        = max(1, (int)$_POST['dias']);
            $this->personas    = max(1, (int)$_POST['personas']);
            $this->totalPrecio = (float)$_POST['total_precio'];
            $this->recursoId   = (int)($_GET['recurso_id'] ?? 0);

            $rec = $this->resourceModel->getById($this->recursoId);
            if (!$rec) {
                $this->error = 'Recurso no válido.';
                return;
            }

            $fechaInicio = (new DateTimeImmutable())->format('Y-m-d');
            $horaEvento  = $rec['hora_inicio'];

            $this->reservationModel->create(
                $this->usuarioId,
                $this->recursoId,
                $fechaInicio,
                $this->dias,
                $horaEvento,
                $this->totalPrecio,
                $this->personas
            );

            $this->reservaOK    = true;
            $this->reservaHecha = [
                'dias'      => $this->dias,
                'personas'  => $this->personas,
                'total'     => $this->totalPrecio,
                'fecha'     => $fechaInicio,
                'hora'      => $horaEvento,
            ];

            $this->misReservas = $this->reservationModel->getByUser($this->usuarioId);
        }
    }

    public function recursoSeleccionado(): ?array
    {
        return $this->recursoId ? $this->resourceModel->getById($this->recursoId) : null;
    }
    public function puntosInteres(): array
    {
        return $this->recursoId ? $this->resourceModel->getPuntosInteres($this->recursoId) : [];
    }
}


$ctrl = new ReservasController();   
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Reservas – Lena</title>
  <link rel="stylesheet" href="estilo/estilo.css"/>
  <link rel="stylesheet" href="estilo/layout.css"/>
  <link rel="icon" href="multimedia/imagenes/favicon.ico" sizes="16x16"/>
</head>
<body>
<header>
  <h1><a href="index.html">Lena</a></h1>
  <nav>
    <a href="index.html">Inicio</a><a href="gastronomia.html">Gastronomía</a>
    <a href="rutas.html">Rutas</a><a href="meteorologia.html">Meteorología</a>
    <a href="juego.html">Juego</a><a href="reservas.php">Reservas</a>
    <a href="ayuda.html">Ayuda</a>
  </nav>
</header>
    <p>Estás en: <a href="index.html">Inicio</a> &gt;&gt; Reservas</p>

<main>
  <h2>Bienvenido, <?= htmlspecialchars($ctrl->usuarioNombre) ?></h2>

  <?php if ($ctrl->mensaje): ?>
    <section role="alert"><p><?= htmlspecialchars($ctrl->mensaje) ?></p></section>
  <?php endif; ?>
  <?php if ($ctrl->error): ?>
    <section role="alert"><p><?= htmlspecialchars($ctrl->error) ?></p></section>
  <?php endif; ?>

  <!-- selector de recurso -->
  <section>
    <h3>Recursos para reservar</h3>
    <form method="GET" action="reservas.php">
      <label>Recurso:
        <select name="recurso_id" onchange="this.form.submit()">
          <option value="">-- Selecciona --</option>
          <?php foreach ($ctrl->recursos as $r): ?>
            <option value="<?= $r['id'] ?>" <?= $r['id']===$ctrl->recursoId ? 'selected':'' ?>>
              <?= htmlspecialchars($r['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
    </form>
  </section>

  <?php if ($recSel = $ctrl->recursoSeleccionado()): ?>
    <?php $puntos = $ctrl->puntosInteres(); ?>

    <section>
      <h3>Detalles del Recurso</h3>
      <dl>
        <dt>Nombre:</dt><dd><?= htmlspecialchars($recSel['nombre']) ?></dd>
        <dt>Tipo:</dt><dd><?= htmlspecialchars($recSel['nombre_tipo']) ?></dd>
        <dt>Descripción:</dt><dd><?= htmlspecialchars($recSel['descripcion']) ?></dd>
        <dt>Ocupación:</dt><dd><?= (int)$recSel['limite_ocupacion'] ?></dd>
        <dt>Inicio:</dt><dd><?= htmlspecialchars($recSel['fecha_inicio'].' '.$recSel['hora_inicio']) ?></dd>
        <dt>Fin:</dt><dd><?= htmlspecialchars($recSel['fecha_fin'].' '.$recSel['hora_fin']) ?></dd>
        <dt>Precio:</dt><dd>€<?= number_format((float)$recSel['precio'],2) ?> por día/persona</dd>
        <?php if ($puntos): ?>
          <dt>Puntos de interés:</dt>
          <dd><ul><?php foreach ($puntos as $p) echo '<li>'.htmlspecialchars($p).'</li>'; ?></ul></dd>
        <?php endif; ?>
      </dl>
    </section>

    <section>
      <h3>Reservar</h3>
      <?php if ($ctrl->reservaOK): ?>
        <article>
          <h4>Resumen de la reserva</h4>
          <p>Reserva completada con éxito.</p>
          <p>Días: <?= $ctrl->reservaHecha['dias'] ?></p>
          <p>Personas: <?= $ctrl->reservaHecha['personas'] ?></p>
          <p>Total: €<?= number_format($ctrl->reservaHecha['total'],2) ?></p>
          <p>Fecha: <?= $ctrl->reservaHecha['fecha'] ?> a las <?= $ctrl->reservaHecha['hora'] ?></p>
        </article>
      <?php elseif ($ctrl->totalPrecio !== null): ?>
        <form method="POST" action="reservas.php?recurso_id=<?= $ctrl->recursoId ?>">
          <p><label>Días:      <input name="dias"      value="<?= $ctrl->dias ?>"     type="number" readonly></label></p>
          <p><label>Personas:  <input name="personas"  value="<?= $ctrl->personas ?>" type="number" readonly></label></p>
          <p><label>Total:     <input name="total_precio" value="<?= number_format($ctrl->totalPrecio,2,'.','') ?>" type="text" readonly></label></p>
          <button name="confirmar_reserva" type="submit">Confirmar Reserva</button>
        </form>
      <?php else: ?>
        <form method="POST" action="reservas.php?recurso_id=<?= $ctrl->recursoId ?>">
          <p><label>Días:     <input name="dias"     value="<?= $ctrl->dias ?>"     type="number" min="1" required></label></p>
          <p><label>Personas: <input name="personas" value="<?= $ctrl->personas ?>" type="number" min="1" required></label></p>
          <button name="calcular_precio" type="submit">Calcular Precio</button>
        </form>
      <?php endif; ?>
    </section>
  <?php endif; ?>

  <section>
    <h3>Mis Reservas</h3>
    <?php if (!$ctrl->misReservas): ?>
      <p>No tienes reservas activas.</p>
    <?php else: ?>
      <form method="GET" action="reservas.php">
        <label>Selecciona una:
          <select name="reserva_id" onchange="this.form.submit()">
            <option value="">-- Selecciona --</option>
            <?php foreach ($ctrl->misReservas as $r): ?>
              <option value="<?= $r['id'] ?>" <?= $r['id']===$ctrl->reservaSelId ? 'selected':'' ?>>
                <?= htmlspecialchars($r['recurso_nombre']) ?> (<?= $r['fecha_inicio_evento'] ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </label>
      </form>

      <?php foreach ($ctrl->misReservas as $r): if ($r['id']!=$ctrl->reservaSelId) continue; ?>
        <dl>
          <dt>Recurso:</dt><dd><?= htmlspecialchars($r['recurso_nombre']) ?></dd>
          <dt>Fecha:</dt><dd><?= $r['fecha_inicio_evento'] ?></dd>
          <dt>Días:</dt><dd><?= $r['dias'] ?></dd>
          <dt>Hora:</dt><dd><?= $r['hora_evento'] ?></dd>
          <dt>Personas:</dt><dd><?= $r['numero_personas'] ?></dd>
          <dt>Precio:</dt><dd>€<?= number_format((float)$r['precio_final'],2) ?></dd>
          <dt>Reserva:</dt><dd><?= $r['fecha_reserva'] ?></dd>
          <dt>Acción:</dt>
          <dd><a href="reservas.php?action=cancel&reserva=<?= $r['id'] ?>">Anular</a></dd>
        </dl>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>

  <p><a href="php/logout.php">Cerrar sesión</a></p>
</main>
</body>
</html>
