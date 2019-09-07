<?php

namespace App\Http\Controllers;

use App\DomainClasses\Calendar;
use App\DomainClasses\Faculty;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function facultyDowChoice() {
        $faculties = Faculty::all()->sortBy('sorting_order');
        $weekCount = Calendar::WeekCount();

        return view('pdf.choice', compact('faculties', 'weekCount'));
    }
    public function facultyDow(Request $request) {
        $dowRu = [
            '1' => 'Понедельник',
            '2' => 'Вторник',
            '3' => 'Среда',
            '4' => 'Четверг',
            '5' => 'Пятница',
            '6' => 'Суббота',
            '7' => 'Воскресенье'
        ];

        $oac = new OldApiController();
        $input = $request->all();
        $input["weeks"] = $input["week"];
        $schedule = $oac->GetFacultyWeeksSchedule($input);

        $facultyId = $input["facultyId"];
        $dow = $input["dow"];
        $week = $input["week"];
        $facultyName = Faculty::find($facultyId)->name;

        $scheduleRings = array();

        foreach ($schedule as $groupSchedule) {
            foreach (array_keys($groupSchedule["lessons"][$dow]) as $time) {
                if (!in_array($time, $scheduleRings)) {
                    $scheduleRings[] = $time;
                }
            }
        }

        usort($scheduleRings, function ($a, $b) {
            $aVal = intval(substr($a, 0, 2)) * 60 + intval(substr($a, 3, 2));
            $bVal = intval(substr($b, 0, 2)) * 60 + intval(substr($b, 3, 2));

            if ($aVal === $bVal) return 0;
            return $aVal < $bVal? -1 : 1;
        });

        $mainFontSize = 12;

        $data = [
            'title' => $dowRu[$dow] . ' (' . $week . ')' . ' - ' . $facultyName,
            'schedule' => $schedule,
            'scheduleRings' => $scheduleRings,
            'dow' => $dow,
            'mainFontSize' => $mainFontSize . 'px'
        ];

        //return $data;

        PDF::setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif']);

        do {
            $pdf = PDF::loadView('pdf.facultyDow', $data)->setPaper('a4', 'landscape');
            $a = $pdf->stream('medium.pdf', array('Attachment' => 0));
            $pageCount = $pdf->dompdf->get_canvas()->get_page_count();
            $mainFontSize -= 0.5;
            $data["mainFontSize"] = $mainFontSize . "px";
        } while($pageCount > 1);

        return $pdf->stream('medium.pdf', array('Attachment' => 0));
    }
}
