<?php

namespace Database\Seeders;

use App\Models\Participant;
use App\Models\ScoreAttempt;
use App\Services\ScoringService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            HazardQuestionSeeder::class,
        ]);

        $scoring = app(ScoringService::class);

        $demoParticipants = [
            ['scorecard_id' => 'HSE-0101', 'name' => 'Aina Rahman', 'email' => 'aina.rahman@petronas.com', 'phone' => '+60-12-345-6789'],
            ['scorecard_id' => 'HSE-0102', 'name' => 'Azlan Rahman', 'email' => 'azlan.rahman@petronas.com', 'phone' => '+60-12-345-6790'],
            ['scorecard_id' => 'HSE-0103', 'name' => 'Farid Iskandar', 'email' => 'farid.iskandar@petronas.com', 'phone' => '+60-12-345-6791'],
            ['scorecard_id' => 'HSE-0104', 'name' => 'Nur Aisyah', 'email' => 'nur.aisyah@petronas.com', 'phone' => '+60-12-345-6792'],
            ['scorecard_id' => 'HSE-0105', 'name' => 'Mei Lin Tan', 'email' => 'meilin.tan@petronas.com', 'phone' => '+60-12-345-6793'],
        ];

        foreach ($demoParticipants as $data) {
            Participant::create($data);
        }

        $demoScores = [
            ['scorecard_id' => 'HSE-0101', 'game_code' => 'hazard_hunt_ride', 'correct' => 4, 'seconds' => 48],
            ['scorecard_id' => 'HSE-0101', 'game_code' => 'reaction_risk', 'correct' => 8],
            ['scorecard_id' => 'HSE-0101', 'game_code' => 'quickfire_quiz', 'correct' => 9],
            ['scorecard_id' => 'HSE-0101', 'game_code' => 'pipe_fit_challenge', 'completed' => true, 'seconds' => 18],

            ['scorecard_id' => 'HSE-0102', 'game_code' => 'hazard_hunt_ride', 'correct' => 5, 'seconds' => 72],
            ['scorecard_id' => 'HSE-0102', 'game_code' => 'reaction_risk', 'correct' => 7],
            ['scorecard_id' => 'HSE-0102', 'game_code' => 'quickfire_quiz', 'correct' => 7],
            ['scorecard_id' => 'HSE-0102', 'game_code' => 'pipe_fit_challenge', 'completed' => true, 'seconds' => 34],

            ['scorecard_id' => 'HSE-0103', 'game_code' => 'hazard_hunt_ride', 'correct' => 4, 'seconds' => 90],
            ['scorecard_id' => 'HSE-0103', 'game_code' => 'reaction_risk', 'correct' => 9],
            ['scorecard_id' => 'HSE-0103', 'game_code' => 'quickfire_quiz', 'correct' => 8],
            ['scorecard_id' => 'HSE-0103', 'game_code' => 'pipe_fit_challenge', 'completed' => true, 'seconds' => 50],

            ['scorecard_id' => 'HSE-0104', 'game_code' => 'hazard_hunt_ride', 'correct' => 3, 'seconds' => 25],
            ['scorecard_id' => 'HSE-0104', 'game_code' => 'reaction_risk', 'correct' => 7],
            ['scorecard_id' => 'HSE-0104', 'game_code' => 'quickfire_quiz', 'correct' => 8],
            ['scorecard_id' => 'HSE-0104', 'game_code' => 'pipe_fit_challenge', 'completed' => true, 'seconds' => 38],

            ['scorecard_id' => 'HSE-0105', 'game_code' => 'hazard_hunt_ride', 'correct' => 4, 'seconds' => 132],
            ['scorecard_id' => 'HSE-0105', 'game_code' => 'reaction_risk', 'correct' => 7],
            ['scorecard_id' => 'HSE-0105', 'game_code' => 'quickfire_quiz', 'correct' => 7],
            ['scorecard_id' => 'HSE-0105', 'game_code' => 'pipe_fit_challenge', 'completed' => true, 'seconds' => 32],
        ];

        foreach ($demoScores as $score) {
            $participant = Participant::where('scorecard_id', $score['scorecard_id'])->first();
            if (! $participant) {
                continue;
            }

            $gameCode = $score['game_code'];

            if ($gameCode === 'hazard_hunt_ride') {
                $result = $scoring->scoreHazard($score['correct'], $score['seconds'] ?? null);
            } elseif ($gameCode === 'reaction_risk') {
                $calculated = $scoring->scoreReactionRisk($score['correct']);
                $result = ['score' => $calculated, 'rawResult' => "{$score['correct']}/10 sticks", 'breakdown' => []];
            } elseif ($gameCode === 'quickfire_quiz') {
                $calculated = $scoring->scoreQuickfire($score['correct']);
                $result = ['score' => $calculated, 'rawResult' => "{$score['correct']}/10", 'breakdown' => []];
            } elseif ($gameCode === 'pipe_fit_challenge') {
                $calculated = $scoring->scorePipeFit($score['completed'], $score['seconds'] ?? null);
                $result = ['score' => $calculated, 'rawResult' => 'pass + '.($score['seconds'] ?? '0').' sec', 'breakdown' => []];
            } else {
                continue;
            }

            ScoreAttempt::create([
                'participant_id' => $participant->id,
                'game_code' => $gameCode,
                'raw_result' => $result['rawResult'],
                'calculated_score' => $result['score'],
                'source' => 'demo',
                'status' => 'approved',
                'breakdown' => $result['breakdown'],
            ]);
        }
    }
}
