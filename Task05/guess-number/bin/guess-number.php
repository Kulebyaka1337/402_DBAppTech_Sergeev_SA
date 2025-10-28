#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Game.php';
require_once __DIR__ . '/../src/View.php';
require_once __DIR__ . '/../src/Controller.php';

\Kulebyaka1337\GuessNumber\Controller\startGame();
