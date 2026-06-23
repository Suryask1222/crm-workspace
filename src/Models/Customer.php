<?php
// src/Models/Customer.php

namespace App\Models;

class Customer {
    public int $id;
    public ?int $lead_id;
    public string $name;
    public ?string $phone;
    public ?string $email;
    public ?string $company;
    public float $total_purchases;
    public int $purchase_count;
    public string $status; // active, inactive
    public string $created_at;
    public ?string $assigned_name = null;
}
