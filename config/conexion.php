<?php
class Conexion {
    private static $conexion;

    public static function getConexion() {
        if (!self::$conexion) {
            try {
                self::$conexion = new PDO('mysql:host=localhost;dbname=dogmatch;charset=utf8mb4', 'root', '');
                self::$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die('Error de conexión: ' . $e->getMessage());
            }
        }
        return self::$conexion;
    }
}