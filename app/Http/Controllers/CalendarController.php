<?php

namespace App\Http\Controllers;

use App\DomainClasses\Calendar;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $calendars = Calendar::all()->sortBy('date');

        return view('calendars.index', compact('calendars'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('calendars.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newCalendar = new Calendar();
        $newCalendar->date = $request->year . "-" . $request->month . "-" . $request->day;
        $newCalendar->state = $request->state;
        $newCalendar->save();

        return redirect('/calendars');
    }

    public function range(Request $request)
    {
        $dt = Carbon::createFromDate($request->year1, $request->month1, $request->day1);
        $dtEnd = Carbon::createFromDate($request->year2, $request->month2, $request->day2);

        do
        {
            $newCalendar = new Calendar();
            $newCalendar->date = $dt->format('Y-m-d');
            $newCalendar->state = $request->state;
            $newCalendar->save();

            $dt = $dt->addDay();
        }
        while($dt->lessThanOrEqualTo($dtEnd));



        return redirect('/calendars');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Calendar  $calendar
     * @return \Illuminate\Http\Response
     */
    public function show(Calendar $calendar)
    {
        return view('calendars.show', compact('calendar'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Calendar  $calendar
     * @return \Illuminate\Http\Response
     */
    public function edit(Calendar $calendar)
    {
        return view('calendars.edit', compact('calendar'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Calendar  $calendar
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Calendar $calendar)
    {
        $calendar->date = $request->year . "-" . $request->month . "-" . $request->day;
        $calendar->state = $request->state;
        $calendar->save();

        return redirect('calendars');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Calendar  $calendar
     * @return \Illuminate\Http\Response
     */
    public function destroy(Calendar $calendar)
    {
        $calendarLessons = $calendar->lessons->count();
        $calendarEvents = $calendar->auditorium_events->count();

        if ($calendarLessons !== 0)
        {
            return back()->with('error', 'Нельзя удалить день семестра (' .
                Carbon::createFromFormat('Y-m-d', $calendar->date)->format('d.m.Y') .
                '). На этот день назначены или были назначены занятия.');
        }

        if ($calendarEvents !== 0)
        {
            return back()->with('error', 'Нельзя удалить день семестра (' .
                Carbon::createFromFormat('Y-m-d', $calendar->date)->format('d.m.Y') .
                '). В базе есть отметки о занятости аудиторий в этот день.');
        }

        Calendar::destroy($calendar->id);
        return redirect('calendars');
    }
}
