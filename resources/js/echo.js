import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const ROUND_ID_ATTR = 'data-quiz-round-id';

function getRoundId() {
    const el = document.querySelector(`[${ROUND_ID_ATTR}]`);
    return el ? el.dataset.quizRoundId : null;
}

function initEcho() {
    if (window.Echo) return window.Echo;

    window.Pusher = Pusher;

    Pusher.logToConsole = false;

    const echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    echo.connector.pusher.connection.bind('error', (err) => {
        console.warn('Reverb unavailable, quiz updates will not be real-time.');
    });

    window.Echo = echo;
    return echo;
}

function listenToRound(roundId, echo) {
    if (!roundId || !echo) return;

    const channel = echo.channel(`quiz-round.${roundId}`);

    const trigger = () => window.Livewire.dispatch('quiz-poll');

    channel.listen('.quiz.round.started', trigger);
    channel.listen('.quiz.player.joined', trigger);
    channel.listen('.quiz.player.answered', trigger);
    channel.listen('.quiz.phase.changed', trigger);
    channel.listen('.quiz.player.next-ready', trigger);
    channel.listen('.quiz.round.reset', trigger);
}

const roundId = getRoundId();

if (roundId) {
    const echo = initEcho();
    listenToRound(roundId, echo);
}

document.addEventListener('livewire:init', () => {
    const id = getRoundId();
    if (id && id !== roundId) {
        const echo = initEcho();
        listenToRound(id, echo);
    }
});
