<?php
// src/Repositories/SettingRepository.php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class SettingRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getSettings(): array {
        $stmt = $this->db->query("SELECT key_name, value_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['key_name']] = $row['value_value'];
        }
        return $settings;
    }

    public function getByKey(string $key, ?string $default = null): ?string {
        $stmt = $this->db->prepare("SELECT value_value FROM settings WHERE key_name = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    }

    public function saveSettings(array $settings): bool {
        try {
            $stmt = $this->db->prepare("INSERT INTO settings (key_name, value_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value_value = ?");
            foreach ($settings as $key => $value) {
                $stmt->execute([$key, $value, $value]);
            }
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }
}
