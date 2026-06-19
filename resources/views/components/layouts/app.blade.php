<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PETRONAS HSExPSM Month 2026</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <main class="app-shell">
        <section class="topbar">
            <div>
                <p class="eyebrow">PETRONAS HSExPSM Month 2026</p>
                <div class="title-row">
                    <h1>HSExPSM Games</h1>
                    <a href="{{ route('leaderboard', ['entry' => 1]) }}" class="primary-button compact-action">Manual Entry</a>
                </div>
            </div>
            <div class="top-actions">
                <a href="{{ route('leaderboard') }}" class="ghost-button">Leaderboard</a>
                <a href="{{ route('quiz.host') }}" class="ghost-button">QuickFire</a>
                <a href="{{ route('hazard.start') }}" class="ghost-button">Spot the Error</a>
                <a href="{{ route('scoring.guide') }}" class="ghost-button">Scoring Guide</a>
                <a href="{{ route('leaderboard') }}" class="ghost-button" onclick="event.preventDefault();window.location.reload()">Restart</a>
            </div>
        </section>

        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
