<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';

    /**
     * Get the display label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Completed => 'Completed',
        };
    }

    /**
     * Get the CSS color for the status badge.
     */
    public function color(): string
    {
        return match ($this) {
            self::Pending => '#f59e0b',   // amber
            self::Completed => '#22c55e', // green
        };
    }

    /**
     * Get the background color for the status badge.
     */
    public function bgColor(): string
    {
        return match ($this) {
            self::Pending => '#fffbeb',
            self::Completed => '#f0fdf4',
        };
    }

    /**
     * Get the icon for the status.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Pending => '⏳',
            self::Completed => '✅',
        };
    }
}
