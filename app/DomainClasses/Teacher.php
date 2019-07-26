<?php

namespace App\DomainClasses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Teacher extends Model
{
    public $timestamps = false;

    public function disciplines()
    {
        return $this->belongsToMany(Discipline::class, 'discipline_teacher')->withPivot('id');
    }

    public static function OrderByFio()
    {
        return DB::table('teachers')
            ->orderBy('fio', 'asc')
            ->get();
    }

    public static function IdAndFioList()
    {
        return DB::table('teachers')
            ->select('id', 'fio')
            ->orderBy('fio', 'asc')
            ->get();
    }
}
