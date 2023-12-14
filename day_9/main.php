<?php

$debug = array_key_exists(1, $argv) && $argv[1] === 'debug';

$file_content = file_get_contents(__DIR__ . '/input.txt');
$lines = explode(PHP_EOL, $file_content);

if ($debug) {
    $lines = [
        '0 3 6 9 12 15',
        '1 3 6 10 15 21',
        '10 13 16 21 30 45',
    ];
}

function display(string $result): void
{
    echo $result . PHP_EOL;
}


function parseLines(array $lines): array
{
    $result = [];

    foreach ($lines as $line) {
        if($line === '') {
            continue;
        }
        $result[] = array_map('intval', explode(' ', $line));
    }

    return $result;
}

function array_every($array,$callback)
{

   return  !in_array(false,  array_map($callback,$array));
}

function getNextHistory(array $line, bool $first = false): int
{
    $diffs = [
        [...$line]
    ];
    $index = 0;
    do {
        $row = $diffs[$index];
        $new_line = [];
        for ($i = 0; $i < count($row); $i++) {
            if (!isset($row[$i + 1])) {
                break;
            }
            $new_line[] = $row[$i + 1] - $row[$i];
        }
        $diffs[] = $new_line;
        $index++;
    } while (!array_every($new_line, fn($n) => $n === 0) && count($new_line) > 0);

    $last_diff = 0;
    $diffs = array_reverse($diffs); // we want to start from the end
    foreach ($diffs as $index => $diff) {
        $last_entry = $diff[$first ? 0 : count($diff) - 1];
        if($first){
            $last_diff = $last_entry - $last_diff;
        }else{
            $last_diff = $last_entry + $last_diff;
        }
    }

    return $last_diff;
}

################################
########### PART 1 #############
################################

function part1(array $lines): int
{
    $result = 0;

    $lines = parseLines($lines);

    foreach ($lines as $line) {
        $next_history = getNextHistory($line);
        $result += $next_history;
    }

    return $result;
}

################################
########### PART 2 #############
################################

function part2(array $lines): int
{
    $result = 0;

    $lines = parseLines($lines);

    foreach ($lines as $line) {
        $next_history = getNextHistory($line, true);
        $result += $next_history;
    }

    return $result;
}

display('Part 1: ' . part1($lines));
display('Part 2: ' . part2($lines));
