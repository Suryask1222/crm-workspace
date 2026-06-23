<?php
// src/Repositories/UserRepository.php

namespace App\Repositories;

use App\Core\Database;
use App\Models\User;
use PDO;

class UserRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email): ?User {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name AS role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.email = ?
        ");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return $this->mapRowToModel($row);
    }

    public function findById(int $id): ?User {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name AS role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return $this->mapRowToModel($row);
    }

    public function getAllUsers(): array {
        $stmt = $this->db->query("
            SELECT u.*, r.name AS role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            ORDER BY u.created_at DESC
        ");
        $rows = $stmt->fetchAll();
        $users = [];
        foreach ($rows as $row) {
            $users[] = $this->mapRowToModel($row);
        }
        return $users;
    }

    public function getActiveUsersList(): array {
        $stmt = $this->db->query("SELECT id, name FROM users WHERE status = 'active' ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function getRoles(): array {
        $stmt = $this->db->query("SELECT id, name FROM roles ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function create(string $name, string $email, string $hashedPassword, int $roleId): int {
        $stmt = $this->db->prepare("INSERT INTO users (role_id, name, email, password, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([$roleId, $name, $email, $hashedPassword]);
        return (int)$this->db->lastInsertId();
    }

    public function updateStatus(int $id, string $status): bool {
        $stmt = $this->db->prepare("UPDATE users SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    private function mapRowToModel(array $row): User {
        $user = new User();
        $user->id = (int)$row['id'];
        $user->role_id = (int)$row['role_id'];
        $user->name = $row['name'];
        $user->email = $row['email'];
        $user->password = $row['password'];
        $user->status = $row['status'];
        $user->two_factor_secret = $row['two_factor_secret'] ?? null;
        $user->created_at = $row['created_at'];
        $user->updated_at = $row['updated_at'];
        $user->role_name = $row['role_name'] ?? null;
        return $user;
    }
}
