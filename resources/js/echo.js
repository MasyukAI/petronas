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
        wsPort: import.meta.env.VITE_REVERB_WS_PORT ?? 8080,
        wssPort: import.meta.env.VITE_REVERB_WSS_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    echo.connector.pusher.connection.bind('error', () => {
        console.warn('Reverb unavailable, quiz updates will not be real-time.');
    });

    window.Echo = echo;
    return echo;
}

function listenToRound(roundId, echo) {
    if (!roundId || !echo) return;

    const channel = echo.channel(`quiz-round.${roundId}`);

    const trigger = () => {
        if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
            window.Livewire.dispatch('quiz-poll');
        }
    };

    channel.listen('.quiz.round.started', trigger);
    channel.listen('.quiz.player.joined', trigger);
    channel.listen('.quiz.player.answered', trigger);
    channel.listen('.quiz.phase.changed', trigger);
    channel.listen('.quiz.player.next-ready', trigger);
    channel.listen('.quiz.round.reset', trigger);

    return channel;
}

function subscribeToRound(channel, roundId) {
    if (window.__echoChannel) {
        window.Echo.leave(`quiz-round.${window.__echoChannel}`);
    }
    window.__echoChannel = roundId;
    const echo = initEcho();
    listenToRound(roundId, echo);
}

const roundId = getRoundId();

if (roundId) {
    subscribeToRound(null, roundId);
}

document.addEventListener('livewire:init', () => {
    const id = getRoundId();
    if (id && id !== (window.__echoChannel || roundId)) {
        subscribeToRound(window.__echoChannel, id);
    }
});

document.addEventListener('livewire:navigated', () => {
    const id = getRoundId();
    if (id && id !== (window.__echoChannel || roundId)) {
        subscribeToRound(window.__echoChannel, id);
    }
});
