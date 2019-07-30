<?php

namespace App\DomainClasses;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Calendar extends Model
{
    public $timestamps = false;

    public static function IdfromDate($date)
    {
        $id = DB::table('calendars')
            ->where('date', '=', $date)
            ->select('id')
            ->first();

        $result = ($id == null) ? "" : $id->id;

        return $result;
    }

    public static function IdsFromWeek($week)
    {
        $startOfWeek = Carbon::parse(ConfigOption::SemesterStarts())
            ->startOfWeek()->addWeeks($week - 1);
        $endOfWeek = $startOfWeek->copy()->endOfWeek();
        return DB::table('calendars')
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->select('id')
            ->get()
            ->map(function($item) { return $item->id;});
    }

    public static function GetWeekNumber($date = null)
    {
        if (is_null($date))
        {
            $date = Carbon::now();
        }
        else {
            $date = Carbon::createFromFormat("Y-m-d", $date);
        }
        $ss = Carbon::parse(ConfigOption::SemesterStarts());
        $diff = $ss->diffInDays($date);
        $week = 1 + (int)($diff / 7);
        return $week;
    }

    public static function WeekFromDate($date, $semesterStarts)
    {
        $date = Carbon::createFromFormat("Y-m-d", $date);

        $diff = $semesterStarts->diffInDays($date);
        $week = 1 + (int)($diff / 7);
        return $week;
    }

    public static function IdsFromWeeks($weeks)
    {
        $semesterStarts = Carbon::parse(ConfigOption::SemesterStarts());

        $calendars = Calendar::all()->toArray();

        $result = array_filter($calendars, function($calendar) use ($weeks, $semesterStarts) {
            $lessonWeek = Calendar::WeekFromDate($calendar["date"], $semesterStarts);

            return in_array($lessonWeek, $weeks);
        });

        $result=array_column($result, 'id');

        return $result;

    }

    public static function WeekCount()
    {
        if (Calendar::all()->count() == 0) {
            return 0;
        }

        $lastDate = DB::table('calendars')->orderBy('date', 'desc')->first()->date;

        $lastDateWeek = Calendar::WeekFromDate($lastDate, Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts()));

        return $lastDateWeek;
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
