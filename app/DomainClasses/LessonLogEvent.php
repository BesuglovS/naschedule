<?php

namespace App\DomainClasses;

use Illuminate\Database\Eloquent\Model;

class LessonLogEvent extends Model
{
    public $timestamps = false;

    public function old_lesson()
    {
        return $this->hasOne(Lesson::class);
    }

    public function new_lesson()
    {
        return $this->hasOne(Lesson::class);
    }
}
