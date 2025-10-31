<?php

namespace Kulebyaka1337\GuessNumber\Controller;

use Kulebyaka1337\GuessNumber\View\View;
use Kulebyaka1337\GuessNumber\Game\Game;
use Kulebyaka1337\GuessNumber\Database\Database;

function startGame(): void
{
    // Полный путь к базе внутри папки guess-number
    $dbPath = __DIR__ . '/../data/games.sqlite';

    // Если папки data нет — создаём
    $dataDir = dirname($dbPath);
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0777, true);
    }

    // Создаём объект базы (RedBeanPHP)
    $db = new Database($dbPath);

    while (true) {
        View::renderStartScreen();
        $choice = View::promptMenuChoice();

        switch ($choice) {
            case 1:
                $game = new Game();
                $record = $game->playInteractive();
                $db->saveGame($record);
                View::renderGameReplay($record['attempts']);
                break;

            case 2:
                $games = $db->getAllGames();
                View::renderGamesList($games);
                break;

            case 3:
                $games = $db->getGamesByResult(true);
                View::renderGamesList($games);
                break;

            case 4:
                $games = $db->getGamesByResult(false);
                View::renderGamesList($games);
                break;

            case 5:
                $stats = $db->getPlayerStats();
                View::renderPlayerStats($stats);
                break;

            case 6:
                $games = $db->getAllGames();
                View::renderGamesList($games);

                $id = (int) View::prompt("Введите ID партии для повтора");
                $attempts = $db->getGameAttempts($id);
                if ($attempts) {
                    View::renderGameReplay($attempts);
                } else {
                    echo "Партия с ID $id не найдена.\n";
                }
                break;

            case 0:
                echo "Выход из игры. До встречи!\n";
                return;

            default:
                echo "Неверный выбор. Попробуйте снова.\n";
        }

        echo "\nНажмите Enter, чтобы продолжить...";
        fgets(STDIN);
    }
}
