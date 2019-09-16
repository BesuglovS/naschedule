<?php
namespace App\Http\Controllers;

use App\DomainClasses\Calendar;
use App\DomainClasses\ConfigOption;
use App\DomainClasses\Faculty;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use PDFMerger;

class PdfController extends Controller
{
    public function facultyDowChoice() {

        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();

        $faculties = Faculty::all()->sortBy('sorting_order');
        $weekCount = Calendar::WeekCount();

        $weeks = array();
        for($w = 1; $w <= $weekCount; $w++) {
            $start = $css->clone();
            $end = $start->clone()->addDays(6);
            $weeks[$w] = $start->format("d.m") . " - " . $end->format('d.m');

            $css = $css->addWeek();
        }

        $today = CarbonImmutable::now()->format('Y-m-d');
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();
        $currentWeek = Calendar::WeekFromDate($today, $css);
        $dowRu = array("Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота");

        return view('pdf.choice', compact('faculties', 'weekCount', 'weeks', 'currentWeek', 'dowRu'));
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
            'mainFontSize' => $mainFontSize . 'px',
            'timestamp' => $immutable = CarbonImmutable::now()->format('d.m.Y H:i:s')
        ];

        //return $data;

        PDF::setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif']);

        do {
            $pdf = PDF::loadView('pdf.facultyDow', $data)->setPaper('a4', 'landscape');
            $pdf->stream('Расписание (' . $data["title"] .').pdf');
            $pageCount = $pdf->getDomPDF()->get_canvas()->get_page_count();
            $mainFontSize -= 0.5;
            $data["mainFontSize"] = $mainFontSize . "px";
        } while($pageCount > 1);

        ob_clean();

        $pdf = PDF::loadView('pdf.facultyDow', $data)->setPaper('a4', 'landscape');
        return $pdf->stream('Расписание (' . $data["title"] .').pdf');
    }

    public function facultyDowDownload(Request $request) {
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
            'mainFontSize' => $mainFontSize . 'px',
            'timestamp' => $immutable = CarbonImmutable::now()->format('d.m.Y H:i:s')
        ];

        //return $data;

        PDF::setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif']);

        do {
            $pdf = PDF::loadView('pdf.facultyDow', $data)->setPaper('a4', 'landscape');
            $pdf->stream('Расписание (' . $data["title"] .').pdf');
            $pageCount = $pdf->getDomPDF()->get_canvas()->get_page_count();
            $mainFontSize -= 0.5;
            $data["mainFontSize"] = $mainFontSize . "px";
        } while($pageCount > 1);

        ob_clean();

        $pdf = PDF::loadView('pdf.facultyDow', $data)->setPaper('a4', 'landscape');
        $pdf->save('Расписание (' . $data["title"] .').pdf');
        return $pdf->download('Расписание (' . $data["title"] .').pdf');
    }

    public function facultyDowBulkDownload(Request $request) {
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
        $week = $input["week"];
        $facultyName = Faculty::find($facultyId)->name;

        $fileNames = array();

        for($dow = 1; $dow <= 6; $dow++) {
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
                return $aVal < $bVal ? -1 : 1;
            });

            $mainFontSize = 12;

            $data = [
                'title' => $dowRu[$dow] . ' (' . $week . ')' . ' - ' . $facultyName,
                'schedule' => $schedule,
                'scheduleRings' => $scheduleRings,
                'dow' => $dow,
                'mainFontSize' => $mainFontSize . 'px',
                'timestamp' => $immutable = CarbonImmutable::now()->format('d.m.Y H:i:s')
            ];

            //return $data;

            PDF::setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif']);

            do {
                $pdf = PDF::loadView('pdf.facultyDow', $data)->setPaper('a4', 'landscape');
                $pdf->stream('Расписание (' . $data["title"] . ').pdf');
                $pageCount = $pdf->getDomPDF()->get_canvas()->get_page_count();
                $mainFontSize -= 0.5;
                $data["mainFontSize"] = $mainFontSize . "px";
            } while ($pageCount > 1);

            ob_clean();

            $pdf = PDF::loadView('pdf.facultyDow', $data)->setPaper('a4', 'landscape');
            $pdf->save('Расписание (' . $data["title"] . ').pdf');
            $fileNames[] = 'Расписание (' . $data["title"] . ').pdf';
        }

        $pdfM = new PDFMerger();
        foreach ($fileNames as $fileName) {
            $pdfM->addPDF($fileName, 'all');
        }

        $filename = "Schedule - " . $facultyId . " (" . $week . ").pdf";

        $pdfM->merge('download', $filename);

        return redirect()->back();
    }


}
