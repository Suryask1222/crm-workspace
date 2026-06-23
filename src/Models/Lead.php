<?php
// src/Models/Lead.php

namespace App\Models;

class Lead {
    public int $id;
    public string $name;
    public ?string $phone;
    public ?string $email;
    public ?string $company;
    public ?string $industry;
    public ?string $address;
    public ?string $source;
    public string $priority; // low, medium, high
    public string $status; // new, contacted, follow_up, qualified, etc.
    public ?int $assigned_to;
    public float $expected_value;
    public string $created_at;
    public string $updated_at;
    public ?string $assigned_name = null;
    public ?string $assigned_email = null;
}
