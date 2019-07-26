<?php

namespace App\Http\Controllers;

use App\DomainClasses\ConfigOption;
use Illuminate\Http\Request;

class ConfigOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $configOptions = ConfigOption::all()->sortBy('key');

        return view('configOption.index', compact('configOptions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('configOption.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newConfigOption = new ConfigOption();
        $newConfigOption->key = $request->key;
        $newConfigOption->value = $request->value;
        $newConfigOption->save();

        return redirect('/configOptions');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ConfigOption  $configOption
     * @return \Illuminate\Http\Response
     */
    public function show(ConfigOption $configOption)
    {
        return view('configOption.show', compact('configOption'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ConfigOption  $configOption
     * @return \Illuminate\Http\Response
     */
    public function edit(ConfigOption $configOption)
    {
        return view('configOption.edit', compact('configOption'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ConfigOption  $configOption
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ConfigOption $configOption)
    {
        $configOption->key = $request->key;
        $configOption->value = $request->value;
        $configOption->save();

        return redirect('configOptions');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ConfigOption  $configOption
     * @return \Illuminate\Http\Response
     */
    public function destroy(ConfigOption $configOption)
    {
        ConfigOption::destroy($configOption->id);
        return redirect('configOptions');
    }
}
