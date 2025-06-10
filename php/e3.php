<?php
class Formula1Manager {
    private $conn;

    public function __construct() {
        $this->connectDB();
    }

    private function connectDB() {
        $host = 'localhost';
        $username = 'DBUSER2024';
        $password = 'DBPSWD2024';
        $dbname = 'f1_stats';

        $this->conn = new mysqli($host, $username, $password, $dbname);

        if ($this->conn->connect_error) {
            die("Error de conexión");
        }
    }

    public function crearTablas() {
        $sqlFile = 'formula1_db.sql';

        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            if ($this->conn->multi_query($sql)) {
                do {
                    if ($result = $this->conn->store_result()) {
                        $result->free();
                    }
                } while ($this->conn->next_result());
            }
        }
    }

    public function obtenerPilotos() {
        $query = "SELECT piloto_id, CONCAT(nombre, ' ', apellido) AS nombre_completo FROM Pilotos";
        $result = $this->conn->query($query);
        $pilotos = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $pilotos[] = $row;
            }
        }
        return $pilotos;
    }

    public function compararPilotos($piloto1_id, $piloto2_id) {
        $query = "SELECT 
                    p.piloto_id,
                    CONCAT(p.nombre, ' ', p.apellido) AS nombre,
                    p.fecha_nacimiento,
                    p.pais,
                    IFNULL(e.nombre, 'Sin equipo') AS equipo,
                    COALESCE(SUM(r.puntos), 0) AS total_puntos,
                    COUNT(r.resultado_id) AS total_carreras
                  FROM Pilotos p
                  LEFT JOIN Equipos e ON p.equipo_id = e.equipo_id
                  LEFT JOIN Resultados r ON p.piloto_id = r.piloto_id
                  WHERE p.piloto_id IN (?, ?)
                  GROUP BY p.piloto_id, p.nombre, p.apellido, p.fecha_nacimiento, p.pais, e.nombre";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $piloto1_id, $piloto2_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $datosComparacion = [];

        while ($row = $result->fetch_assoc()) {
            $datosComparacion[] = $row;
        }
        return $datosComparacion;
    }

    public function obtenerDatosTabla($tabla) {
        $validTables = ['Pilotos', 'Equipos', 'Circuitos', 'Carreras', 'Resultados'];
        if (!in_array($tabla, $validTables)) {
            return [];
        }

        $query = "SELECT * FROM $tabla";
        $result = $this->conn->query($query);
        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function importarCSV($tabla, $filename) {
        if (($handle = fopen($filename, "r")) !== FALSE) {
            $headers = fgetcsv($handle, 0, ';');

            switch ($tabla) {
                case 'Pilotos':
                    $insertQuery = "INSERT INTO Pilotos (nombre, apellido, fecha_nacimiento, pais, equipo_id) VALUES (?, ?, ?, ?, ?)";
                    break;
                case 'Equipos':
                    $insertQuery = "INSERT INTO Equipos (nombre, pais, fundacion) VALUES (?, ?, ?)";
                    break;
                case 'Circuitos':
                    $insertQuery = "INSERT INTO Circuitos (nombre, pais, longitud, vueltas) VALUES (?, ?, ?, ?)";
                    break;
                case 'Carreras':
                    $insertQuery = "INSERT INTO Carreras (nombre, fecha, circuito_id) VALUES (?, ?, ?)";
                    break;
                case 'Resultados':
                    $insertQuery = "INSERT INTO Resultados (carrera_id, piloto_id, posicion, tiempo, puntos) VALUES (?, ?, ?, ?, ?)";
                    break;
                default:
                    fclose($handle);
                    return; 
            }

            $stmt = $this->conn->prepare($insertQuery);

            while (($data = fgetcsv($handle, 0, ';')) !== FALSE) {
                $data = array_map('trim', $data);

                switch ($tabla) {
                    case 'Pilotos':
                        // data: [piloto_id, nombre, apellido, fecha_nacimiento, pais, equipo_id]
                        if (count($data) < 6) break;
                        $nombre = $data[1];
                        $apellido = $data[2];
                        $fecha_nacimiento = $data[3];
                        $pais = $data[4];
                        $equipo_id = $data[5];
                        $stmt->bind_param("ssssi", $nombre, $apellido, $fecha_nacimiento, $pais, $equipo_id);
                        break;
                    case 'Equipos':
                        // data: [equipo_id, nombre, pais, fundacion]
                        if (count($data) < 4) break;
                        $nombre = $data[1];
                        $pais = $data[2];
                        $fundacion = (int)$data[3];
                        $stmt->bind_param("ssi", $nombre, $pais, $fundacion);
                        break;
                    case 'Circuitos':
                        // data: [circuito_id, nombre, pais, longitud, vueltas]
                        if (count($data) < 5) break;
                        $nombre = $data[1];
                        $pais = $data[2];
                        $longitud = (float)$data[3];
                        $vueltas = (int)$data[4];
                        $stmt->bind_param("ssdi", $nombre, $pais, $longitud, $vueltas);
                        break;
                    case 'Carreras':
                        // data: [carrera_id, nombre, fecha, circuito_id]
                        if (count($data) < 4) break;
                        $nombre = $data[1];
                        $fecha = $data[2];
                        $circuito_id = (int)$data[3];
                        $stmt->bind_param("ssi", $nombre, $fecha, $circuito_id);
                        break;
                    case 'Resultados':
                        // data: [resultado_id, carrera_id, piloto_id, posicion, tiempo, puntos]
                        if (count($data) < 6) break;
                        $carrera_id = (int)$data[1];
                        $piloto_id = (int)$data[2];
                        $posicion = (int)$data[3];
                        $tiempo = $data[4];
                        $puntos = (float)$data[5];
                        $stmt->bind_param("iiisi", $carrera_id, $piloto_id, $posicion, $tiempo, $puntos);
                        break;
                }

                $stmt->execute();
            }

            fclose($handle);
            $stmt->close();
        }
    }
}

$manager = new Formula1Manager();
$pilotos = $manager->obtenerPilotos();
$resultados = [];
$atributo = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['crear_tablas'])) {
        $manager->crearTablas();
    } elseif (isset($_POST['comparar'])) {
        $piloto1_id = $_POST['piloto1'];
        $piloto2_id = $_POST['piloto2'];
        $atributo = $_POST['valor_comparar'] ?? null;

        if ($atributo && in_array($atributo, ['total_carreras', 'total_puntos'])) {
            $resultados = $manager->compararPilotos($piloto1_id, $piloto2_id);
        }
    } elseif (isset($_POST['exportar'])) {
        $tabla = $_POST['tabla'];
        $datos = $manager->obtenerDatosTabla($tabla);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $tabla . '.csv"');

        $output = fopen('php://output', 'w');

        if (!empty($datos)) {
            fputcsv($output, array_keys($datos[0]), ';');

            foreach ($datos as $row) {
                fputcsv($output, $row, ';');
            }
        }

        fclose($output);
        exit;
    } elseif (isset($_POST['importar'])) {
        $tabla = $_POST['tabla'];

        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $filename = $_FILES['csv_file']['tmp_name'];
            $manager->importarCSV($tabla, $filename);
        }
    }
}

$atributo_label = '';
if ($atributo === 'total_carreras') {
    $atributo_label = 'Total Carreras';
} elseif ($atributo === 'total_puntos') {
    $atributo_label = 'Total Puntos';
}
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="author" content="Adrian Dumitru" />
  <meta name="description" content="Gestión de Fórmula 1" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="keywords" content="estadisticas,pilotos" />
  <title>Gestión Fórmula 1 - F1 Desktop</title>
  <link rel="stylesheet" href="../estilo/estilo.css" />
  <link rel="stylesheet" href="../estilo/layout.css" />
  <link rel="stylesheet" href="../estilo/article_elements.css" />
  <link rel="icon" href="multimedia/imagenes/favicon.ico" sizes="16x16" />
</head>
<body>
  <header>
    <h1><a href="../index.html">F1 Desktop</a></h1>
    <nav>
      <a href="../index.html">Inicio</a>
      <a href="../piloto.html">Piloto</a>
      <a href="../noticias.html">Noticias</a>
      <a href="../calendario.html">Calendario</a>
      <a href="../meteorología.html">Meteorología</a>
      <a href="../circuito.html">Circuito</a>
      <a href="viajes.php">Viajes</a>
      <a href="../juegos.html">Juegos</a>
    </nav>
  </header>
  <p>Estas en: <a href="../index.html">Inicio</a>&gt;&gt; <a href="../juegos.html">Juegos</a> &gt;&gt; Gestión Fórmula 1</p>
  
  <main>
    <section>
      <h2>Gestión de Fórmula 1</h2>
      <form method="POST">
        <h3>Crear Tablas</h3>
        <button type="submit" name="crear_tablas">Crear Tablas</button>
      </form>

      <form method="POST" enctype="multipart/form-data">
        <h3>Importar Datos</h3>
        <label for="tabla_importar">Seleccionar Tabla:</label>
        <select id="tabla_importar" name="tabla">
          <option value="Pilotos" label="Pilotos">Pilotos</option>
          <option value="Equipos" label="Equipos">Equipos</option>
          <option value="Circuitos" label="Circuitos">Circuitos</option>
          <option value="Carreras" label="Carreras">Carreras</option>
          <option value="Resultados" label="Resultados">Resultados</option>
        </select>
        
        <label for="csv_file">Archivo CSV:</label>
        <input id="csv_file" type="file" name="csv_file" accept=".csv" required>
        
        <button type="submit" name="importar">Importar</button>
      </form>

      <form method="POST">
        <h3>Exportar Datos</h3>
        <label for="tabla_exportar">Seleccionar Tabla:</label>
        <select id="tabla_exportar" name="tabla">
          <option value="Pilotos" label="Pilotos">Pilotos</option>
          <option value="Equipos" label="Equipos">Equipos</option>
          <option value="Circuitos" label="Circuitos">Circuitos</option>
          <option value="Carreras" label="Carreras">Carreras</option>
          <option value="Resultados" label="Resultados">Resultados</option>
        </select>
        <button type="submit" name="exportar">Exportar</button>
      </form>

      <form method="POST">
        <h3>Comparar Pilotos</h3>
        <label for="piloto1">Seleccionar Piloto 1:</label>
        <select id="piloto1" name="piloto1" required>
          <option value="" label="Seleccione un Piloto">Seleccione un Piloto</option>
          <?php foreach ($pilotos as $piloto): ?>
            <option value="<?= $piloto['piloto_id'] ?>" label="<?= htmlspecialchars($piloto['nombre_completo']) ?>"><?= htmlspecialchars($piloto['nombre_completo']) ?></option>
          <?php endforeach; ?>
        </select>
        
        <label for="piloto2">Seleccionar Piloto 2:</label>
        <select id="piloto2" name="piloto2" required>
          <option value="" label="Seleccione un Piloto">Seleccione un Piloto</option>
          <?php foreach ($pilotos as $piloto): ?>
            <option value="<?= $piloto['piloto_id'] ?>" label="<?= htmlspecialchars($piloto['nombre_completo']) ?>"><?= htmlspecialchars($piloto['nombre_completo']) ?></option>
          <?php endforeach; ?>
        </select>
        
        <label for="valor_comparar">Seleccionar Valor a Comparar:</label>
        <select id="valor_comparar" name="valor_comparar" required>
          <option value="" label="Seleccione un valor">Seleccione un valor</option>
          <option value="total_carreras" label="Total Carreras">Total Carreras</option>
          <option value="total_puntos" label="Total Puntos">Total Puntos</option>
        </select>
        
        <button type="submit" name="comparar">Comparar</button>
      </form>
    </section>

    <?php if (!empty($resultados) && $atributo): ?>
    <section>
      <h2>Resultados de la Comparación</h2>
      <table>
        <thead>
          <tr>
            <th>Piloto</th>
            <th>Fecha de Nacimiento</th>
            <th>País</th>
            <th>Equipo</th>
            <th><?= htmlspecialchars($atributo_label) ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($resultados as $piloto): ?>
          <tr>
            <td><?= htmlspecialchars($piloto['nombre']) ?></td>
            <td><?= htmlspecialchars($piloto['fecha_nacimiento']) ?></td>
            <td><?= htmlspecialchars($piloto['pais']) ?></td>
            <td><?= htmlspecialchars($piloto['equipo']) ?></td>
            <td><?= htmlspecialchars($piloto[$atributo]) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
    <?php endif; ?>
  </main>
</body>
</html>
