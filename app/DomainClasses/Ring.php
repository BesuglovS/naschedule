<?php

namespace App\DomainClasses;

use Illuminate\Database\Eloquent\Model;

class Ring extends Model
{
    public $timestamps = false;

    public function auditorium_events()
    {
        return $this->hasMany(AuditoriumEvent::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}
