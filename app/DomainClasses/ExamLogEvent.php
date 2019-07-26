<?php

namespace App\DomainClasses;

use Illuminate\Database\Eloquent\Model;

class ExamLogEvent extends Model
{
    public $timestamps = false;

    public function old_exam()
    {
        return $this->hasOne(Exam::class);
    }

    public function new_exam()
    {
        return $this->hasOne(Exam::class);
    }
}
