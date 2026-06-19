<div data-quiz-round-id="{{ $round?->id }}">
    <section class="quiz-view" wire:poll.10s="pollTick">
        <div class="quiz-shell">
            @if(!$round || !$player)
                <section class="quiz-panel">
                    <h3>No active round</h3>
                    <p class="quiz-muted">Join a round using the join code from the host.</p>
                    <a href="{{ route('quiz.join') }}" class="primary-button" style="margin-top:12px;display:inline-block">Join a Round</a>
                </section>
            @elseif(in_array($phase['name'], ['lobby', 'ready', 'countdown']))
                <section class="quiz-panel">
                    <div class="quiz-panel-heading">
                        <div>
                            <p class="mode-label">Lobby</p>
                            <h3>Waiting for host to start</h3>
                        </div>
                    </div>
                    <p class="quiz-muted">You have joined the round. The host will start the quiz shortly.</p>
                    @if(!$player->ready && $phase['name'] === 'lobby')
                        <button wire:click="markReady" class="primary-button" type="button" style="margin-top:12px">I'm Ready</button>
                    @elseif($player->ready)
                        <p style="color:var(--good);font-weight:800;margin-top:12px">You are ready. Waiting for host...</p>
                    @endif
                </section>
            @elseif($phase['name'] === 'result')
                <section class="quiz-panel quiz-result-panel">
                    <div class="quiz-panel-heading">
                        <div>
                            <p class="mode-label">Round Complete</p>
                            <h3>QuickFire Results</h3>
                        </div>
                    </div>
                    <div class="quiz-results">
                        @foreach($players->sortByDesc('score') as $pIndex => $p)
                            <article class="quiz-result-card">
                                <span>Rank {{ $pIndex + 1 }}</span>
                                <strong>{{ $p->name }}</strong>
                                <div>{{ $p->score }}</div>
                                <small>{{ $p->correct_count }} / {{ $round->question_count }} correct</small>
                            </article>
                        @endforeach
                    </div>
                    <p style="margin-top:16px;text-align:center"><a href="{{ route('leaderboard') }}" class="ghost-button" style="display:inline-block">Leave Game</a></p>
                </section>
            @elseif($currentQuestion)
                <section class="quiz-play-panel">
                    <div class="quiz-status-grid">
                        <div class="status-card">
                            <span>Question</span>
                            <strong>{{ ($round->current_question ?? 0) + 1 }} / {{ $round->question_count }}</strong>
                        </div>
                        <div class="status-card timer-card">
                            <span>{{ $phase['name'] === 'review' ? 'Review' : 'Answer' }}</span>
                            <strong>{{ $phase['name'] === 'review' ? 'Done' : 'Open' }}</strong>
                        </div>
                        <div class="status-card">
                            <span>Your Score</span>
                            <strong>{{ $player->score }}</strong>
                        </div>
                    </div>

                    <article class="quiz-question-card">
                        <div class="quiz-category-row">
                            <span>{{ $currentQuestion['category'] ?? 'PSO' }}</span>
                            <span>{{ $phase['name'] === 'question' ? ($ownAnswer ? 'Answer locked. Waiting for all active players.' : 'Choose your answer. The question stays open until all active players answer.') : ($phase['name'] === 'review' ? 'Review the correct answer. Move on when everyone is ready.' : '') }}</span>
                        </div>
                        <h3>{{ $currentQuestion['question'] }}</h3>

                        @if($phase['name'] === 'question')
                            <div class="quiz-options">
                                @foreach($currentQuestion['options'] as $optIndex => $option)
                                    @php
                                        $answered = $ownAnswer !== null;
                                        $isCorrect = $answered && $ownAnswer['answerIndex'] === $currentQuestion['answerIndex'];
                                        $selected = $answered && $ownAnswer['answerIndex'] === $optIndex;
                                    @endphp
                                    <button
                                        type="button"
                                        class="quiz-option @if($answered && $optIndex === $currentQuestion['answerIndex']) correct @endif @if($selected && !$isCorrect) wrong @endif @if($selected) selected @endif"
                                        @if($answered) disabled @endif
                                        wire:click="submitAnswer({{ $round->current_question }}, {{ $optIndex }})"
                                    >
                                        <span>{{ chr(65 + $optIndex) }}</span>
                                        <strong>{{ $option }}</strong>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @if($phase['name'] === 'review')
                            <div class="quiz-review">
                                <span>Correct Answer</span>
                                <strong>{{ $currentQuestion['correctAnswer'] ?? $currentQuestion['options'][$currentQuestion['answerIndex']] ?? '' }}</strong>
                                @php
                                    $correctPlayerNames = $players->filter(fn($p) => ($p->answers[(string)$round->current_question]['correct'] ?? false))->pluck('name')->join(', ');
                                @endphp
                                <small>@if($correctPlayerNames) {{ $correctPlayerNames }} got it correct. @else No player answered correctly. @endif Press Next Question when ready.</small>
                            </div>
                            @if($player && $player->next_ready)
                                <p style="color:var(--muted);font-weight:800;margin-top:12px;text-align:center">Waiting for other players...</p>
                            @else
                                <button wire:click="markNextReady" class="primary-button quiz-next-button" type="button">{{ ($round->current_question ?? 0) >= $round->question_count - 1 ? 'Show Results' : 'Next Question' }}</button>
                            @endif
                        @endif
                    </article>
                </section>
                <p style="margin-top:16px;text-align:center"><a href="{{ route('leaderboard') }}" class="ghost-button" style="display:inline-block">Leave Game</a></p>
            @endif
        </div>
    </section>
</div>
