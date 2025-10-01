<?php
namespace Kulebyaka1337\GuessNumber\Controller;

use Kulebyaka1337\GuessNumber\View\View;
use Kulebyaka1337\GuessNumber\Game\Game;

function startGame() {
    View::renderStartScreen();

    // пример вызова логики
    $game = new Game();
    $record = $game->playInteractive();

    // выводим сводку
    echo "\n--- Итоги игры ---\n";
    echo "Игрок: {$record['player']}\n";
    echo "Макс. число: {$record['max_number']}\n";
    echo "Загаданное число: {$record['secret']}\n";
    echo "Результат: " . ($record['won'] ? 'ПОБЕДА' : 'ПРОИГРЫШ') . "\n";
    echo "Количество попыток: " . count($record['attempts']) . "\n";
}
