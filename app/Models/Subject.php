<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'subject_code',
    ];

    protected $appends = ['icon'];

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }

    public function getIconAttribute(): string
    {
        $name = $this->name ?? '';

        return match (true) {
            str_contains($name, 'Math')          => 'sigma',
            str_contains($name, 'Science')       => 'atom',
            str_contains($name, 'Physics')       => 'atom',
            str_contains($name, 'History')       => 'book-open-variant',
            str_contains($name, 'Geography')     => 'earth',
            str_contains($name, 'ICT')           => 'laptop',
            str_contains($name, 'General Info')  => 'laptop',
            str_contains($name, 'English')       => 'alphabetical',
            str_contains($name, 'General Eng')   => 'alphabetical',
            str_contains($name, 'Sinhala')       => 'translate',
            str_contains($name, 'Tamil')         => 'translate',
            str_contains($name, 'Religion')      => 'hands-pray',
            str_contains($name, 'Music')         => 'music-note',
            str_contains($name, 'Art')           => 'palette',
            str_contains($name, 'Dancing')       => 'human',
            str_contains($name, 'Drama')         => 'drama-masks',
            str_contains($name, 'Business')      => 'chart-timeline-variant',
            str_contains($name, 'Economics')     => 'chart-bar',
            str_contains($name, 'Accounting')    => 'calculator',
            str_contains($name, 'Health')        => 'heart-pulse',
            str_contains($name, 'Agriculture')   => 'sprout',
            str_contains($name, 'Home Science')  => 'home-heart',
            str_contains($name, 'Political')     => 'bank',
            str_contains($name, 'Buddhist')      => 'om',
            str_contains($name, 'Communication') => 'access-point',
            str_contains($name, 'PTS')           => 'clipboard-check',
            str_contains($name, 'Civics')        => 'vote',
            default                              => 'book-outline',
        };
    }
}