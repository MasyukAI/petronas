<div>
    <section class="start-view">
        <div class="intro-panel">
            <p class="mode-label">Hazard Hunt Ride</p>
            <h2>Hazard Hunt: Spot the Error</h2>
            <p>
                Enter your Scorecard ID to start. You will get 5 random
                process-safety questions from the hazard bank.
            </p>

            @if($showCountdown)
                <div class="countdown-active" x-data="{ count: 3, started: false }" x-init="
                    $wire.on('countdown-start', () => {
                        if (started) return;
                        started = true;
                        const interval = setInterval(() => {
                            count--;
                            if (count <= 0) {
                                clearInterval(interval);
                                $wire.startGame();
                            }
                        }, 1000);
                    });
                ">
                    <p class="mode-label">Get ready</p>
                    <h2>Hazard Hunt starts in</h2>
                    <strong x-text="count" class="countdown-number">3</strong>
                    <p>Scorecard ID confirmed.</p>
                </div>
            @else
                <form wire:submit="confirmAndStart" class="hazard-start-form">
                    <label>
                        Scorecard ID
                        <input wire:model="scorecardId" type="text" autocomplete="off" required placeholder="Example: HSE-0142">
                        @error('scorecardId') <p class="form-error">{{ $message }}</p> @enderror
                    </label>
                    <label>
                        Name
                        <input wire:model="name" type="text" autocomplete="off" required placeholder="Your full name">
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </label>
                    <button class="primary-button" type="submit">Confirm & Start</button>
                </form>
            @endif
        </div>
    </section>
</div>
