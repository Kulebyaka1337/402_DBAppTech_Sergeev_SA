<?php

namespace Kulebyaka1337\GuessNumber\View;

class View 
{
    public static function renderStartScreen(): void 
    {
        self::line("=== Угадай число (GuessNumber) ===");
        self::line("Выберите действие:");
        self::line("1) Новая игра");
        self::line("2) Список всех игр");
        self::line("3) Победные игры");
        self::line("4) Проигранные игры");
        self::line("5) Статистика по игрокам");
        self::line("6) Повтор партии");
        self::line("0) Выход");
        self::line("");
    }

    public static function promptMenuChoice(): int 
    {
        $choice = self::prompt("Введите номер действия", "1");
        return (int)$choice;
    }

    public static function renderGamesList(array $games): void
    {
        if (empty($games)) {
            self::line("Нет сохранённых игр.");
            return;
        }

        self::line("--- Список игр ---");
        foreach ($games as $game) {
            $result = $game['won'] ? 'Победа' : 'Проигрыш';
            self::line(sprintf(
                "[%s] Игрок: %s | Макс: %d | Загадано: %d | Результат: %s | Попыток: %d",
                $game['date'],
                $game['player'],
                $game['max_number'],
                $game['secret'],
                $result,
                $game['attempts_count']
            ));
        }
    }

    public static function renderPlayerStats(array $stats): void
    {
        if (empty($stats)) {
            self::line("Нет данных о статистике.");
            return;
        }

        self::line("--- Статистика по игрокам ---");
        foreach ($stats as $player => $data) {
            self::line(sprintf(
                "Игрок: %s | Игр: %d | Побед: %d | Поражений: %d | Процент побед: %.1f%%",
                $data['player'],
                $data['total_games'],
                $data['wins'],
                $data['losses'],
                $data['win_rate']
            ));
        }
    }

    public static function renderGameReplay(array $attempts): void
    {
        self::line("--- Повтор партии ---");
        foreach ($attempts as $index => $attempt) {
            self::line(sprintf(
                "Попытка %d: %d (%s)",
                $index + 1,
                $attempt['value'],
                $attempt['reply']
            ));
        }
    }

    private static function line(string $text): void
    {
        if (function_exists('\cli\line')) {
            \cli\line($text);
        } else {
            echo $text . PHP_EOL;
        }
    }

    public static function prompt(string $message, string $default = ''): string
    {
        if (function_exists('\cli\prompt')) {
            return \cli\prompt($message, $default);
        }

        echo $message;
        if ($default !== '') {
            echo " [$default]";
        }
        echo ": ";
        $line = trim(fgets(STDIN));
        return $line === '' ? $default : $line;
    }
}
