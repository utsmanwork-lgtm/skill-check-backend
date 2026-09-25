<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Assessment;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $students = Student::where('guru_id', $user->id)
            ->with('classRoom')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Students retrieved',
            'data' => $students,
        ], 200);
    }

    public function skills($id, Request $request)
    {
        $student = Student::findOrFail($id);
        $user = $request->user();

        if ($student->guru_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $skills = \App\Models\Skill::with(['indicators'])
            ->get()
            ->map(function ($skill) use ($student) {
                $assessment = Assessment::where('student_id', $student->id)
                    ->where('skill_id', $skill->id)
                    ->first();

                if ($assessment) {
                    $checkedCount = $assessment->indicators()
                        ->where('checked', true)
                        ->count();
                    $totalCount = $assessment->indicators()->count();
                    $status = $totalCount > 0 && $checkedCount === $totalCount ? 'completed' : 'pending';
                } else {
                    $checkedCount = 0;
                    $totalCount = $skill->indicators()->count();
                    $status = 'pending';
                }

                return [
                    'skill' => $skill,
                    'assessment_id' => $assessment?->id,
                    'status' => $status,
                    'progress' => $totalCount > 0 ? ($checkedCount / $totalCount) * 100 : 0,
                    'checked_count' => $checkedCount,
                    'total_count' => $totalCount,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Student skills retrieved',
            'data' => $skills,
        ], 200);
    }
}
