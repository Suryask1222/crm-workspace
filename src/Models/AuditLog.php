<?php
// src/Models/AuditLog.php

namespace App\Models;

class AuditLog {
    public int $id;
    public ?int $user_id;
    public string $action;
    public string $table_name;
    public ?int $record_id;
    public ?string $old_values; // JSON string
    public ?string $new_values; // JSON string
    public string $created_at;
    public ?string $user_name = null;
}
