<?php

namespace App\DomainClasses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ConfigOption extends Model
{
    public $timestamps = false;

    public static function SemesterStarts()
    {
        $ssOption = DB::table('config_options')
            ->where('key', '=', 'Semester Starts')
            ->select('value')
            ->first();

        return ($ssOption !== null) ? $ssOption->value : null;
    }
}
