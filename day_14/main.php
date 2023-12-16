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

function printMap($map): void
{
    foreach ($map as $line) {
        echo $line . PHP_EOL;
    }
}

function parseLines(array $lines): array
{
    $parsed_lines = [];
    foreach ($lines as $line) {
        if ($line === '') {
            continue;
        }
        $parsed_lines[] = $line;
    }

    return $parsed_lines;
}

function rotate(array $map): array
{
    foreach ($map as $row => $row_value) {
        $map[$row] = str_split($row_value);
    }
    $new_map = [];
    for ($col = 0; $col < count($map); $col++) {
        $new_map[] = [];
        for ($row = 0; $row < count($map[0]); $row++) {
            $new_map[$col][] = $map[$row][$col];
        }
        $new_map[$col] = implode('', $new_map[$col]);
    }

    return $new_map;
}

function reverse(array $map): array
{
    foreach ($map as $row => $row_value) {
        $map[$row] = strrev($row_value);
    }
    return $map;
}

function moove(array $map)
{
    // we rotate the map to switch rows and cols
    $map = rotate($map);
    foreach ($map as $row_value) {
        // split row in groups on # char
        // we isolate each group with is limit of moove
        $split_row = explode('#', $row_value);

        // foreach group, we sort chars on right
        // mooving stones to the right
        $new_row = array_map(function ($group) {
            $chars = str_split($group);
            rsort($chars);
            return implode('', $chars);
        }, $split_row);

        // we join each group with # char
        $new_row = implode('#', $new_row);

        // we have the new row
        $new_map[] = $new_row;
    }
    // we reverse each row to prepare the next rotation
    $new_map = reverse($new_map);
    return $new_map;
}

function getResult(array $map): int
{
    $result = 0;
    foreach ($map as $row => $row_value) {
        $stone_value = strlen($row_value) - $row;
        $nb_stones = substr_count($row_value, 'O');
        $result += $stone_value * $nb_stones;
    }

    return $result;
}


################################
########### PART 1 #############
################################
function part1(array $lines): int
{
    $map = parseLines($lines);
    $map = moove($map);
    // get back to normal position
    for ($i = 0; $i < 3; $i++) {
        $map = rotate($map);
        $map = reverse($map);
    }
    return getResult($map);
}

################################
########### PART 2 #############
################################
function cycle(array $map): array
{
    for ($i = 0; $i < 4; $i++) {
        $map = moove($map);
    }

    return $map;
}
function part2(array $lines): int
{
    $parsed_lines = parseLines($lines);
    $map = $parsed_lines;

    $map_string = json_encode($map);
    $key_cache = [$map_string];
    $cache = [$map];
    $wanted_iteration = 1000000000;
    $iterations = 0;
    // build cache loop
    while (true) {
        $iterations++;
        $map = cycle($map);
        $map_string = json_encode($map);
        if (array_key_exists($map_string, $cache)) {
            break;
        }

        $cache[$map_string] = $map;
        $key_cache[] = $map_string;
    };

    $first_match = array_search($map_string, $key_cache);
    $cache_position = ($wanted_iteration - $first_match) % ($iterations - $first_match) + $first_match;
    $map = $cache[$key_cache[$cache_position]];

    return getResult($map);
}

display('Part 1: ' . part1($lines));
display('Part 2: ' . part2($lines));
