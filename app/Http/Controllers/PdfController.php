<?php
namespace App\Http\Controllers;

ini_set('max_execution_time', 180);

use App\DomainClasses\Building;
use App\DomainClasses\Calendar;
use App\DomainClasses\ConfigOption;
use App\DomainClasses\Faculty;
use App\DomainClasses\StudentGroup;
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

    public function facultyBulkDownload(Request $request) {
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

    public function DowBulkDownload(Request $request) {
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

        $dow = $input["dow"];
        $week = $input["week"];

        $fileNames = array();

        $faculties = Faculty::all()->sortBy('sorting_order');

        foreach($faculties as $faculty) {
            $input["facultyId"] = $faculty->id;

            $schedule = $oac->GetFacultyWeeksSchedule($input);

            $facultyName = Faculty::find($faculty->id)->name;
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

        $filename = "Schedule (" . $week . ").pdf";

        $pdfM->merge('download', $filename);

        return redirect()->back();
    }

    public function DowBulkShow(Request $request) {
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

        $dow = $input["dow"];
        $week = $input["week"];

        $fileNames = array();

        $faculties = Faculty::all()->sortBy('sorting_order');

        foreach($faculties as $faculty) {
            $input["facultyId"] = $faculty->id;

            $schedule = $oac->GetFacultyWeeksSchedule($input);

            $facultyName = Faculty::find($faculty->id)->name;
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

        $filename = "Schedule (" . $week . ").pdf";

        $pdfM->merge('browser', $filename);

        return redirect()->back();
    }

    public function StudentGroupWeek(Request $request) {
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
        $input["compactResult"] = "1";

        $schedule = $oac->GetWeeksSchedule($input);

        $group = StudentGroup::find($input['groupId']);
        $week = $input["week"];

        $scheduleRings = array();

        for($dow = 1; $dow <= 7; $dow++) {
            foreach (array_keys($schedule[$dow]) as $time) {
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

        $mainFontSize = 10;

        $data = [
            'title' => "Расписание " . $group->name . " (" . $week . ")",
            'dowRu' => $dowRu,
            'groupSchedule' => $schedule,
            'groupName' => $group->name,
            'scheduleRings' => $scheduleRings,
            'mainFontSize' => $mainFontSize . 'px',
            'timestamp' => $immutable = CarbonImmutable::now()->format('d.m.Y H:i:s')
        ];

        //return $data;

        PDF::setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif']);

        do {
            $pdf = PDF::loadView('pdf.studentGroupWeek', $data)->setPaper('a4', 'landscape');
            $pdf->stream($data["title"] .'.pdf');
            $pageCount = $pdf->getDomPDF()->get_canvas()->get_page_count();
            $mainFontSize -= 0.5;
            $data["mainFontSize"] = $mainFontSize . "px";
        } while($pageCount > 1);

        ob_clean();

        $pdf = PDF::loadView('pdf.studentGroupWeek', $data)->setPaper('a4', 'landscape');
        return $pdf->stream($data["title"] .'.pdf');
    }

    public function StudentGroupWeekIndex() {
        $studentGroups = StudentGroup::allSorted();

        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();
        $today = CarbonImmutable::now()->format('Y-m-d');
        $currentWeek = Calendar::WeekFromDate($today, $css);

        $weekCount = Calendar::WeekCount();

        $weeks = array();
        for($w = 1; $w <= $weekCount; $w++) {
            $start = $css->clone();
            $end = $start->clone()->addDays(6);
            $weeks[$w] = $start->format("d.m") . " - " . $end->format('d.m');

            $css = $css->addWeek();
        }

        return view('pdf.studentGroupChoice', compact('studentGroups', 'weekCount', 'weeks', 'currentWeek'));
    }

    public function buildingEventsPdfChoice() {
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();

        $buildings = Building::all();
        $weekCount = Calendar::WeekCount();

        $weeks = array();
        for($w = 1; $w <= $weekCount; $w++) {
            $start = $css->clone();
            $end = $start->clone()->addDays(6);
            $weeks[$w] = $start->format("d.m") . " - " . $end->format('d.m');

            $css = $css->addWeek();
        }

        $today = CarbonImmutable::now()->format('Y-m-d');
        $currentDow = Calendar::CarbonDayOfWeek(CarbonImmutable::now());
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();
        $currentWeek = Calendar::WeekFromDate($today, $css);
        $dowRu = array("Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота");

        return view('pdf.buildingEventsPdfChoice', compact('buildings', 'dowRu', 'weekCount', 'weeks', 'currentWeek', 'currentDow'));
    }

    public function buildingEventsPdf(Request $request) {
        $input = $request->all();
        $input['weeks'] = $input['week'];
        $dowRu = array("Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота");

        $oac = new OldApiController();
        $events = $oac->GetBuildingEvents($input);

        PDF::setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif']);

        $mainFontSize = 12;
        $data["mainFontSize"] = $mainFontSize . "px";

        $building = Building::find($input['buildingId']);

        $rings = $events['rings'];
        usort($rings, function ($a, $b) {
            $aValue = intval(mb_substr($a,0,2)) * 60 + intval(mb_substr($a,3,2));
            $bValue = intval(mb_substr($b,0,2)) * 60 + intval(mb_substr($b,3,2));

            if ($aValue === $bValue) return 0;
            return ($aValue < $bValue) ? -1 : 1;
        });
        $ringsFlipped = array_flip($events['rings']);
        $newRings = array();
        foreach($rings as $ring) {
            $newRings[] = array(
                'id' => $ringsFlipped[$ring],
                'time' => $ring
            );
        }

        $auds = $events['auditoriums'];
        $auds = array_values($auds);
        sort($auds);
        $audsFlipped = array_flip($events['auditoriums']);
        $newAuds = array();
        foreach($auds as $aud) {
            $newAuds[] = array(
                'id' => $audsFlipped[$aud],
                'name' => $aud
            );
        }

        $data = [
            'title' => 'Занятость аудиторий корпуса (' . $building->name  .  ') ' .
                $dowRu[$input['dow']-1] . ' (' . $input['week'] . ')',
            'title1' => 'Занятость аудиторий корпуса' ,
            'title2' => '(' . $building->name  .  ')' ,
            'title3' => $dowRu[$input['dow']-1] . ' (' . $input['week'] . ')',
            'events' => $events['schedule'],
            'rings' => $newRings,
            'auditoriums' => $newAuds,
            'mainFontSize' => $mainFontSize . 'px',
            'timestamp' => $immutable = CarbonImmutable::now()->format('d.m.Y H:i:s')
        ];

        //return $data;

        PDF::setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif']);

        do {
            $pdf = PDF::loadView('pdf.buildingEvents', $data)->setPaper('a4', 'landscape');
            $pdf->stream($data["title"] .'.pdf');
            $pageCount = $pdf->getDomPDF()->get_canvas()->get_page_count();
            $mainFontSize -= 0.5;
            $data["mainFontSize"] = $mainFontSize . "px";
        } while($pageCount > 1);

        ob_clean();

        $pdf = PDF::loadView('pdf.buildingEvents', $data)->setPaper('a4', 'landscape');
        return $pdf->stream($data["title"] .'.pdf');
    }
}
