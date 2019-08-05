<?php

namespace App\DomainClasses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Building extends Model
{
    public $timestamps = false;

    public function auditoriums()
    {
        return $this->hasMany(Auditorium::class);
    }

    public static function AuditoriumIds(int $buildingId)
    {
        return DB::table('auditoriums')
            ->where('auditoriums.building_id', '=', $buildingId)
            ->pluck('auditoriums.id');
    }
}
