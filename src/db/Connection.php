<?php

require_once __DIR__.'/../settings/Configuration.php';

//use PDO;

abstract class Connection
{
    private static $_conn;

    private static function createConnection(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s',
            Configuration::get('DB_HOST'),
            Configuration::get('DB_SCHEMA')
        );

        $pdo = new PDO(
            $dsn,
            Configuration::get('DB_USER'),
            Configuration::get('DB_PASSWORD')
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public static function conn(): PDO
    {
        if (!isset(Connection::$_conn)) {
            return Connection::$_conn = Connection::createConnection();
        }

        return Connection::$_conn;
    }
}