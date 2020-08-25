<?php

namespace App\Http\Controllers;

use App\DomainClasses\Auditorium;
use App\DomainClasses\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditoriumController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $auditoriums = DB::table('auditoriums')
            ->join('buildings', 'buildings.id' , '=', 'auditoriums.building_id')
            ->select('auditoriums.*', 'buildings.name as buildingName')
            ->orderBy('buildingName', 'asc')
            ->orderBy('auditoriums.name', 'asc')
            ->get();

        return view('auditoriums.index', compact('auditoriums'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $buildings = Building::all();

        return view('auditoriums.create', compact('buildings'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newAuditorium = new Auditorium();
        $newAuditorium->name = $request->name;
        $newAuditorium->building_id = $request->building_id;
        $newAuditorium->capacity = $request->capacity;
        $newAuditorium->save();

        return redirect('/auditoriums');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Auditorium  $auditorium
     * @return \Illuminate\Http\Response
     */
    public function show(Auditorium $auditorium)
    {
        $auditorium = DB::table('auditoriums')
            ->where('auditoriums.id', '=', $auditorium->id)
            ->join('buildings', 'buildings.id' , '=', 'auditoriums.building_id')
            ->select('auditoriums.*', 'buildings.name as buildingName')
            ->first();

        return view('auditoriums.show', compact('auditorium'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Auditorium  $auditorium
     * @return \Illuminate\Http\Response
     */
    public function edit(Auditorium $auditorium)
    {
        $auditorium = DB::table('auditoriums')
            ->where('auditoriums.id', '=', $auditorium->id)
            ->join('buildings', 'buildings.id' , '=', 'auditoriums.building_id')
            ->select('auditoriums.*', 'buildings.name as buildingName')
            ->first();

        $buildings = Building::all();

        return view('auditoriums.edit', compact('auditorium', 'buildings'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Auditorium  $auditorium
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Auditorium $auditorium)
    {
        $auditorium->name = $request->name;
        $auditorium->building_id = $request->building_id;
        $auditorium->capacity = $request->capacity;
        $auditorium->save();

        return redirect('auditoriums');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Auditorium  $auditorium
     * @return \Illuminate\Http\Response
     */
    public function destroy(Auditorium $auditorium)
    {
        $auditoriumLessons = $auditorium->lessons->count();
        $auditoriumEvents = $auditorium->auditorium_events->count();

        if ($auditoriumLessons !== 0)
        {
            return back()->with('error', 'Нельзя удалить аудиторию (' . $auditorium->name . '). В ней есть или были занятия.');
        }

        if ($auditoriumEvents !== 0)
        {
            return back()->with('error', 'Нельзя удалить аудиторию (' . $auditorium->name . '). В базе есть отметки об её занятости.');
        }

        Auditorium::destroy($auditorium->id);
        return redirect('auditoriums');
    }
}
