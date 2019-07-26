<?php

namespace App\Http\Controllers;

use App\DomainClasses\Auditorium;
use App\DomainClasses\AuditoriumEvent;
use App\DomainClasses\Calendar;
use App\DomainClasses\Ring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditoriumEventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $auditoriumEvents = DB::table('auditorium_events')
            ->join('calendars', 'calendars.id' , '=', 'auditorium_events.calendar_id')
            ->join('rings', 'rings.id' , '=', 'auditorium_events.ring_id')
            ->join('auditoriums', 'auditoriums.id' , '=', 'auditorium_events.auditorium_id')
            ->select('auditorium_events.*', 'calendars.date as calendar_date',
                'rings.time as ring_time', 'auditoriums.name as auditoriumName')
            ->orderBy('calendars.date', 'asc')
            ->orderBy('rings.time', 'asc')
            ->orderBy('auditorium_events.name', 'asc')
            ->get();

        return view('auditoriumEvents.index', compact( 'auditoriumEvents'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $calendars = Calendar::all()->sortBy('date');
        $rings = Ring::all()->sortBy('time');
        $auditoriums = Auditorium::allSorted();

        return view('auditoriumEvents.create', compact('calendars', 'rings', 'auditoriums'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $auditoriumEvent = new AuditoriumEvent();
        $auditoriumEvent->name = $request->name;
        $auditoriumEvent->calendar_id = $request->calendar_id;
        $auditoriumEvent->ring_id = $request->ring_id;
        $auditoriumEvent->auditorium_id = $request->auditorium_id;
        $auditoriumEvent->save();

        return redirect('auditoriumEvents');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AuditoriumEvent  $auditoriumEvent
     * @return \Illuminate\Http\Response
     */
    public function show(AuditoriumEvent $auditoriumEvent)
    {
        $auditoriumEvent = DB::table('auditorium_events')
            ->join('calendars', 'calendars.id' , '=', 'auditorium_events.calendar_id')
            ->join('rings', 'rings.id' , '=', 'auditorium_events.ring_id')
            ->join('auditoriums', 'auditoriums.id' , '=', 'auditorium_events.auditorium_id')
            ->select('auditorium_events.*', 'calendars.date as calendar_date',
                'rings.time as ring_time', 'auditoriums.name as auditoriumName')
            ->where('auditorium_events.id', '=', $auditoriumEvent->id)
            ->first();

        return view('auditoriumEvents.show', compact( 'auditoriumEvent'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AuditoriumEvent  $auditoriumEvent
     * @return \Illuminate\Http\Response
     */
    public function edit(AuditoriumEvent $auditoriumEvent)
    {
        $auditoriumEvent = DB::table('auditorium_events')
            ->join('calendars', 'calendars.id' , '=', 'auditorium_events.calendar_id')
            ->join('rings', 'rings.id' , '=', 'auditorium_events.ring_id')
            ->join('auditoriums', 'auditoriums.id' , '=', 'auditorium_events.auditorium_id')
            ->select('auditorium_events.*', 'calendars.date as calendar_date',
                'rings.time as ring_time', 'auditoriums.name as auditorium_name')
            ->where('auditorium_events.id', '=', $auditoriumEvent->id)
            ->first();

        $calendars = Calendar::all()->sortBy('date');
        $rings = Ring::all()->sortBy('time');
        $auditoriums = Auditorium::allSorted();

        return view('auditoriumEvents.edit', compact('auditoriumEvent', 'calendars', 'rings', 'auditoriums'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AuditoriumEvent  $auditoriumEvent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AuditoriumEvent $auditoriumEvent)
    {
        $auditoriumEvent->name = $request->name;
        $auditoriumEvent->calendar_id = $request->calendar_id;
        $auditoriumEvent->ring_id = $request->ring_id;
        $auditoriumEvent->auditorium_id = $request->auditorium_id;
        $auditoriumEvent->save();

        return redirect('auditoriumEvents');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AuditoriumEvent  $auditoriumEvent
     * @return \Illuminate\Http\Response
     */
    public function destroy(AuditoriumEvent $auditoriumEvent)
    {
        AuditoriumEvent::destroy($auditoriumEvent->id);
        return redirect('auditoriumEvents');
    }
}
