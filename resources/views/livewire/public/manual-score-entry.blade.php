<div>
    <form wire:submit="save" class="scorecard-panel">
        <div class="scorecard-heading">
            <div>
                <h3>Manual Scorecard Entry</h3>
                <p>Enter the actual scorecard marks and times. The app calculates leaderboard points before saving.</p>
            </div>
            <span class="status-pill">Manual</span>
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

        @if($saveNotice)
            <div class="score-save-notice {{ $saveNoticeTone }}" role="status" aria-live="polite">{{ $saveNotice }}</div>
        @elseif($statusMessage)
            <p style="color:var(--good);font-weight:800;margin:12px 0 0">{{ $statusMessage }}</p>
        @endif

        <p class="score-helper">Enter the actual mark and time from the physical scorecard. The app calculates leaderboard points automatically before saving.</p>

        <div class="save-row">
            <strong>Total: <span>{{ $totalPreview }}</span></strong>
            <button class="primary-button" type="submit">Save Score</button>
        </div>
    </form>

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
