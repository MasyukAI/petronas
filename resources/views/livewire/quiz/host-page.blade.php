<div data-quiz-round-id="{{ $round?->id }}">
    <section class="quiz-view">
        <div class="quiz-shell">
            <section class="quiz-hero">
                <div>
                    <p class="mode-label">QuickFire Quiz</p>
                    <h2>QuickFire Safety Challenge</h2>
                    <p id="quizLead">Scan the QR, join with 1 to 3 players, and answer 10 process safety questions.</p>
                </div>
                @if($round)
                    @php $joinLink = url('/quickfire/join/' . $round->code); @endphp
                    <div class="quiz-code-panel">
                        <span>QR Link</span>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&margin=10&data={{ urlencode($joinLink) }}" alt="Quiz QR code">
                        <strong>{{ $joinLink }}</strong>
                    </div>
                @endif
            </section>

            @if($round)
            <section class="quiz-panel">
                    <div class="quiz-panel-heading">
                        <div>
                            <p class="mode-label">Waiting Lobby</p>
                            <h3>
                                @if($round->phase_name === 'countdown')
                                    Starting in a moment...
                                @elseif($playerCount === 0)
                                    Waiting for players
                                @else
                                    {{ $playerCount }} player(s) joined
                                @endif
                            </h3>
                        </div>
                        @if($round->phase_name === 'lobby' && $playerCount > 0)
                            <button wire:click="startRound" class="primary-button" type="button">Start Quiz</button>
                        @endif
                        <button wire:click="resetRound" class="ghost-button" type="button">Reset</button>
                    </div>

                    <div class="quiz-players">
                        @foreach($players as $player)
                            @php
                                $isActive = is_null($player->last_heartbeat) || $player->last_heartbeat->gt(now()->subSeconds(10));
                            @endphp
                            <article class="quiz-player-card @if(!$isActive) empty @endif">
                                <span>{{ $player->scorecard_id }}</span>
                                <strong>{{ $player->name }}</strong>
                                <small>
                                    @if(!$isActive)
                                        Disconnected
                                    @elseif($round->phase_name === 'review')
                                        {{ $player->next_ready ? 'Ready for next' : 'Reviewing' }}
                                    @elseif($player->ready)
                                        Ready
                                    @else
                                        Waiting
                                    @endif
                                </small>
                            </article>
                        @endforeach
                        @for($i = $playerCount; $i < 3; $i++)
                            <article class="quiz-player-card empty">
                                <span>Player {{ $i + 1 }}</span>
                                <strong>Waiting...</strong>
                                <small>Scan QR to join</small>
                            </article>
                        @endfor
                    </div>
                </section>

                @if(in_array($round->phase_name, ['question', 'review']))
                    <section class="quiz-play-panel">
                        <div class="quiz-question-card">
                            <div class="quiz-category-row">
                                <span>{{ $questions[$round->current_question]['category'] ?? 'PSO' }}</span>
                                <span id="quizPhaseHint">{{ $round->phase_name === 'review' ? 'Review the correct answer. Press Next Question when all players have reviewed.' : 'Waiting for all active players to answer...' }}</span>
                            </div>
                            <h3>{{ $questions[$round->current_question]['question'] ?? '' }}</h3>

                            @if($round->phase_name === 'review')
                                <div class="quiz-review">
                                    <span>Correct Answer</span>
                                    <strong>{{ $questions[$round->current_question]['correctAnswer'] ?? '' }}</strong>
                                </div>
                                <button wire:click="advanceToNext" class="primary-button quiz-next-button" type="button">{{ ($round->current_question ?? 0) >= $round->question_count - 1 ? 'Show Results' : 'Next Question' }}</button>
                            @endif
                        </div>
                    </section>
                @endif

                @if($round->phase_name === 'result')
                    <section class="quiz-panel quiz-result-panel">
                        <div class="quiz-panel-heading">
                            <div>
                                <p class="mode-label">Round Complete</p>
                                <h3>QuickFire Results</h3>
                            </div>
                            <button wire:click="resetRound" class="primary-button" type="button">Start Next Group</button>
                        </div>
                        <div class="quiz-results">
                            @foreach($players->sortByDesc('score') as $pIndex => $player)
                                <article class="quiz-result-card">
                                    <span>Rank {{ $pIndex + 1 }}</span>
                                    <strong>{{ $player->name }}</strong>
                                    <div>{{ $player->score }}</div>
                                    <small>{{ $player->correct_count }} / {{ $round->question_count }} correct. Score saved to QuickFire leaderboard column.</small>
                                </article>
                            @endforeach
                        </div>
                        @if($statusMessage)
                            <p style="color:var(--good);font-weight:800;margin-top:12px">{{ $statusMessage }}</p>
                        @endif
                    </section>
                @endif
            @endif
        </div>
    </section>
</div>
