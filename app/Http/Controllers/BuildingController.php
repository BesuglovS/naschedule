<?php

namespace App\Http\Controllers;

use App\DomainClasses\Auditorium;
use App\DomainClasses\Building;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Building[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        $buildings = Building::all()->sortBy('name');

        return view('building.index', compact('buildings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('building.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newBuilding = new Building();
        $newBuilding->name = $request->name;
        $newBuilding->save();

        return redirect('/buildings');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Building  $building
     * @return \Illuminate\Http\Response
     */
    public function show(Building $building)
    {
        $auditoriums = $building->auditoriums->sortBy('name');

        return view('building.show', compact('building', 'auditoriums'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Building  $building
     * @return \Illuminate\Http\Response
     */
    public function edit(Building $building)
    {
        return view('building.edit', compact('building'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Building  $building
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Building $building)
    {
        $building->name = $request->name;

        $building->save();

        return redirect('buildings');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Building  $building
     * @return \Illuminate\Http\Response
     */
    public function destroy(Building $building)
    {
        $buildingAuditoriumsCount = $building->auditoriums->count();

        if ($buildingAuditoriumsCount == 0) {
            Building::destroy($building->id);
            return redirect('buildings');
        } else {
            return back()->with('error', 'Нельзя удалить корпус (' . $building->name . '). В корпусе есть аудитории.');
        }
    }
}
