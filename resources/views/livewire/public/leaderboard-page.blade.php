<div wire:poll.10s="pollRefresh">
    <section class="leaderboard-view @if($showEntryForm) score-entry-mode @endif">
        <div class="leaderboard-header">
            <div>
                <p class="mode-label">PETRONAS HSExPSM Month 2026</p>
                <h2>Live Leaderboard</h2>
            </div>
            <div class="leaderboard-actions">
                <a href="{{ route('scoring.guide') }}" class="ghost-button">Scoring Guide</a>
                <button wire:click="loadLeaderboard" class="ghost-button" type="button">Refresh</button>
            </div>
        </div>

        <div class="game-hub">
            <article class="game-card featured-game">
                <span>Game 1</span>
                <strong>Hazard Hunt Ride</strong>
                <small>Review process safety scenarios and choose the best answer.</small>
                <a href="{{ route('hazard.start') }}" class="primary-button">Open Spot the Error</a>
            </article>
            <article class="game-card">
                <span>Game 2</span>
                <strong>Reaction Risk</strong>
                <small>Sticks caught out of 10 are converted into leaderboard points.</small>
            </article>
            <article class="game-card">
                <span>Game 3</span>
                <strong>QuickFire Quiz</strong>
                <small>Correct answers from the 10-question round are converted into leaderboard points.</small>
                <a href="{{ route('quiz.host') }}" class="primary-button">Open QuickFire</a>
            </article>
            <article class="game-card">
                <span>Game 4</span>
                <strong>Pipe Fit Challenge</strong>
                <small>Correct completion time is converted into leaderboard points.</small>
            </article>
        </div>

        <div class="leaderboard-grid">
            @if($showEntryForm)
                <form wire:submit="save" class="scorecard-panel">
                    <div class="scorecard-heading">
                        <div>
                            <h3>Manual Scorecard Entry</h3>
                            <p>Enter the actual scorecard marks and times. The app calculates leaderboard points before saving.</p>
                        </div>
                        <span class="status-pill">Manual</span>
                    </div>

                    <div style="margin:0 0 16px;padding:16px;border:1px solid var(--line);border-radius:8px;background:#f4fbfa">
                        <p style="font-weight:800;margin:0 0 8px">Scan a scorecard photo to auto-fill</p>
                        <input type="file" wire:model.live="ocrImage" accept="image/*" id="ocr-file-input" style="display:none">
                        <button type="button" class="primary-button compact-action" onclick="document.getElementById('ocr-file-input').click()">
                            Upload from Gallery or Camera
                        </button>
                        <span wire:loading wire:target="ocrImage" style="margin-left:8px;color:var(--muted);font-weight:700">Uploading...</span>
                        <span wire:loading wire:target="runOcr" style="margin-left:8px;color:var(--muted);font-weight:700">Processing with AI...</span>
                        @if($ocrStatus)
                            <p style="margin-top:8px;color:var(--petronas-dark);font-weight:800;font-size:14px">{{ $ocrStatus }}</p>
                        @endif
                    </div>

                    <div class="score-fields">
                        <label>
                            Scorecard No / Employee ID
                            <input wire:model="scorecardId" type="text" autocomplete="off" required placeholder="Example: 101 or employee ID">
                            @error('scorecardId') <p class="form-error">{{ $message }}</p> @enderror
                        </label>
                        <label>
                            Name
                            <input wire:model="name" type="text" autocomplete="off" required placeholder="Participant name">
                            @error('name') <p class="form-error">{{ $message }}</p> @enderror
                        </label>
                        <label>
                            Phone
                            <input wire:model="phone" type="tel" autocomplete="off" placeholder="Optional">
                        </label>
                        <label>
                            Email
                            <input wire:model="email" type="email" autocomplete="off" required placeholder="Required for winner contact">
                            @error('email') <p class="form-error">{{ $message }}</p> @enderror
                        </label>
                    </div>

                    <div class="scorecard-entry-table">
                        <div class="scorecard-entry-head">
                            <span>Activity</span>
                            <span>Mark 1</span>
                            <span>Time 1</span>
                            <span>Mark 2</span>
                            <span>Time 2</span>
                            <span>Best</span>
                        </div>

                        <div class="scorecard-entry-row">
                            <strong>Hazard Hunt Ride</strong>
                            <input wire:model.live="hazardMark" type="text" inputmode="numeric" placeholder="3/5">
                            <input wire:model.live="hazardTime" type="text" inputmode="numeric" placeholder="163s">
                            <input wire:model.live="hazardMark2" type="text" inputmode="numeric" placeholder="5/5">
                            <input wire:model.live="hazardTime2" type="text" inputmode="numeric" placeholder="90s">
                            <output>{{ $previewScores[0] ?? '-' }}</output>
                        </div>

                        <div class="scorecard-entry-row">
                            <strong>Reaction Risk</strong>
                            <input wire:model.live="reactionMark" type="text" inputmode="numeric" placeholder="8/10">
                            <span class="not-applicable">-</span>
                            <input wire:model.live="reactionMark2" type="text" inputmode="numeric" placeholder="9/10">
                            <span class="not-applicable">-</span>
                            <output>{{ $previewScores[1] ?? '-' }}</output>
                        </div>

                        <div class="scorecard-entry-row">
                            <strong>QuickFire Quiz</strong>
                            <input wire:model.live="quickfireMark" type="text" inputmode="numeric" placeholder="9/10">
                            <span class="not-applicable">-</span>
                            <input wire:model.live="quickfireMark2" type="text" inputmode="numeric" placeholder="10/10">
                            <span class="not-applicable">-</span>
                            <output>{{ $previewScores[2] ?? '-' }}</output>
                        </div>

                        <div class="scorecard-entry-row">
                            <strong>Pipe Fit Challenge</strong>
                            <span class="not-applicable">-</span>
                            <input wire:model.live="pipeTime" type="text" inputmode="numeric" placeholder="75s">
                            <span class="not-applicable">-</span>
                            <input wire:model.live="pipeTime2" type="text" inputmode="numeric" placeholder="60s">
                            <output>{{ $previewScores[3] ?? '-' }}</output>
                        </div>
                    </div>

                    <div class="score-fields staff-fields">
                        <label>
                            Staff PIN
                            <input wire:model="pin" type="password" autocomplete="off" placeholder="Optional">
                        </label>
                    </div>

                    @if($saveNotice)
                        <div class="score-save-notice {{ $saveNoticeTone }}" role="status" aria-live="polite">{{ $saveNotice }}</div>
                    @endif

                    <p class="score-helper">Enter the actual mark and time from the physical scorecard. The app calculates leaderboard points automatically before saving.</p>

                    <div class="save-row">
                        <strong>Total: <span>{{ $totalPreview }}</span></strong>
                        <button class="primary-button" type="submit">Save Score</button>
                    </div>
                </form>
                <p style="margin-top:12px;text-align:center"><a href="{{ route('leaderboard') }}" class="ghost-button" style="display:inline-block">Back to Leaderboard</a></p>
            @endif

            <div class="leaderboard-panel">
                <div class="leaderboard-meta">
                    <span>{{ $status }}</span>
                    @if($lastUpdated)
                        <span>Updated {{ $lastUpdated }}</span>
                    @endif
                </div>
                <div class="table-wrap">
                    <table class="leaderboard-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>ID</th>
                                <th>Participant</th>
                                <th>Total</th>
                                <th>Hazard Ride</th>
                                <th>Reaction</th>
                                <th>QuickFire</th>
                                <th>Pipe Fit</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entries as $index => $entry)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $entry['scorecardId'] }}</td>
                                    <td><strong>{{ $entry['name'] }}</strong></td>
                                    <td><strong>{{ $entry['total'] }}</strong></td>
                                    @foreach($entry['scores'] as $i => $score)
                                        <td>
                                            @if($score !== null)
                                                {{ $score }}
                                                @if(($entry['attempts'][$i] ?? 0) > 1)
                                                    <small>{{ $entry['attempts'][$i] }} tries</small>
                                                @endif
                                            @else
                                                <span class="incomplete-score">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td>{{ $entry['completedGames'] }}/4</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="empty-cell">No scores yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    @if($showCelebration)
        <div class="leader-celebration" wire:click="dismissCelebration" role="alert">
            <div class="fireworks" aria-hidden="true">
                <span></span><span></span><span></span>
                <span></span><span></span><span></span>
            </div>
            <div class="leader-celebration-card">
                <span>New Leader</span>
                <strong>{{ $celebrationName }}</strong>
                <small>{{ $celebrationScore }}</small>
            </div>
        </div>
    @endif
</div>
