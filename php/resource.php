<?php
// php/Resource.php
require_once "database.php";

class Resource {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Devuelve todos los recursos (o filtra por tipo, si se pasa $tipoId).
     * Retorna un array de arrays asociativos con campos de recurso y tipo.
     */
    public function getAll($tipoId = null) {
        if ($tipoId === null) {
            $sql  = "SELECT r.*, t.nombre_tipo
                     FROM recursos_turisticos r
                     JOIN tipo_recursos t ON r.tipo_id = t.id
                     ORDER BY r.nombre ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        } else {
            $sql  = "SELECT r.*, t.nombre_tipo
                     FROM recursos_turisticos r
                     JOIN tipo_recursos t ON r.tipo_id = t.id
                     WHERE r.tipo_id = ?
                     ORDER BY r.nombre ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $tipoId);
            $stmt->execute();
        }

        $result   = $stmt->get_result();
        $recursos = [];
        while ($fila = $result->fetch_assoc()) {
            $recursos[] = $fila;
        }
        $stmt->close();
        return $recursos;
    }

    /**
     * Obtiene un recurso por su ID.
     */
    public function getById($id) {
        $stmt = $this->conn->prepare(
            "SELECT r.*, t.nombre_tipo
             FROM recursos_turisticos r
             JOIN tipo_recursos t ON r.tipo_id = t.id
             WHERE r.id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res      = $stmt->get_result();
        $recurso  = $res->fetch_assoc();
        $stmt->close();
        return $recurso;
    }

    /**
     * Obtener puntos de interés asociados a un recurso.
     */
    public function getPuntosInteres($recursoId) {
        $sql  = "SELECT texto FROM puntos_interes WHERE recurso_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error en prepare(): " . $this->conn->error);
        }
        $stmt->bind_param("i", $recursoId);
        $stmt->execute();
        $res    = $stmt->get_result();
        $puntos = [];
        while ($row = $res->fetch_assoc()) {
            $puntos[] = $row['texto'];
        }
        $stmt->close();
        return $puntos;
    }
}
