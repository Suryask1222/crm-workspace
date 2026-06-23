<?php
// src/Models/LeadActivity.php

namespace App\Models;

class LeadActivity {
    public int $id;
    public int $lead_id;
    public int $user_id;
    public string $activity_type;
    public string $description;
    public string $created_at;
    public ?string $user_name = null;
    public ?string $lead_name = null;
}
