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
    $parsed_lines = [];
    foreach ($lines as $line) {
        if ($line === '') {
            continue;
        }
        $parsed_lines[] = str_split($line);
    }

    return $parsed_lines;
}

function printMap($map): void
{
    foreach ($map as $line) {
        echo implode('', $line) . PHP_EOL;
    }
}


################################
########### PART 1 #############
################################
$cached_maps = [];
function rollStones(array $map, string $direction = 'north'): array
{
    $copy_map = $map;

    foreach ($copy_map as $row => $row_value) {
        foreach ($row_value as $col => $col_value) {
            if ($col_value === 'O') {
                $copy_map[$row][$col] = '.';
            }
        }
    }
    $new_stones = [];
    if ($direction === 'north') {
        for ($col = 0; $col < count($map); $col++) {
            $stones_to_roll = [];
            for ($row = count($map[0]) - 1; $row >= 0; $row--) {
                $char = $map[$row][$col];
                if ($char === 'O') {
                    $stones_to_roll[] = count($map) - $row;
                } elseif ($char === '#') {
                    $new_stones = array_merge($new_stones, $stones_to_roll);
                    foreach ($stones_to_roll as $stone) {
                        $copy_map[count($map) - $stone][$col] = 'O';
                    }
                    $stones_to_roll = [];
                } elseif ($char === '.') {
                    $stones_to_roll = array_map(function ($stone) {
                        return $stone + 1;
                    }, $stones_to_roll);
                }
            }
            foreach ($stones_to_roll as $stone) {
                $copy_map[count($map) - $stone][$col] = 'O';
            }
            $new_stones = array_merge($new_stones, $stones_to_roll);
        }
    }
    if ($direction === 'south') {
        for ($col = 0; $col < count($map); $col++) {
            $stones_to_roll = [];
            for ($row = 0; $row < count($map[0]); $row++) {
                $char = $map[$row][$col];
                if ($char === 'O') {
                    $stones_to_roll[] = $row;
                } elseif ($char === '#') {
                    $new_stones = array_merge($new_stones, $stones_to_roll);
                    foreach ($stones_to_roll as $stone) {
                        $copy_map[$stone][$col] = 'O';
                    }
                    $stones_to_roll = [];
                } elseif ($char === '.') {
                    $stones_to_roll = array_map(function ($stone) {
                        return $stone + 1;
                    }, $stones_to_roll);
                }
            }
            foreach ($stones_to_roll as $stone) {
                $copy_map[$stone][$col] = 'O';
            }
            $new_stones = array_merge($new_stones, $stones_to_roll);
        }
    }

    if ($direction === 'east') {
        for ($row = 0; $row < count($map); $row++) {
            $stones_to_roll = [];
            for ($col = 0; $col < count($map[0]); $col++) {
                $char = $map[$row][$col];
                if ($char === 'O') {
                    $stones_to_roll[] = $col;
                } elseif ($char === '#') {
                    $new_stones = array_merge($new_stones, $stones_to_roll);
                    foreach ($stones_to_roll as $stone) {
                        $copy_map[$row][$stone] = 'O';
                    }
                    $stones_to_roll = [];
                } elseif ($char === '.') {
                    $stones_to_roll = array_map(function ($stone) {
                        return $stone + 1;
                    }, $stones_to_roll);
                }
            }
            foreach ($stones_to_roll as $stone) {
                $copy_map[$row][$stone] = 'O';
            }
            $new_stones = array_merge($new_stones, $stones_to_roll);
        }
    }

    if ($direction === 'west') {
        for ($row = 0; $row < count($map); $row++) {
            $stones_to_roll = [];
            for ($col = count($map[0]) - 1; $col >= 0; $col--) {
                $char = $map[$row][$col];
                if ($char === 'O') {
                    $stones_to_roll[] = count($map[0]) - $col;
                } elseif ($char === '#') {
                    $new_stones = array_merge($new_stones, $stones_to_roll);
                    foreach ($stones_to_roll as $stone) {
                        $copy_map[$row][count($map[0]) - $stone] = 'O';
                    }
                    $stones_to_roll = [];
                } elseif ($char === '.') {
                    $stones_to_roll = array_map(function ($stone) {
                        return $stone + 1;
                    }, $stones_to_roll);
                }
            }
            foreach ($stones_to_roll as $stone) {
                $copy_map[$row][count($map[0]) - $stone] = 'O';
            }
            $new_stones = array_merge($new_stones, $stones_to_roll);
        }
    }

    // printMap($copy_map);
    // echo PHP_EOL;

    return [
        'map' => $copy_map,
        'new_stones' => $new_stones
    ];
}

function part1(array $lines): int
{
    $parsed_lines = parseLines($lines);
    return array_sum(rollStones($parsed_lines)['new_stones']);
}

################################
########### PART 2 #############
################################
function cycle(array $map): array
{
    $result = rollStones($map, 'north', true);
    $map = $result['map'];
    $result = rollStones($map, 'west', true);
    $map = $result['map'];
    $result = rollStones($map, 'south', true);
    $map = $result['map'];
    $result = rollStones($map, 'east', true);
    return $result;
}
function part2(array $lines): int
{
    $parsed_lines = parseLines($lines);
    $map = $parsed_lines;
    $new_stones = [];
    $cache = [];
    for ($i = 0; $i < 1000000000; $i++) {
        $map_string = json_encode($map);
        if (array_key_exists($map_string, $cache)) {
            $result = $cache[$map_string];
            $map = $result['map'];
        }else{
            $result = cycle($map);
            $map = $result['map'];
            $cache[$map_string] = $result;
        }

        $new_stones = $result['new_stones'];
    }

    printMap($map);

    return array_sum($new_stones);
}

display('Part 1: ' . part1($lines));
display('Part 2: ' . part2($lines));
