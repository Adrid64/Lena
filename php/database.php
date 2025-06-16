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
            error_log("Conexión fallida: " . $this->conn->connect_error);
            exit("Error de conexión a la base de datos.");
        }
        // Establecer conjunto de caracteres
        $this->conn->set_charset("utf8mb4");
    }

    /**
     * Devuelve la instancia de conexión 
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Cierra la conexión 
     */
    public function close() {
        if ($this->conn !== null) {
            $this->conn->close();
        }
    }
}
