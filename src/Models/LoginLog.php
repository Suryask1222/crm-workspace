<?php
// src/Models/LoginLog.php

namespace App\Models;

class LoginLog {
    public int $id;
    public ?int $user_id;
    public string $ip_address;
    public ?string $user_agent;
    public string $login_time;
    public string $status; // success, failed
    public ?string $user_name = null;
    public ?string $user_email = null;
}
