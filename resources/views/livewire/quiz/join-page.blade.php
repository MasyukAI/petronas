<div>
    <section class="quiz-view">
        <div class="quiz-shell">
            <section class="quiz-hero">
                <div>
                    <p class="mode-label">QuickFire Quiz</p>
                    <h2>QuickFire Safety Challenge</h2>
                    <p>Join with your Scorecard ID or Employee ID and answer 10 HSE & PSM questions.</p>
                </div>
            </section>

            @if(!$playerToken)
                <section class="quiz-panel">
                    <div>
                        <p class="mode-label">Join Round</p>
                        <h3>Enter your Scorecard ID and name</h3>
                    </div>
                    <form wire:submit="joinRound" class="quiz-join-form">
                        <input wire:model="code" type="text" placeholder="Join Code" required>
                        <input wire:model="scorecardId" type="text" placeholder="Scorecard ID" required>
                        <input wire:model="name" type="text" placeholder="Participant name" required>
                        <button class="primary-button" type="submit">Join Quiz</button>
                    </form>
                    @error('code') <p class="form-error">{{ $message }}</p> @enderror
                    @error('scorecardId') <p class="form-error">{{ $message }}</p> @enderror
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    <p class="quiz-muted">{{ $statusMessage }}</p>
                </section>
            @else
                <section class="quiz-panel">
                    <div class="quiz-panel-heading">
                        <div>
                            <p class="mode-label">Joined</p>
                            <h3>Waiting for host to start</h3>
                        </div>
                    </div>
                    <p class="quiz-muted">{{ $statusMessage }}</p>
                </section>
            @endif

            @if($round && $players->count())
                <section class="quiz-panel">
                    <h3>Players in this round</h3>
                    <div class="quiz-players">
                        @foreach($players as $player)
                            <article class="quiz-player-card">
                                <span>{{ $player->scorecard_id }}</span>
                                <strong>{{ $player->name }}</strong>
                                <small>{{ $player->ready ? 'Ready' : 'Joined' }}</small>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </section>
</div>
