<?php
// src/Helpers/Formatter.php

namespace App\Helpers;

class Formatter {
    public static function date(string $datetime): string {
        return date('M j, Y', strtotime($datetime));
    }

    public static function dateTime(string $datetime): string {
        return date('M j, Y, g:i A', strtotime($datetime));
    }

    public static function shortDateTime(string $datetime): string {
        return date('M j, g:i A', strtotime($datetime));
    }

    public static function money(float $amount, string $symbol = '₹'): string {
        return $symbol . number_format($amount, 2);
    }

    public static function badgeClass(string $status): string {
        return 'badge badge-' . htmlspecialchars($status);
    }

    public static function priorityClass(string $priority): string {
        return 'priority-' . htmlspecialchars($priority);
    }

    public static function formatStatus(string $status): string {
        return htmlspecialchars(str_replace('_', ' ', $status));
    }
}
