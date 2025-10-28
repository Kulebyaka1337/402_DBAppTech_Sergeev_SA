<?php

namespace Kulebyaka1337\GuessNumber\Database;

use RedBeanPHP\R as R;

class Database
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->init();
    }

    public function init(): void
    {
        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Подключение RedBeanPHP
        R::setup('sqlite:' . $this->path);

        // Включаем freeze=false, чтобы RedBean могла создавать таблицы
        R::freeze(false);
    }

    public function saveGame(array $record): void
    {
        $game = R::dispense('game');
        $game->date = $record['date'];
        $game->player = $record['player'];
        $game->max_number = $record['max_number'];
        $game->secret = $record['secret'];
        $game->won = $record['won'] ? 1 : 0;
        $gameId = R::store($game);

        foreach ($record['attempts'] as $i => $attempt) {
            $att = R::dispense('attempt');
            $att->game_id = $gameId;
            $att->attempt_number = $i + 1;
            $att->value = $attempt['value'];
            $att->reply = $attempt['reply'];
            R::store($att);
        }
    }

    public function getAllGames(): array
    {
        $games = R::findAll('game', 'ORDER BY id DESC');
        $result = [];
        foreach ($games as $g) {
            $attemptsCount = R::count('attempt', 'game_id = ?', [$g->id]);
            $result[] = [
                'id' => $g->id,
                'date' => $g->date,
                'player' => $g->player,
                'max_number' => $g->max_number,
                'secret' => $g->secret,
                'won' => (bool)$g->won,
                'attempts_count' => $attemptsCount,
            ];
        }
        return $result;
    }

    public function getGamesByResult(bool $won): array
    {
        $games = R::find('game', 'won = ? ORDER BY id DESC', [$won ? 1 : 0]);
        $result = [];
        foreach ($games as $g) {
            $attemptsCount = R::count('attempt', 'game_id = ?', [$g->id]);
            $result[] = [
                'id' => $g->id,
                'date' => $g->date,
                'player' => $g->player,
                'max_number' => $g->max_number,
                'secret' => $g->secret,
                'won' => (bool)$g->won,
                'attempts_count' => $attemptsCount,
            ];
        }
        return $result;
    }

    public function getPlayerStats(): array
    {
        $games = R::findAll('game');
        $stats = [];
        foreach ($games as $g) {
            if (!isset($stats[$g->player])) {
                $stats[$g->player] = ['player' => $g->player, 'total_games' => 0, 'wins' => 0, 'losses' => 0];
            }
            $stats[$g->player]['total_games']++;
            if ($g->won) {
                $stats[$g->player]['wins']++;
            } else {
                $stats[$g->player]['losses']++;
            }
        }
        // добавляем процент побед
        foreach ($stats as &$s) {
            $s['win_rate'] = $s['total_games'] > 0 ? round($s['wins'] * 100 / $s['total_games'], 1) : 0;
        }
        return array_values($stats);
    }

    public function getGameAttempts(int $gameId): array
    {
        $attempts = R::find('attempt', 'game_id = ? ORDER BY attempt_number ASC', [$gameId]);
        $result = [];
        foreach ($attempts as $a) {
            $result[] = [
                'attempt_number' => $a->attempt_number,
                'value' => $a->value,
                'reply' => $a->reply,
            ];
        }
        return $result;
    }
}
