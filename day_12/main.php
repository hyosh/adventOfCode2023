<?php

$debug = array_key_exists(1, $argv) && $argv[1] === 'debug';

$file_content = file_get_contents(__DIR__ . '/input.txt');
if ($debug) {
    $file_content = file_get_contents(__DIR__ . '/debug.txt');
}
$lines = explode(PHP_EOL, $file_content);



function display(string $result): void
{
    echo $result . PHP_EOL;
}

function parseLines(array $lines): array
{
    $parsed = [];
    foreach ($lines as $line) {
        if($line === '') {
            continue;
        }
        $data = explode(' ', $line);
        $parsed[] = [
            'line' => str_split($data[0]),
            'arrangements' => array_map('intval', explode(',', $data[1])),
        ];
    }
    return $parsed;
}

################################
########### PART 1 #############
################################

function getNbPossibilies(array $line, array $arrangements): int
{
    $nbPossibilities = 0;
    // TODO
    // 1. get all positions of ?
    // 2. get all positions of #
    // 3. calculate all possibilities for each arrangement
    return $nbPossibilities;
}

function part1(array $lines): int
{
    $result = 0;
    $parsed = parseLines($lines);
    foreach ($parsed as $line) {
        $result += getNbPossibilies($line['line'], $line['arrangements']);
    }
    return $result;
}

display('Part1: ' . part1($lines));
