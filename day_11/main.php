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

    $empty_rows = [];
    $empty_cols = [];
    $stars_position = [];

    foreach ($lines as $row => $line) {
        if ($line === '') {
            continue;
        }
        if (preg_match('/^\.+$/', $line)) {
            $empty_rows[] = $row;
        }
        foreach (str_split($line) as $col => $case) {
            if ($case === '#') {
                $stars_position[] = [$row, $col];
            }
        }
    }

    //check if col as only dots
    $col = 0;
    while ($col < strlen($lines[0])) {
        $col_is_only_dots = true;
        foreach ($lines as $row) {
            if ($row === '') {
                continue;
            }
            // var_dump($row);
            $row = str_split($row);
            if ($row[$col] !== '.') {
                $col_is_only_dots = false;
                break;
            }
        }
        if ($col_is_only_dots) {
            $empty_cols[] = $col;
        }
        $col++;
    }

    return [
        'stars_position' => $stars_position,
        'empty_rows' => $empty_rows,
        'empty_cols' => $empty_cols,
    ];
}

function resolve(array $lines, int $scale) :int
{
    $game = parseLines($lines);
    $stars_position = $game['stars_position'];
    $empty_rows = $game['empty_rows'];
    $empty_cols = $game['empty_cols'];
    $total = 0;

    // we already une the Manhattan distance to calculate the distance between stars
    $scale--;
    foreach ($stars_position as $star_key_1 => $star_position) {
        // we only need to calculate the distance between each star once
        // so we skip the stars we already calculated
        $star_positions_2 = array_slice($stars_position, $star_key_1);
        foreach ($star_positions_2 as $star_key_2 => $star_position2) {
            $row_star_1 = $star_position[0];
            $col_star_1 = $star_position[1];
            $row_star_2 = $star_position2[0];
            $col_star_2 = $star_position2[1];

            $keys = [$star_key_1, $star_key_2];
            sort($keys);
            $key = implode('-', $keys);

            $distances_ever_calculated[] = $key;
            // calculate the distance between the two stars with the Manhattan distance
            // https://fr.wikipedia.org/wiki/Distance_de_Manhattan
            $distance_without_scale = abs($row_star_1 - $row_star_2) + abs($col_star_1 - $col_star_2);

            // calculate the scale multiplator
            // the scale multiplator is the number of empty rows and cols between the two stars
            $scale_multiplator = 0;
            $min_row = min($row_star_1, $row_star_2);
            $max_row = max($row_star_1, $row_star_2);
            $min_col = min($col_star_1, $col_star_2);
            $max_col = max($col_star_1, $col_star_2);
            foreach($empty_rows as $empty_row) {
                if ($empty_row > $min_row && $empty_row < $max_row) {
                    $scale_multiplator++;
                }
            }
            foreach($empty_cols as $empty_col) {
                if ($empty_col > $min_col && $empty_col < $max_col) {
                    $scale_multiplator++;
                }
            }
            // add the distance between the two stars to the total with the scale
            $total += $distance_without_scale + ($scale * $scale_multiplator);
        }
    }

    return $total;
}

################################
########### PART 1 #############
################################


function part1(array $lines): int
{
    return resolve($lines, 2);
}

################################
########### PART 2 #############
################################


function part2(array $lines): int
{
    return resolve($lines, 1000000);
}


display('Part 1: ' . part1($lines));
display('Part 2: ' . part2($lines));
