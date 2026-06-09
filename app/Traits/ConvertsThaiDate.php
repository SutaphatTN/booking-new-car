<?php

namespace App\Traits;

trait ConvertsThaiDate
{
    private function toGregorian(?string $date): ?string
    {
        if (!$date) return null;
        $year = (int) substr($date, 0, 4);
        return $year >= 2500 ? ($year - 543) . substr($date, 4) : $date;
    }
}
