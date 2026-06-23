<?php
// src/Models/Followup.php

namespace App\Models;

class Followup {
    public int $id;
    public int $lead_id;
    public int $user_id;
    public string $title;
    public ?string $description;
    public string $scheduled_at;
    public string $status; // pending, completed, missed
    public string $created_at;
    public ?string $lead_name = null;
    public ?string $lead_company = null;
    public ?string $staff_name = null;
}
