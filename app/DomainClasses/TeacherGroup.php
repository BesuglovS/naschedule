<?php

namespace App\DomainClasses;

use Illuminate\Database\Eloquent\Model;

class TeacherGroup extends Model
{
    public $timestamps = false;

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, "teacher_teacher_group")->withPivot('id');
    }
}
