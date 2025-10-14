<?php

namespace Kulebyaka1337\GuessNumber\Database;

use PDO;
use PDOException;

class Database
{
    private PDO $pdo;
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function init(): void
    {
        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        try {
            $this->pdo = new PDO('sqlite:' . $this->path);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS games (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    date TEXT NOT NULL,
                    player TEXT NOT NULL,
                    max_number INTEGER NOT NULL,
                    secret INTEGER NOT NULL,
                    won INTEGER NOT NULL
                );
            ");

            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS attempts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    game_id INTEGER NOT NULL,
                    attempt_number INTEGER NOT NULL,
                    value INTEGER NOT NULL,
                    reply TEXT NOT NULL,
                    FOREIGN KEY (game_id) REFERENCES games(id)
                );
            ");
        } catch (PDOException $e) {
            echo "Ошибка подключения к базе данных: " . $e->getMessage() . PHP_EOL;
            exit(1);
        }
    }

    public function saveGame(array $record): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO games (date, player, max_number, secret, won)
            VALUES (:date, :player, :max_number, :secret, :won)
        ");

        $stmt->execute([
            ':date' => $record['date'],
            ':player' => $record['player'],
            ':max_number' => $record['max_number'],
            ':secret' => $record['secret'],
            ':won' => $record['won'] ? 1 : 0,
        ]);

        $gameId = (int) $this->pdo->lastInsertId();

        $stmtAttempt = $this->pdo->prepare("
            INSERT INTO attempts (game_id, attempt_number, value, reply)
            VALUES (:game_id, :attempt_number, :value, :reply)
        ");

        foreach ($record['attempts'] as $i => $attempt) {
            $stmtAttempt->execute([
                ':game_id' => $gameId,
                ':attempt_number' => $i + 1,
                ':value' => $attempt['value'],
                ':reply' => $attempt['reply'],
            ]);
        }
    }

    public function getAllGames(): array
    {
        $stmt = $this->pdo->query("
            SELECT g.*, 
                   (SELECT COUNT(*) FROM attempts a WHERE a.game_id = g.id) as attempts_count 
            FROM games g 
            ORDER BY id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGamesByResult(bool $won): array
    {
        $stmt = $this->pdo->prepare("
            SELECT g.*, 
                   (SELECT COUNT(*) FROM attempts a WHERE a.game_id = g.id) as attempts_count 
            FROM games g 
            WHERE won = :won 
            ORDER BY id DESC
        ");
        $stmt->execute([':won' => $won ? 1 : 0]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPlayerStats(): array
    {
        $sql = "
            SELECT 
                player,
                COUNT(*) AS total_games,
                SUM(CASE WHEN won = 1 THEN 1 ELSE 0 END) AS wins,
                SUM(CASE WHEN won = 0 THEN 1 ELSE 0 END) AS losses,
                ROUND(
                    SUM(CASE WHEN won = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 
                    1
                ) AS win_rate
            FROM games
            GROUP BY player
            ORDER BY win_rate DESC
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGameAttempts(int $gameId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT attempt_number, value, reply
            FROM attempts
            WHERE game_id = :id
            ORDER BY attempt_number ASC
        ");
        $stmt->execute([':id' => $gameId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
