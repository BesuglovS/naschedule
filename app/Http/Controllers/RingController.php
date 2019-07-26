<?php

namespace App\Http\Controllers;

use App\DomainClasses\Ring;
use Illuminate\Http\Request;

class RingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rings = Ring::all()->sortBy('time');

        return view('rings.index', compact('rings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('rings.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newRing = new Ring();
        $newRing->time = $request->hours . ":" . $request->minutes . ":00";
        $newRing->save();

        return redirect('/rings');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Ring  $ring
     * @return \Illuminate\Http\Response
     */
    public function show(Ring $ring)
    {
        return view('rings.show', compact('ring'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Ring  $ring
     * @return \Illuminate\Http\Response
     */
    public function edit(Ring $ring)
    {
        return view('rings.edit', compact('ring'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Ring  $ring
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Ring $ring)
    {
        $ring->time = $request->hours . ":" . $request->minutes . ":00";

        $ring->save();

        return redirect('rings');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Ring  $ring
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ring $ring)
    {
        $ringLessons = $ring->lessons->count();
        $ringEvents = $ring->auditorium_events->count();

        if ($ringLessons !== 0)
        {

            return back()->with('error', 'Нельзя удалить время начала урока (' . mb_substr($ring->time, 0, 5) . '). На это время назначены или были назначены занятия.');
        }

        if ($ringEvents !== 0)
        {
            return back()->with('error', 'Нельзя удалить время начала урока (' . mb_substr($ring->time, 0, 5) . '). В базе есть отметки об её занятости аудитории в это время.');
        }

        Ring::destroy($ring->id);
        return redirect('rings');
    }
}
