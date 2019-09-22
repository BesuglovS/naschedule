<?php

namespace App\DomainClasses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Ring extends Model
{
    public $timestamps = false;

    public static function IdfromTime($time)
    {
        if (mb_strlen($time) === 5) {
            $time = $time . ":00";
        }

        $id = DB::table('rings')
            ->where('time', '=', $time)
            ->select('id')
            ->first();

        $result = ($id == null) ? "" : $id->id;

        return $result;
    }

    public function auditorium_events()
    {
        return $this->hasMany(AuditoriumEvent::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}
