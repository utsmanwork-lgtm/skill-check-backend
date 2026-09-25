<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Skill;
use App\Models\Assessment;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function heatmap(Request $request)
    {
        $user = $request->user();
        
        $students = Student::where('guru_id', $user->id)->get();
        $skills = Skill::with('indicators')->get();

        $matrix = [];

        foreach ($students as $student) {
            $studentRow = [
                'student_id' => $student->id,
                'nis' => $student->nis,
                'name' => $student->name,
                'skills' => [],
            ];

            foreach ($skills as $skill) {
                $assessment = Assessment::where('student_id', $student->id)
                    ->where('skill_id', $skill->id)
                    ->first();

                if ($assessment) {
                    $checkedCount = $assessment->indicators()
                        ->where('checked', true)
                        ->count();
                    $totalCount = $assessment->indicators()->count();
                    $score = $totalCount > 0 ? round(($checkedCount / $totalCount) * 100) : 0;
                    $status = $score === 100 ? 'LULUS' : 'BELUM_LULUS';
                } else {
                    $score = 0;
                    $status = 'BELUM_LULUS';
                }

                $studentRow['skills'][] = [
                    'skill_id' => $skill->id,
                    'code' => $skill->code,
                    'name' => $skill->name,
                    'score' => $score,
                    'status' => $status,
                ];
            }

            $matrix[] = $studentRow;
        }

        return response()->json([
            'success' => true,
            'message' => 'Heatmap matrix retrieved',
            'data' => [
                'skills' => $skills->map(fn($s) => ['id' => $s->id, 'code' => $s->code, 'name' => $s->name]),
                'matrix' => $matrix,
            ],
        ], 200);
    }

    public function gapAnalysis(Request $request)
    {
        $user = $request->user();
        
        $skills = Skill::with('indicators')->get();
        $totalStudents = Student::where('guru_id', $user->id)->count();

        $analysis = $skills->map(function ($skill) use ($user, $totalStudents) {
            $assessments = Assessment::where('skill_id', $skill->id)
                ->whereHas('student', function ($query) use ($user) {
                    $query->where('guru_id', $user->id);
                })
                ->get();

            $passedCount = 0;
            $failedCount = 0;
            $notStartedCount = $totalStudents - $assessments->count();

            foreach ($assessments as $assessment) {
                if ($assessment->isComplete()) {
                    $passedCount++;
                } else {
                    $failedCount++;
                }
            }

            $passRate = $totalStudents > 0 ? round(($passedCount / $totalStudents) * 100, 2) : 0;

            return [
                'skill_id' => $skill->id,
                'code' => $skill->code,
                'name' => $skill->name,
                'total_students' => $totalStudents,
                'passed_count' => $passedCount,
                'failed_count' => $failedCount,
                'not_started_count' => $notStartedCount,
                'pass_rate' => $passRate,
                'gap' => 100 - $passRate,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Gap analysis retrieved',
            'data' => $analysis,
        ], 200);
    }
}
