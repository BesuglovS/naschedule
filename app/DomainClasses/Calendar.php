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

    public static function CarbonDayOfWeek($date)
    {
        switch ($date->dayOfWeek) {
            case Carbon::MONDAY:    return 1;
            case Carbon::TUESDAY:   return 2;
            case Carbon::WEDNESDAY: return 3;
            case Carbon::THURSDAY:  return 4;
            case Carbon::FRIDAY:    return 5;
            case Carbon::SATURDAY:  return 6;
            case Carbon::SUNDAY:    return 7;
        }

        return -1;
    }

    public static function IdsFromDowAndWeeks($dow, $weeks)
    {
        $semesterStarts = Carbon::parse(ConfigOption::SemesterStarts());

        $calendars = Calendar::all()->toArray();

        $result = array_filter($calendars, function($calendar) use ($dow, $weeks, $semesterStarts) {
            $calendarWeek = Calendar::WeekFromDate($calendar["date"], $semesterStarts);
            $calendarDow = Calendar::CarbonDayOfWeek(Carbon::createFromFormat('Y-m-d', $calendar["date"]));

            return (in_array($calendarWeek, $weeks)) && ($calendarDow == $dow);
        });

        $result = array_column($result, 'id');

        return $result;
    }

    public static function IdsByWeekFromDowAndWeeks($dow, $weeks)
    {
        $semesterStarts = Carbon::parse(ConfigOption::SemesterStarts());

        $calendars = Calendar::all()->toArray();

        $result = array();
        foreach ($calendars as $calendar) {
            $calendarWeek = Calendar::WeekFromDate($calendar["date"], $semesterStarts);
            $calendarDow = Calendar::CarbonDayOfWeek(Carbon::createFromFormat('Y-m-d', $calendar["date"]));

            if ((in_array($calendarWeek, $weeks)) && ($calendarDow == $dow)) {
                $result[$calendarWeek] = $calendar["id"];
            }
        }

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

    public static function IdsByWeekAndDowFromDowsAndWeeks(array $dows, array $weeks)
    {
        $semesterStarts = Carbon::parse(ConfigOption::SemesterStarts());

        $calendars = Calendar::all()->toArray();

        $result = array();
        foreach ($calendars as $calendar) {
            $calendarWeek = Calendar::WeekFromDate($calendar["date"], $semesterStarts);
            $calendarDow = Calendar::CarbonDayOfWeek(Carbon::createFromFormat('Y-m-d', $calendar["date"]));

            if ((in_array($calendarWeek, $weeks)) && (in_array($calendarDow, $dows))) {

                if (!array_key_exists($calendarWeek, $result)) {
                    $result[$calendarWeek] = array();
                }

                $result[$calendarWeek][$calendarDow] = $calendar["id"];
            }
        }

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
