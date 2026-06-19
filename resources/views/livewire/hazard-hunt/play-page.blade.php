<div>
    @if($error)
        <section class="start-view">
            <div class="intro-panel">
                <p class="mode-label">Error</p>
                <h2>Unable to Start</h2>
                <p>{{ $error }}</p>
                <div class="result-actions" style="margin-top:24px">
                    <a href="{{ route('hazard.start') }}" class="primary-button">Back to Start</a>
                </div>
            </div>
        </section>
    @elseif(!$finished && $currentQuestion)
        <section class="game-view">
            <div class="status-row hazard-quiz-status">
                <div class="status-card">
                    <span>Scorecard</span>
                    <strong>{{ $scorecardId }}</strong>
                </div>
                <div class="status-card">
                    <span>Scene</span>
                    <strong>{{ $currentIndex + 1 }} / {{ $questionCount }}</strong>
                </div>
                <div class="status-card">
                    <span>Correct</span>
                    <strong>{{ $score }}</strong>
                </div>
                <div class="scene-title">
                    <span>Scene {{ $currentIndex + 1 }}</span>
                    <strong>{{ $currentQuestion['title'] }}</strong>
                </div>
            </div>

            <div class="hazard-quiz-layout">
                <div class="image-stage">
                    <img class="scene-photo" src="{{ $currentQuestion['image'] ?? '' }}" alt="{{ $currentQuestion['title'] }}" draggable="false" onerror="this.style.display='none'">
                </div>

                <aside class="found-panel quiz-panel-card">
                    <p class="mode-label">{{ $currentQuestion['source'] }}</p>
                    <h2>{{ $currentQuestion['question'] }}</h2>

                    <div class="hazard-options">
                        @foreach($currentQuestion['options'] as $optIndex => $option)
                            @php
                                $answered = isset($answers[$currentIndex]);
                                $isCorrect = $answered && $answers[$currentIndex]['isCorrect'];
                                $selected = $answered && $answers[$currentIndex]['selectedIndex'] === $optIndex;
                            @endphp
                            <button
                                type="button"
                                class="hazard-option @if($answered && $optIndex === $currentQuestion['answer']) correct @endif @if($selected && !$isCorrect) wrong @endif"
                                @if($answered) disabled @endif
                                wire:click="answerQuestion({{ $optIndex }})"
                            >{{ $option }}</button>
                        @endforeach
                    </div>

                    @if(isset($answers[$currentIndex]))
                        <div class="hazard-feedback {{ $answers[$currentIndex]['isCorrect'] ? 'correct' : 'wrong' }}">
                            <strong>{{ $answers[$currentIndex]['isCorrect'] ? 'Correct.' : 'Not correct.' }}</strong>
                            <p><b>Right answer:</b> {{ $currentQuestion['options'][$currentQuestion['answer']] }}</p>
                            <p>{{ $currentQuestion['explanation'] }}</p>
                        </div>
                        <button wire:click="nextQuestion" class="primary-button" type="button">
                            {{ $currentIndex >= $questionCount - 1 ? 'Finish' : 'Continue' }}
                        </button>
                    @endif
                </aside>
            </div>
        </section>
    @elseif($finished && $result)
        <section class="result-view">
            <div class="result-summary">
                <p class="mode-label">Finished</p>
                <h2>{{ $score >= 4 ? 'Process Safety Champion' : ($score >= 3 ? 'Strong Process Safety Awareness' : 'Challenge Complete') }}</h2>
                <div class="result-score">
                    <span>{{ $result['score'] }}</span>
                    <small>/ 100 points</small>
                </div>
                <p>Scorecard ID: {{ $scorecardId }} · Correct answers: {{ $score }} / {{ $questionCount }} · Ride time: {{ $elapsedSeconds }} sec · {{ $result['breakdown']['quizPoints'] ?? 0 }} quiz pts + {{ $result['breakdown']['timeBonus'] ?? 0 }} time bonus.</p>
                <div class="result-actions">
                    <button wire:click="playAgain" class="primary-button" type="button">Back to Introduction</button>
                    <button wire:click="retryChallenge" class="ghost-button" type="button">Retry Challenge</button>
                </div>
            </div>

            <div class="learning-panel">
                <h2>Answer Review</h2>
                <div class="learning-list">
                    @foreach($questions as $qIndex => $question)
                        @php $answer = $answers[$qIndex] ?? null; @endphp
                        <article class="learning-item {{ $answer && $answer['isCorrect'] ? 'found' : 'missed' }}">
                            <h3>Scene {{ $qIndex + 1 }}: {{ $question['title'] }}</h3>
                            <p><b>Your answer:</b> {{ $answer ? $question['options'][$answer['selectedIndex']] : 'No answer recorded' }}</p>
                            <p><b>Correct answer:</b> {{ $question['options'][$question['answer']] }}</p>
                            <p>{{ $question['explanation'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
