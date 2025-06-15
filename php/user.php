<?php
// php/User.php
require_once "database.php";

class User {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function __destruct() {
    }

    /**
     * Registra un usuario. Devuelve el ID si tiene éxito, o false si hay error (email ya usado o fallo).
     */
    public function register($nombre, $apellidos, $email, $password) {
        // 1. Comprobar si el email existe
        $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->close();
            return false; // email ya usado
        }
        $stmt->close();

        // 2. Insertar
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO usuarios (nombre, apellidos, email, password_hash) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $apellidos, $email, $hash);
        if ($stmt->execute()) {
            $id = $stmt->insert_id;
            $stmt->close();
            return $id;
        } else {
            $stmt->close();
            return false;
        }
    }

    /**
     * Verifica login: devuelve array asociativo con datos del usuario si OK, o false si no.
     */
    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT id, nombre, password_hash FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows !== 1) {
            $stmt->close();
            return false;
        }
        $stmt->bind_result($id, $nombre, $hash);
        $stmt->fetch();
        if (password_verify($password, $hash)) {
            $stmt->close();
            return ["id" => $id, "nombre" => $nombre];
        } else {
            $stmt->close();
            return false;
        }
    }

    /**
     * Obtiene la información de un usuario por su ID
     */

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT id, nombre, apellidos, email, fecha_registro FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();
        return $user;
    }
}
