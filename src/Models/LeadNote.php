<?php
// src/Models/LeadNote.php

namespace App\Models;

class LeadNote {
    public int $id;
    public int $lead_id;
    public int $user_id;
    public string $note;
    public int $is_internal; // 1 or 0
    public string $created_at;
    public ?string $user_name = null;
}
