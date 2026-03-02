<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialYear extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'short_name',
        'start_date',
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean'
    ];

    // Scope to get active financial years
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope to get financial years within a date range
    // public function scopeWithinDateRange($query, $startDate, $endDate)
    // {
    //     return $query->where(function($q) use ($startDate, $endDate) {
    //         $q->whereBetween('start_date', [$startDate, $endDate])
    //           ->orWhereBetween('end_date', [$startDate, $endDate])
    //           ->orWhere(function($q2) use ($startDate, $endDate) {
    //               $q2->where('start_date', '<=', $startDate)
    //                  ->where('end_date', '>=', $endDate);
    //           });
    //     });
    // }

    public function scopeWithinDateRange($query, $startDate, $endDate)
{
    return $query->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
        $q->where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function($q2) use ($startDate, $endDate) {
                  $q2->where('start_date', '<=', $startDate)
                     ->where('end_date', '>=', $endDate);
              });
        });
    });
}

    // Check if a date falls within this financial year
    public function containsDate($date)
    {
        $date = \Carbon\Carbon::parse($date);
        return $date->between($this->start_date, $this->end_date);
    }

    // Get financial years for a project period
    public static function getForProjectPeriod($startDate, $endDate)
    {
        return self::active()
            ->withinDateRange($startDate, $endDate)
            ->orderBy('start_date')
            ->get();
    }

    // Get the current financial year (contains today's date)
    public static function getCurrentFinancialYear()
    {
        return self::active()
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();
    }

    // Format display
    public function getDisplayNameAttribute()
    {
        return $this->name . ($this->short_name ? " ({$this->short_name})" : '');
    }
}
