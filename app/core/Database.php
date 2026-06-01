<?php

class Database
{
    private static $connection = null;

    public static function connect()
    {
        if (self::$connection === null) {

            try {

                self::$connection = new PDO(
                    "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'],
                    $_ENV['DB_USER'],
                    $_ENV['DB_PASS']
                );

                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            } catch (PDOException $e) {

                die("Database Connection Failed : " . $e->getMessage());
            }
        }

        return self::$connection;
    }
}