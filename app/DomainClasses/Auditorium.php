<?php

namespace App\DomainClasses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Auditorium extends Model
{
    protected $table = 'auditoriums';

    public $timestamps = false;

    public static function allSorted()
    {
        return DB::table('auditoriums')
            ->join('buildings', 'auditoriums.building_id', '=', 'buildings.id')
            ->select('auditoriums.*')
            ->orderBy('buildings.name', 'asc')
            ->orderBy('auditoriums.name', 'asc')
            ->get();
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
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
