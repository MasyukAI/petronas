<div>
    <section class="scoring-view">
        <div class="scoring-header">
            <div>
                <p class="mode-label">Scoring Guide</p>
                <h2>How Scores Are Calculated</h2>
            </div>
            <a href="{{ route('leaderboard') }}" class="ghost-button">Back to Leaderboard</a>
        </div>

        <div class="formula-strip">
            <strong>Total Score = Hazard Hunt Ride + Reaction Risk + QuickFire Quiz + Pipe Fit Challenge</strong>
            <span>Each game is worth 100 points. Maximum total is 400 points.</span>
        </div>

        <div class="scoring-grid">
            <article class="scoring-card">
                <span>Game 1</span>
                <h3>Hazard Hunt Ride</h3>
                <p>5 questions plus ride time. Correct answers are worth 80 points. Ride time can add up to 20 bonus points, adjusted by accuracy.</p>
                <code>correct / 5 x 80 + time bonus</code>
            </article>
            <article class="scoring-card">
                <span>Game 2</span>
                <h3>Reaction Risk</h3>
                <p>Participants catch up to 10 sticks. The score is converted directly into 100 points.</p>
                <code>sticks caught / 10 x 100</code>
            </article>
            <article class="scoring-card">
                <span>Game 3</span>
                <h3>QuickFire Quiz</h3>
                <p>10 questions from the existing question bank. The server calculates the final score from correct answers.</p>
                <code>correct / 10 x 100</code>
            </article>
            <article class="scoring-card">
                <span>Game 4</span>
                <h3>Pipe Fit Challenge</h3>
                <p>The pipe fit must be completed correctly. Faster correct completion earns a higher score. Incorrect or incomplete attempts score 0.</p>
                <code>correct completion time to 100 points</code>
            </article>
        </div>

        <div class="fairness-panel">
            <h3>Fairness Rules</h3>
            <ul>
                <li>Scorecard ID is used to combine scores across different games and different days.</li>
                <li>Participants may retry a game when allowed by event staff.</li>
                <li>The leaderboard uses the best approved attempt for each game.</li>
                <li>A worse retry will not reduce a participant's leaderboard score.</li>
                <li>Staff should enter the scorecard values exactly as written before saving.</li>
            </ul>
        </div>
    </section>
</div>
