<?php

use App\Http\Livewire\HazardHunt\HazardPlayPage;
use App\Http\Livewire\HazardHunt\HazardStartPage;
use App\Http\Livewire\Public\LeaderboardPage;
use App\Http\Livewire\Public\ScoringGuidePage;
use App\Http\Livewire\Quiz\QuizHostPage;
use App\Http\Livewire\Quiz\QuizJoinPage;
use App\Http\Livewire\Quiz\QuizPlayerPage;
use Illuminate\Support\Facades\Route;

Route::get('/', LeaderboardPage::class)->name('leaderboard');
Route::get('/leaderboard', LeaderboardPage::class)->name('leaderboard');
Route::get('/scoring', ScoringGuidePage::class)->name('scoring.guide');
Route::get('/score-entry', function () {
    return redirect()->route('leaderboard', ['entry' => 1]);
})->name('score.entry');

Route::get('/spot-the-error', HazardStartPage::class)->name('hazard.start');
Route::get('/spot-the-error/play', HazardPlayPage::class)->name('hazard.play');

Route::get('/quickfire', QuizHostPage::class)->name('quiz.host');
Route::get('/quickfire/join/{code?}', QuizJoinPage::class)->name('quiz.join');
Route::get('/quickfire/play/{round?}', QuizPlayerPage::class)->name('quiz.play');

Route::redirect('/kahoot', '/quickfire');
Route::redirect('/quiz', '/quickfire');
Route::redirect('/host', '/quickfire');
