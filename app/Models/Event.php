<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class Event extends Model
{
    use HasFactory;
    use BelongsToCompany; // Garante isolamento por empresa se configurado globalmente

    protected $fillable = [
        'user_id',
        'company_id',
        'title',
        'start',
        'end',
        'all_day',
        'url',
        'calendar', // Label
        'location',
        'description',
        'guests',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'all_day' => 'boolean',
        'guests' => 'array',
    ];

    // Para retornar no formato que o FullCalendar espera
    public function toFullCalendarEvent()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->start->toIso8601String(),
            'end' => $this->end->toIso8601String(),
            'allDay' => $this->all_day,
            'url' => $this->url,
            'extendedProps' => [
                'calendar' => $this->calendar,
                'location' => $this->location,
                'description' => $this->description,
                'guests' => $this->guests ?? [],
            ]
        ];
    }
}