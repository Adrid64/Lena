<?php
// php/Reservation.php

class Reservation
{
    private $db;

    public function __construct()
    {
        $host   = "localhost";
        $user   = "DBUSER2025";
        $pass   = "DBPWD2025";
        $dbname = "reservas_turismo";

        $this->db = new mysqli($host, $user, $pass, $dbname);
        if ($this->db->connect_errno) {
            die("Error de conexión MySQL: " . $this->db->connect_error);
        }
        $this->db->set_charset("utf8mb4");
    }

    /**
     * Inserta una reserva (varios días).
     */
    public function create($usuarioId, $recursoId, $fechaInicio, $dias, $horaEvento, $precioFinal, $numeroPersonas)
    {
        $sql = "INSERT INTO reservas (
                    usuario_id, recurso_id, fecha_inicio_evento, dias, hora_evento,
                    precio_final, numero_personas, fecha_reserva
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_DATE)";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        // Se ajusta bind_param para reflejar 7 variables
        $stmt->bind_param(
            "iisisdi",
            $usuarioId,
            $recursoId,
            $fechaInicio,
            $dias,
            $horaEvento,
            $precioFinal,
            $numeroPersonas
        );

        $exito = $stmt->execute();
        $stmt->close();
        return $exito;
    }

    /**
     * Devuelve todas las reservas del usuario, incluyendo el nombre del recurso.
     */
    public function getByUser($usuarioId)
    {
        $sql = "
            SELECT 
                r.id,
                r.fecha_inicio_evento,
                r.dias,
                r.hora_evento,
                r.precio_final,
                r.numero_personas,
                r.fecha_reserva,
                r.estado,
                rt.nombre AS recurso_nombre
            FROM reservas r
            JOIN recursos_turisticos rt ON r.recurso_id = rt.id
            WHERE r.usuario_id = ?
            ORDER BY r.fecha_inicio_evento DESC
        ";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $reservas  = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $reservas;
    }

    /**
     * Elimina una reserva de la tabla (si pertenece al usuario).
     */
    public function cancel($reservaId, $usuarioId)
    {
        $sql = "
            DELETE FROM reservas
            WHERE id = ?
              AND usuario_id = ?
        ";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("ii", $reservaId, $usuarioId);
        $stmt->execute();
        $filasAfectadas = $stmt->affected_rows;
        $stmt->close();
        return ($filasAfectadas > 0);
    }
}
