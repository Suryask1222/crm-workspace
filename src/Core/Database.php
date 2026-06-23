<?php
// src/Core/Database.php

namespace App\Core;

use PDO;

class Database {
    private static ?PDO $connection = null;

    public static function getConnection(): PDO {
        if (self::$connection === null) {
            // Retrieve PDO using legacy config method to retain custom db settings
            self::$connection = getDBConnection();
        }
        return self::$connection;
    }
}
