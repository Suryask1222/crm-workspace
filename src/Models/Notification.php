<?php
// src/Models/Notification.php

namespace App\Models;

class Notification {
    public int $id;
    public int $user_id;
    public string $title;
    public string $message;
    public string $type; // success, warning, info
    public int $is_read; // 1 or 0
    public string $created_at;
}
