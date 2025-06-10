<?php
// php/Database.php

class Database {
    private $host = "localhost";
    private $user = "DBUSER2025";
    private $pass = "DBPWD2025";
    private $dbname = "reservas_turismo";
    private $conn;

    public function __construct() {
        // Crear conexión
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
        // Comprobar errores
        if ($this->conn->connect_error) {
            die("Conexión fallida: " . $this->conn->connect_error);
        }
        // Establecer conjunto de caracteres
        $this->conn->set_charset("utf8mb4");
    }

    /**
     * Devuelve la instancia de conexión (mysqli)
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Cierra la conexión (opcional)
     */
    public function close() {
        if ($this->conn !== null) {
            $this->conn->close();
        }
    }
}
