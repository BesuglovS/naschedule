<?php
namespace App\Http\Controllers;

ini_set('max_execution_time', 180);

use App\DomainClasses\Building;
use App\DomainClasses\Calendar;
use App\DomainClasses\ConfigOption;
use App\DomainClasses\Faculty;
use App\DomainClasses\StudentGroup;
use App\DomainClasses\Teacher;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $input["internal"] = 1;
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
        $input["internal"] = 1;
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
        $download = $input["download"];
        $input["weeks"] = $input["week"];
        $input["internal"] = 1;
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
            $filename = 'fbd ' . $facultyId . " " . $dow . '.pdf';
            $pdf->save($filename);
            $fileNames[] = $filename;
        }

        $pdfM = new PDFMerger();
        foreach ($fileNames as $fileName) {
            $pdfM->addPDF($fileName, 'all');
        }

        $faculty = Faculty::find($facultyId);

        $filename = "Расписание (" . $faculty->name . " - " . $week . ").pdf";

        if ($download == "true") {
            $pdfM->merge('download', $filename);
        } else {
            $pdfM->merge('browser', $filename);
        }

        foreach ($fileNames as $fn) {
            unlink($fn);
        }

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
        $input["internal"] = 1;

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
            $filename = 'dbd ' . $dow . " " . $week . " " . $faculty->id . '.pdf';
            $pdf->save($filename);
            $fileNames[] = $filename;
        }

        $pdfM = new PDFMerger();
        foreach ($fileNames as $fileName) {
            $pdfM->addPDF($fileName, 'all');
        }

        $filename = "Расписание (" . $dowRu[$dow] . " - " . $week . ").pdf";

        $pdfM->merge('download', $filename);

        foreach ($fileNames as $fn) {
            unlink($fn);
        }

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
        $input["internal"] = 1;

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
            $filename = 'dbs ' . $dow . " " . $week . " " . $faculty->id . '.pdf';
            $pdf->save($filename);
            $fileNames[] = $filename;
        }

        $pdfM = new PDFMerger();
        foreach ($fileNames as $fileName) {
            $pdfM->addPDF($fileName, 'all');
        }

        $filename = "Расписание (" . $dowRu[$dow] . " - " . $week . ").pdf";

        $pdfM->merge('browser', $filename);

        foreach ($fileNames as $fn) {
            unlink($fn);
        }

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

        $disciplineTypes = array(
            1 => isset($input['bud']),
            2 => isset($input['vne']),
            3 => isset($input['pla'])
        );

        $typeString = "";
        if (isset($input['bud'])) {
            $typeString .= "Бюджет";
        }
        if (isset($input['vne'])) {
            $typeString .= (($typeString === "") ? "" : " + ") . "Внеурочные";
        }
        if (isset($input['pla'])) {
            $typeString .= (($typeString === "") ? "" : " + ") . "Платные";
        }

        $input['disciplineTypes'] = $disciplineTypes;

        $input["weeks"] = $input["week"];
        $input["compactResult"] = "1";
        $input["internal"] = 1;

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
            'title' => "Расписание " . $group->name . " (" . $week . ") - " . $typeString,
            'dowRu' => $dowRu,
            'groupSchedule' => $schedule,
            'groupName' => $group->name,
            'scheduleRings' => $scheduleRings,
            'mainFontSize' => $mainFontSize . 'px',
            'timestamp' => $immutable = CarbonImmutable::now()->format('d.m.Y H:i:s'),
            'signature' => isset($input['signature']),
            'exportTimestamp' => isset($input['exportTimestamp'])
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
        $faculties = Faculty::all()->sortBy('sorting_order');

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

        return view('pdf.studentGroupChoice', compact('studentGroups', 'faculties', 'weekCount', 'weeks', 'currentWeek'));
    }

    public function StudentGroupWeekThree(Request $request) {
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

        for($type = 1; $type <= 3; $type++) {

            $disciplineTypes = array(
                1 => $type === 1,
                2 => $type === 2,
                3 => $type === 3
            );

            $typeString = "";
            switch ($type) {
                case 1:
                    $typeString = "Бюджет";
                    break;
                case 2:
                    $typeString = "Внеурочные";
                    break;
                case 3:
                    $typeString = "Платные";
                    break;
            }

            $input['disciplineTypes'] = $disciplineTypes;

            $input["weeks"] = $input["week"];
            $input["compactResult"] = "1";
            $input["internal"] = 1;

            $schedule = $oac->GetWeeksSchedule($input);

            $group = StudentGroup::find($input['groupId']);
            $week = $input["week"];

            $scheduleRings = array();

            for ($dow = 1; $dow <= 7; $dow++) {
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
                return $aVal < $bVal ? -1 : 1;
            });

            $mainFontSize = 10;

            $data = [
                'title' => "Расписание " . $group->name . " (" . $week . ") - " . $typeString,
                'dowRu' => $dowRu,
                'groupSchedule' => $schedule,
                'groupName' => $group->name,
                'scheduleRings' => $scheduleRings,
                'mainFontSize' => $mainFontSize . 'px',
                'timestamp' => $immutable = CarbonImmutable::now()->format('d.m.Y H:i:s'),
                'signature' => isset($input['signature']),
                'exportTimestamp' => isset($input['exportTimestamp'])
            ];

            //return $data;

            PDF::setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif']);

            do {
                $pdf = PDF::loadView('pdf.studentGroupWeek', $data)->setPaper('a4', 'landscape');
                $pdf->stream($week . " " . $type . '.pdf');
                $pageCount = $pdf->getDomPDF()->get_canvas()->get_page_count();
                $mainFontSize -= 0.5;
                $data["mainFontSize"] = $mainFontSize . "px";
            } while ($pageCount > 1);

            ob_clean();

            $pdf = PDF::loadView('pdf.studentGroupWeek', $data)->setPaper('a4', 'landscape');
            $pdf->save($week . " " . $type . '.pdf');
            $fileNames[] = $week . " " . $type . '.pdf';
        }

        $pdfM = new PDFMerger();
        foreach ($fileNames as $fileName) {
            $pdfM->addPDF($fileName, 'all');
        }

        $group = StudentGroup::find($input['groupId']);

        $filename = "Расписание (" . $group->name . " - " . $input['week'] .").pdf";

        $pdfM->merge('download', $filename);

        foreach ($fileNames as $fn) {
            unlink($fn);
        }

        return redirect()->back();
    }

    public function FacultyWeekThree(Request $request) {
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
        $input["internal"] = 1;

        $faculty = Faculty::find($input['facultyId']);
        $facultyGroups = DB::table('faculty_student_group')
            ->join('student_groups', 'faculty_student_group.student_group_id', '=', 'student_groups.id')
            ->where('faculty_student_group.faculty_id', '=', $faculty->id)
            ->orderBy('student_groups.name')
            ->get();

        $fileNames = array();

        foreach($facultyGroups as $facultyGroup) {
            for ($type = 1; $type <= 3; $type++) {

                $disciplineTypes = array(
                    1 => $type === 1,
                    2 => $type === 2,
                    3 => $type === 3
                );

                $typeString = "";
                switch ($type) {
                    case 1:
                        $typeString = "Бюджет";
                        break;
                    case 2:
                        $typeString = "Внеурочные";
                        break;
                    case 3:
                        $typeString = "Платные";
                        break;
                }

                $input['disciplineTypes'] = $disciplineTypes;

                $input["groupId"] = $facultyGroup->id;
                $input["weeks"] = $input["week"];
                $input["compactResult"] = "1";

                $schedule = $oac->GetWeeksSchedule($input);

                $group = StudentGroup::find($input['groupId']);
                $week = $input["week"];

                $scheduleRings = array();

                for ($dow = 1; $dow <= 7; $dow++) {
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
                    return $aVal < $bVal ? -1 : 1;
                });

                $mainFontSize = 10;

                $data = [
                    'title' => "Расписание " . $group->name . " (" . $week . ") - " . $typeString,
                    'dowRu' => $dowRu,
                    'groupSchedule' => $schedule,
                    'groupName' => $group->name,
                    'scheduleRings' => $scheduleRings,
                    'mainFontSize' => $mainFontSize . 'px',
                    'timestamp' => CarbonImmutable::now()->format('d.m.Y H:i:s'),
                    'signature' => isset($input['signature']),
                    'exportTimestamp' => isset($input['exportTimestamp'])
                ];

                //return $data;

                PDF::setOptions([
                    'dpi' => 150,
                    'defaultFont' => 'sans-serif']);

                do {
                    $pdf = PDF::loadView('pdf.studentGroupWeek', $data)->setPaper('a4', 'landscape');
                    $pdf->stream($facultyGroup->id . ' ' . $input["week"] . " " . $type . '.pdf');
                    $pageCount = $pdf->getDomPDF()->get_canvas()->get_page_count();
                    $mainFontSize -= 0.5;
                    $data["mainFontSize"] = $mainFontSize . "px";
                } while ($pageCount > 1);

                ob_clean();

                $pdf = PDF::loadView('pdf.studentGroupWeek', $data)->setPaper('a4', 'landscape');
                $filename = $facultyGroup->id . ' ' . $input["week"] . " " . $type . '.pdf';
                $pdf->save($filename);
                $fileNames[] = $filename;
            }
        }

        $pdfM = new PDFMerger();
        foreach ($fileNames as $fileName) {
            $pdfM->addPDF($fileName, 'all');
        }

        $filename = "Расписание (" . $faculty->name . " - " . $input['week'] .").pdf";

        $pdfM->merge('download', $filename);

        foreach ($fileNames as $fn) {
            unlink($fn);
        }

        return redirect()->back();
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
        $input["internal"] = 1;
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

    public function teacherPdf() {
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();

        $teachers = Teacher::all()->sortBy('fio');
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

        return view('pdf.teacherPdfChoice', compact('teachers', 'weekCount', 'weeks', 'currentWeek', 'dowRu'));
    }

    public function teacherPdfWeek (Request $request) {
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
        $input["compactResult"] = true;
        $input["internal"] = 1;
        $teacherSchedule = $oac->GetTeacherWeeksSchedule($input);
        unset($teacherSchedule[7]);


        $scheduleRings = array();
        for($i = 1; $i <= 6; $i++) {
            foreach (array_keys($teacherSchedule[$i]) as $time) {
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

        //dd($teacherSchedule, $scheduleRings);

        $teacher = Teacher::find($input["teacherId"]);


        $mainFontSize = 12;

        $data = [
            'title' => $teacher->fio . ' - ' . $input["week"],
            'schedule' => $teacherSchedule,
            'scheduleRings' => $scheduleRings,
            'mainFontSize' => $mainFontSize . 'px',
            'dowRu' => $dowRu,
            'timestamp' => $immutable = CarbonImmutable::now()->format('d.m.Y H:i:s')
        ];

        //return $data;

        PDF::setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif']);

        do {
            $pdf = PDF::loadView('pdf.teacherPdf', $data)->setPaper('a4', 'landscape');
            $pdf->stream('Расписание (' . $data["title"] . ' (' . $data["timestamp"].')).pdf');
            $pageCount = $pdf->getDomPDF()->get_canvas()->get_page_count();
            $mainFontSize -= 0.5;
            $data["mainFontSize"] = $mainFontSize . "px";
        } while($pageCount > 1);

        ob_clean();

        $pdf = PDF::loadView('pdf.teacherPdf', $data)->setPaper('a4', 'landscape');
        return $pdf->stream('Расписание (' . $data["title"] . ' (' . $data["timestamp"] .')).pdf');
    }
}
