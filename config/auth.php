<?php
// config/auth.php

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/database.php';

// Ensure user is logged in
function requireLogin(): array {
    return \App\Middleware\AuthMiddleware::requireLogin();
}

// Ensure user is Super Admin (Admin)
function requireAdmin(): array {
    return \App\Middleware\AuthMiddleware::requireAdmin();
}

// Log Login activity
function logLoginActivity(PDO $db, ?int $userId, string $ipAddress, string $userAgent, string $status): void {
    try {
        $stmt = $db->prepare("INSERT INTO login_logs (user_id, ip_address, user_agent, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $ipAddress, $userAgent, $status]);
    } catch (PDOException $e) {
        // Silent error logging to avoid breaking flow
    }
}

// Log System Audit action
function logAudit(PDO $db, ?int $userId, string $action, string $tableName, ?int $recordId, ?array $oldValues, ?array $newValues): void {
    try {
        $oldJson = $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null;
        $newJson = $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null;

        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $tableName, $recordId, $oldJson, $newJson]);
    } catch (PDOException $e) {
        // Silent failure in logging
    }
}

// Create a Notification
function createNotification(PDO $db, int $userId, string $title, string $message, string $type = 'info'): void {
    try {
        $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $message, $type]);
    } catch (PDOException $e) {
        // Silent notification insertion error
    }
}

// Add a Lead Activity log
function logLeadActivity(PDO $db, int $leadId, int $userId, string $type, string $description): void {
    try {
        $stmt = $db->prepare("INSERT INTO lead_activities (lead_id, user_id, activity_type, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$leadId, $userId, $type, $description]);
    } catch (PDOException $e) {
        // Silent lead activity log error
    }
}
