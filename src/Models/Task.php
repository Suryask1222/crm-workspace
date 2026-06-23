<?php
// src/Models/Task.php

namespace App\Models;

class Task {
    public int $id;
    public string $title;
    public ?string $description;
    public int $assigned_to;
    public int $created_by;
    public string $priority; // low, medium, high
    public string $status; // todo, in_progress, completed
    public ?string $due_date;
    public string $created_at;
    public string $updated_at;
    public ?string $assigned_name = null;
}
