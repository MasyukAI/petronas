<?php

declare(strict_types=1);

namespace App\Services;

class ScoringService
{
    const HAZARD_RULES = [
        'maxCorrect' => 5,
        'correctnessPoints' => 80,
        'timerPoints' => 20,
        'bestTargetSeconds' => 60,
        'cutoffSeconds' => 180,
    ];

    const REACTION_RULES = [
        'maxSticks' => 10,
    ];

    const QUICKFIRE_RULES = [
        'maxCorrect' => 10,
    ];

    const PIPEFIT_RULES = [
        'bestTargetSeconds' => 45,
        'cutoffSeconds' => 180,
        'minimumCorrectCompletionScore' => 10,
    ];

    /** @return array{score: int, rawResult: string, breakdown: array{correct: int, maxCorrect: int, timeSeconds: ?int, quizPoints: float, timeBonus: float}} */
    public function scoreHazard(int $correct, ?int $seconds): array
    {
        $correct = max(0, min(self::HAZARD_RULES['maxCorrect'], $correct));
        $accuracyFactor = $correct / self::HAZARD_RULES['maxCorrect'];
        $quizPoints = $accuracyFactor * self::HAZARD_RULES['correctnessPoints'];
        $timeBonus = $this->timerScore(
            $seconds,
            self::HAZARD_RULES['bestTargetSeconds'],
            self::HAZARD_RULES['cutoffSeconds'],
            self::HAZARD_RULES['timerPoints']
        ) * $accuracyFactor;

        $score = max(0, min(100, (int) round($quizPoints + $timeBonus)));

        return [
            'score' => $score,
            'rawResult' => "{$correct}/".self::HAZARD_RULES['maxCorrect'].($seconds === null ? '' : ' + '.round($seconds).' sec'),
            'breakdown' => [
                'correct' => $correct,
                'maxCorrect' => self::HAZARD_RULES['maxCorrect'],
                'timeSeconds' => $seconds,
                'quizPoints' => round($quizPoints, 1),
                'timeBonus' => round($timeBonus, 1),
            ],
        ];
    }

    public function scoreReactionRisk(int $caught, int $total = 10): int
    {
        return (int) round(($caught / max(1, $total)) * 100);
    }

    public function scoreQuickfire(int $correct, int $total = 10): int
    {
        return (int) round(($correct / max(1, $total)) * 100);
    }

    public function scorePipeFit(bool $completed, ?int $seconds): int
    {
        if (! $completed) {
            return 0;
        }
        if ($seconds === null) {
            return 100;
        }

        return max(
            self::PIPEFIT_RULES['minimumCorrectCompletionScore'],
            (int) round($this->timerScore(
                $seconds,
                self::PIPEFIT_RULES['bestTargetSeconds'],
                self::PIPEFIT_RULES['cutoffSeconds'],
                100
            ))
        );
    }

    private function timerScore(?int $seconds, int $bestTarget, int $cutoff, int $maxPoints): float
    {
        if ($seconds === null) {
            return 0;
        }
        if ($seconds <= $bestTarget) {
            return $maxPoints;
        }
        if ($seconds >= $cutoff) {
            return 0;
        }

        return (($cutoff - $seconds) / ($cutoff - $bestTarget)) * $maxPoints;
    }
}
