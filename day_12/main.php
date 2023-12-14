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

function parseLines(array $lines, int $nb_copies = 1): array
{
    $parsed = [];
    foreach ($lines as $line) {
        if ($line === '') {
            continue;
        }
        $data = explode(' ', $line);
        $line = '';
        $arrangements = '';
        for ($i = 0; $i < $nb_copies; $i++) {
            $line .= $data[0] . '?';
            $arrangements .= $data[1] . ',';
        }
        $line = substr($line, 0, -1);
        $arrangements = substr($arrangements, 0, -1);
        $parsed[] = [
            'line' => $line,
            'arrangements' => array_map('intval', explode(',', $arrangements)),
        ];
    }
    return $parsed;
}
$cache = [];
function getNbPossibilies(string $line, array $arrangements): int
{
    global $cache;
    // if line is empty and no more arrangements, it's a possibility
    if ($line === '') {
        return count($arrangements) === 0 ? 1 : 0;
    }

    // if no more arrangements, and line don't contains #, it's a possibility
    if (count($arrangements) === 0) {
        return strpos($line, '#') === false ? 1 : 0;
    }

    $key = $line . '-' . implode('-', $arrangements);

    if(array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    $nb_possibilities = 0;
    $firstChar = $line[0];
    // if first char is . or ?, we get the nexts possibilities
    if (in_array($firstChar, ['.', '?'])) {
        $nb_possibilities += getNbPossibilies(substr($line, 1), $arrangements);
    }

    $arrangement = $arrangements[0];
    // if first char is # or ?, we get the nexts possibilities
    if (in_array($firstChar, ['#', '?'])) {
        if (
            strlen($line) >= $arrangement && // line is long enough
            strpos(substr($line, 0, $arrangement), '.') === false && // don't contains dot on arrangement length
            ($arrangement === strlen($line) || $line[$arrangement] !== '#') // arrangement equals line length or last char of arrangement length +1 is not #
        ) {
            // we can calculate the next possibilities for the next arrangements
            $nb_possibilities += getNbPossibilies(substr($line, $arrangement + 1), array_slice($arrangements, 1));
        }
    }

    $cache[$key] = $nb_possibilities;

    return $nb_possibilities;
}

################################
########### PART 1 #############
################################

function part1(array $lines): int
{
    $result = 0;
    $parsed = parseLines($lines);
    foreach ($parsed as $line) {
        $result += getNbPossibilies($line['line'], $line['arrangements']);
    }
    return $result;
}

################################
########### PART 1 #############
################################

function part2(array $lines): int
{
    $result = 0;
    $parsed = parseLines($lines, 5);
    foreach ($parsed as $line) {
        $result += getNbPossibilies($line['line'], $line['arrangements']);
    }
    return $result;
}


display('Part1: ' . part1($lines));
display('Part2: ' . part2($lines));
