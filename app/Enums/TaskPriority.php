<?php

namespace App\Enums;

enum TaskPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    /**
     * Get the display label for the priority.
     */
    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::Urgent => 'Urgent',
        };
    }

    /**
     * Get the CSS color class for the priority badge.
     */
    public function color(): string
    {
        return match ($this) {
            self::Low => '#22c55e',      // green
            self::Medium => '#eab308',   // yellow
            self::High => '#f97316',     // orange
            self::Urgent => '#ef4444',   // red
        };
    }

    /**
     * Get the background color for the priority badge.
     */
    public function bgColor(): string
    {
        return match ($this) {
            self::Low => '#f0fdf4',
            self::Medium => '#fefce8',
            self::High => '#fff7ed',
            self::Urgent => '#fef2f2',
        };
    }
}
