<?php
// src/Models/User.php

namespace App\Models;

class User {
    public int $id;
    public int $role_id;
    public string $name;
    public string $email;
    public string $password;
    public string $status;
    public ?string $two_factor_secret;
    public string $created_at;
    public string $updated_at;
    public ?string $role_name = null;
}
