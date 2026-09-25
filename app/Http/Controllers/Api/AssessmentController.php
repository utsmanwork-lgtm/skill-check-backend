<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Student;
use App\Models\Skill;
use App\Models\AssessmentIndicator;
use App\Models\AssessmentHistory;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'skill_id' => 'required|exists:skills,id',
            'indicators' => 'required|array',
            'indicators.*.indicator_id' => 'required|exists:indicators,id',
            'indicators.*.checked' => 'required|boolean',
            'indicators.*.notes' => 'nullable|string',
        ]);

        $user = $request->user();
        $student = Student::findOrFail($validated['student_id']);

        if ($student->guru_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $assessment = Assessment::firstOrCreate(
            [
                'student_id' => $validated['student_id'],
                'skill_id' => $validated['skill_id'],
            ],
            [
                'guru_id' => $user->id,
                'status' => 'in_progress',
            ]
        );

        foreach ($validated['indicators'] as $indicator) {
            AssessmentIndicator::updateOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'indicator_id' => $indicator['indicator_id'],
                ],
                [
                    'checked' => $indicator['checked'],
                    'notes' => $indicator['notes'] ?? null,
                ]
            );
        }

        if ($assessment->isComplete()) {
            $assessment->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        AssessmentHistory::create([
            'assessment_id' => $assessment->id,
            'guru_id' => $user->id,
            'action' => 'updated',
            'payload' => $validated['indicators'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assessment submitted',
            'data' => $assessment->load('indicators'),
        ], 201);
    }

    public function show($id, Request $request)
    {
        $assessment = Assessment::findOrFail($id);
        $user = $request->user();

        if ($assessment->guru_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Assessment retrieved',
            'data' => $assessment->load(['student', 'skill', 'indicators.indicator']),
        ], 200);
    }

    public function reset($id, Request $request)
    {
        $assessment = Assessment::findOrFail($id);
        $user = $request->user();

        if ($assessment->guru_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $assessment->indicators()->update(['checked' => false, 'notes' => null]);
        $assessment->update([
            'status' => 'pending',
            'completed_at' => null,
        ]);

        AssessmentHistory::create([
            'assessment_id' => $assessment->id,
            'guru_id' => $user->id,
            'action' => 'reset',
            'payload' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assessment reset for remediation',
            'data' => $assessment,
        ], 200);
    }
}
