<?php

namespace App\DomainClasses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AuditoriumEvent extends Model
{
    public $timestamps = false;

    public static function GetDailyBuildingAuditoriumEvents($calendarId, $buildingId)
    {
        $result = DB::table('auditorium_events')
            ->where('calendar_id', '=', $calendarId)
            ->where('auditoriums.building_id', '=', $buildingId)
            ->join('rings', 'ring_id', '=', 'rings.id')
            ->join('auditoriums', 'auditorium_id', '=', 'auditoriums.id')
            ->select('auditorium_events.name', 'rings.time', 'auditoriums.name as aud_name')
            ->orderBy('rings.time')
            ->get();
        $result->map(function ($lesson) {
            $lesson->time = substr($lesson->time, 0, 5);
        });
        return $result;
    }

    public function calendar()
    {
        $this->belongsTo(Calendar::class);
    }

    public function ring()
    {
        $this->belongsTo(Ring::class);
    }

    public function auditorium()
    {
        $this->belongsTo(Auditorium::class);
    }
}
