<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SkillArea;
use App\Models\Skill;
use App\Models\Indicator;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['code' => 'ENGINE', 'name' => 'Engine Mechanics'],
            ['code' => 'ELECTRICAL', 'name' => 'Electrical Systems'],
            ['code' => 'TRANSMISSION', 'name' => 'Transmission & Drivetrain'],
            ['code' => 'BRAKE', 'name' => 'Brake Systems'],
            ['code' => 'SUSPENSION', 'name' => 'Suspension & Steering'],
        ];

        foreach ($areas as $area) {
            $skillArea = SkillArea::create($area);

            if ($area['code'] === 'ENGINE') {
                $skill = Skill::create([
                    'code' => 'ENG001',
                    'name' => 'Engine Assembly & Disassembly',
                    'skill_area_id' => $skillArea->id,
                ]);
                $this->createEngineIndicators($skill);
            } elseif ($area['code'] === 'ELECTRICAL') {
                $skill = Skill::create([
                    'code' => 'ELEC001',
                    'name' => 'Battery & Charging System',
                    'skill_area_id' => $skillArea->id,
                ]);
                $this->createElectricalIndicators($skill);
            } elseif ($area['code'] === 'TRANSMISSION') {
                $skill = Skill::create([
                    'code' => 'TRANS001',
                    'name' => 'Manual Transmission Maintenance',
                    'skill_area_id' => $skillArea->id,
                ]);
                $this->createTransmissionIndicators($skill);
            } elseif ($area['code'] === 'BRAKE') {
                $skill = Skill::create([
                    'code' => 'BRAKE001',
                    'name' => 'Brake Pad & Rotor Replacement',
                    'skill_area_id' => $skillArea->id,
                ]);
                $this->createBrakeIndicators($skill);
            } elseif ($area['code'] === 'SUSPENSION') {
                $skill = Skill::create([
                    'code' => 'SUSP001',
                    'name' => 'Suspension Component Service',
                    'skill_area_id' => $skillArea->id,
                ]);
                $this->createSuspensionIndicators($skill);
            }
        }
    }

    private function createEngineIndicators(Skill $skill): void
    {
        $indicators = [
            ['code' => 'E001', 'description' => 'Safely remove engine cover and identify components', 'order' => 1],
            ['code' => 'E002', 'description' => 'Drain engine oil properly', 'order' => 2],
            ['code' => 'E003', 'description' => 'Replace oil filter correctly', 'order' => 3],
            ['code' => 'E004', 'description' => 'Install and torque cylinder head bolts', 'order' => 4],
        ];

        foreach ($indicators as $indicator) {
            $indicator['skill_id'] = $skill->id;
            Indicator::create($indicator);
        }
    }

    private function createElectricalIndicators(Skill $skill): void
    {
        $indicators = [
            ['code' => 'EL001', 'description' => 'Identify battery terminal polarity', 'order' => 1],
            ['code' => 'EL002', 'description' => 'Clean battery terminals safely', 'order' => 2],
            ['code' => 'EL003', 'description' => 'Test charging system voltage', 'order' => 3],
            ['code' => 'EL004', 'description' => 'Replace alternator correctly', 'order' => 4],
        ];

        foreach ($indicators as $indicator) {
            $indicator['skill_id'] = $skill->id;
            Indicator::create($indicator);
        }
    }

    private function createTransmissionIndicators(Skill $skill): void
    {
        $indicators = [
            ['code' => 'TR001', 'description' => 'Drain transmission fluid properly', 'order' => 1],
            ['code' => 'TR002', 'description' => 'Check transmission filter condition', 'order' => 2],
            ['code' => 'TR003', 'description' => 'Refill with correct fluid type', 'order' => 3],
            ['code' => 'TR004', 'description' => 'Test gear shifting smoothness', 'order' => 4],
        ];

        foreach ($indicators as $indicator) {
            $indicator['skill_id'] = $skill->id;
            Indicator::create($indicator);
        }
    }

    private function createBrakeIndicators(Skill $skill): void
    {
        $indicators = [
            ['code' => 'BR001', 'description' => 'Measure brake pad thickness', 'order' => 1],
            ['code' => 'BR002', 'description' => 'Safely remove wheel and caliper', 'order' => 2],
            ['code' => 'BR003', 'description' => 'Inspect rotor for damage', 'order' => 3],
            ['code' => 'BR004', 'description' => 'Install pads and test brake feel', 'order' => 4],
        ];

        foreach ($indicators as $indicator) {
            $indicator['skill_id'] = $skill->id;
            Indicator::create($indicator);
        }
    }

    private function createSuspensionIndicators(Skill $skill): void
    {
        $indicators = [
            ['code' => 'SU001', 'description' => 'Identify suspension components', 'order' => 1],
            ['code' => 'SU002', 'description' => 'Check shock absorber condition', 'order' => 2],
            ['code' => 'SU003', 'description' => 'Test steering response', 'order' => 3],
            ['code' => 'SU004', 'description' => 'Replace suspension bushings', 'order' => 4],
        ];

        foreach ($indicators as $indicator) {
            $indicator['skill_id'] = $skill->id;
            Indicator::create($indicator);
        }
    }
}
