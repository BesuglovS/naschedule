<?php

namespace App\DomainClasses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ConfigOption extends Model
{
    public $timestamps = false;

    public static function SemesterStarts()
    {
        return DB::table('config_options')
            ->where('key', '=', 'Semester Starts')
            ->select('value')
            ->first()->value;
    }
}
