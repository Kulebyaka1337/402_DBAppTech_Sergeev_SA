<?php

namespace Kulebyaka1337\GuessNumber\Game;

class Game
{
    public function playInteractive(): array
    {
        $player = $this->promptNonEmpty('Введите имя игрока');

        $maxNumber = (int) $this->promptWithDefault('Введите максимальное число', '100');
        if ($maxNumber < 1) {
            $maxNumber = 100;
        }

        $maxAttempts = (int) $this->promptWithDefault('Введите максимальное количество попыток', '10');
        if ($maxAttempts < 1) {
            $maxAttempts = 10;
        }

        $secret = random_int(1, $maxNumber);
        $attempts = [];
        $won = false;

        for ($i = 1; $i <= $maxAttempts; $i++) {
            $input = $this->promptNonEmpty(sprintf(
                'Попытка %d/%d — введите число (1..%d)', 
                $i, 
                $maxAttempts, 
                $maxNumber
            ));
            
            if (!is_numeric($input)) {
                $this->writeln("Введите корректное число!");
                $i--;
                continue;
            }

            $guess = (int) $input;

            if ($guess < 1 || $guess > $maxNumber) {
                $this->writeln("Число должно быть от 1 до $maxNumber!");
                $i--;
                continue;
            }

            if ($guess === $secret) {
                $attempts[] = ['value' => $guess, 'reply' => 'угадал'];
                $this->writeln("Верно! Вы выиграли за $i попыток.");
                $won = true;
                break;
            }

            if ($guess < $secret) {
                $attempts[] = ['value' => $guess, 'reply' => 'меньше'];
                $this->writeln("Загаданное число больше.");
            } else {
                $attempts[] = ['value' => $guess, 'reply' => 'больше'];
                $this->writeln("Загаданное число меньше.");
            }
        }

        if (!$won) {
            $this->writeln("Попытки закончились! Загаданное число: $secret");
        }

        return [
            'date'       => date('Y-m-d H:i:s'),
            'player'     => $player,
            'max_number' => $maxNumber,
            'secret'     => $secret,
            'won'        => $won,
            'attempts'   => $attempts,
        ];
    }

    private function promptNonEmpty(string $message): string
    {
        $value = '';
        while (trim($value) === '') {
            $this->writeln($message . ': ');
            $value = trim(fgets(STDIN));
            if ($value === '') {
                $this->writeln('Поле не может быть пустым.');
            }
        }
        return $value;
    }

    private function promptWithDefault(string $message, string $default): string
    {
        $this->writeln("$message [$default]: ");
        $line = trim(fgets(STDIN));
        return $line === '' ? $default : $line;
    }

    private function writeln(string $text): void
    {
        echo $text . PHP_EOL;
    }
}
