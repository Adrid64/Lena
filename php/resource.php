<?php
// php/Resource.php
require_once "Database.php";

class Resource {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function __destruct() {
        // No cerramos aquí intencionadamente
    }

    /**
     * Devuelve todos los recursos (o filtra por tipo, si se pasa $tipoId).
     * Retorna un array de arrays asociativos con campos de recurso y tipo.
     */
    public function getAll($tipoId = null) {
        if ($tipoId === null) {
            $sql = "SELECT r.*, t.nombre_tipo 
                    FROM recursos_turisticos r
                    JOIN tipo_recursos t ON r.tipo_id = t.id
                    ORDER BY r.nombre ASC";
            $result = $this->conn->query($sql);
        } else {
            $sql = "SELECT r.*, t.nombre_tipo 
                    FROM recursos_turisticos r
                    JOIN tipo_recursos t ON r.tipo_id = t.id
                    WHERE r.tipo_id = ?
                    ORDER BY r.nombre ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $tipoId);
            $stmt->execute();
            $result = $stmt->get_result();
        }
        $recursos = [];
        while ($fila = $result->fetch_assoc()) {
            $recursos[] = $fila;
        }
        if (isset($stmt)) {
            $stmt->close();
        }
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
        $res = $stmt->get_result();
        $recurso = $res->fetch_assoc();
        $stmt->close();
        return $recurso;
    }

    /**
     * (Opcional) Obtener recursos multimedia asociados.
     */
    public function getMultimedia($recursoId) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM recursos_multimedia WHERE recurso_id = ?"
        );
        $stmt->bind_param("i", $recursoId);
        $stmt->execute();
        $res = $stmt->get_result();
        $media = [];
        while ($f = $res->fetch_assoc()) {
            $media[] = $f;
        }
        $stmt->close();
        return $media;
    }
}
