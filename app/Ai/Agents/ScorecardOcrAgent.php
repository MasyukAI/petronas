<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class ScorecardOcrAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You are a scorecard OCR assistant. Extract the handwritten or printed values from a physical PETRONAS HSExPSM event scorecard photograph.

Extract exactly what you see — do not guess or round. Return values in their original format:
- Marks: as fractions like "3/5", "8/10", "9/10"
- Times: as seconds like "163s", "90s", "75s"
- Names and IDs: exactly as written on the card

If a field is blank or unreadable on the card, omit it (do not include the key).';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'scorecardId' => $schema->string(),
            'name' => $schema->string(),
            'phone' => $schema->string(),
            'email' => $schema->string(),
            'hazardMark' => $schema->string(),
            'hazardTime' => $schema->string(),
            'hazardMark2' => $schema->string(),
            'hazardTime2' => $schema->string(),
            'reactionMark' => $schema->string(),
            'reactionMark2' => $schema->string(),
            'quickfireMark' => $schema->string(),
            'quickfireMark2' => $schema->string(),
            'pipeTime' => $schema->string(),
            'pipeTime2' => $schema->string(),
        ];
    }
}
