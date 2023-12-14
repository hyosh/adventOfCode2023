<?php

$debug = array_key_exists(1, $argv) && $argv[1] === 'debug';

$file_content = file_get_contents(__DIR__ . '/input.txt');
$lines = explode(PHP_EOL, $file_content);

if ($debug) {
    $lines = [
        'Game 1: 3 blue, 4 red; 1 red, 2 green, 6 blue; 2 green',
        'Game 2: 1 blue, 2 green; 3 green, 4 blue, 1 red; 1 green, 1 blue',
        'Game 3: 8 green, 6 blue, 20 red; 5 blue, 4 red, 13 green; 5 green, 1 red',
        'Game 4: 1 green, 3 red, 6 blue; 3 green, 6 red; 3 green, 15 blue, 14 red',
        'Game 5: 6 red, 1 blue, 3 green; 2 blue, 1 red, 2 green'
    ];
}

function display(string $result): void
{
    echo $result . PHP_EOL;
}

################################
########### PART 1 #############
################################
function parseLine(string $line)
{
    $parts = explode(':', $line);
    $game = str_replace('Game ', '', $parts[0]);
    $parts = explode(';', $parts[1]);
    $sets = parseSets($parts);

    return [
        'game' => $game,
        'sets' => $sets,
    ];
}

function parseSets(array $sets)
{
    $parsed_sets = [];
    foreach ($sets as $set) {
        $parts = explode(',', $set);
        $result = [];
        foreach ($parts as $part) {
            $part = trim($part);
            $part = explode(' ', $part);
            $result[$part[1]] = $part[0];
        }
        $parsed_sets[] = $result;
    }

    return $parsed_sets;
}

function getMaxByColor(array $sets)
{
    $result = [
        'red' => 0,
        'blue' => 0,
        'green' => 0,
    ];
    foreach ($sets as $set) {
        foreach ($set as $color => $value) {
            $result[$color] = max($result[$color], $value);
        }
    }

    return $result;
}
function part1(array $lines)
{
    $possibles_games = [];
    foreach ($lines as $line) {
        if (empty($line)) continue;
        $parsed = parseLine($line);
        $max_by_color = getMaxByColor($parsed['sets']);
        if ($max_by_color['red'] <= 12 && $max_by_color['blue'] <= 14 && $max_by_color['green'] <= 13) {
            $possibles_games[] = $parsed['game'];
        }
    }

    return array_sum($possibles_games);
}


################################
########### PART 2 #############
################################

function part2(array $lines)
{
    $total = 0;
    foreach ($lines as $line) {
        if (empty($line)) continue;
        $parsed = parseLine($line);
        $min_by_color = getMaxByColor($parsed['sets']);
        $power_of_set = $min_by_color['red'] * $min_by_color['blue'] * $min_by_color['green'];
        $total += $power_of_set;

    }

    return $total;
}


display('Part 1: ' . part1($lines));
display('Part 2: ' . part2($lines));
