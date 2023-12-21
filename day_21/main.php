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
        echo implode('', $line) . PHP_EOL;
    }
}

function dumpJson(array $data): void
{
    echo json_encode($data) . PHP_EOL;
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

################################
########### PART 1 #############
################################
function updateMap(array $map, $start_pos)
{
    $row = $start_pos[0];
    $col = $start_pos[1];
    $all_directions = [
        [0, 1],
        [1, 0],
        [0, -1],
        [-1, 0],
    ];

    $new_inputs = [];
    foreach ($all_directions as $direction) {
        $new_row = $row + $direction[0];
        $new_col = $col + $direction[1];
        if ($new_row < 0 || $new_row >= count($map) || $new_col < 0 || $new_col >= count($map[0])) {
            continue;
        }
        if ($map[$new_row][$new_col] === '.') {
            $map[$new_row][$new_col] = 'O';
            $new_inputs[] = [$new_row, $new_col];
        }
    }

    return [
        'map' => $map,
        'new_inputs' => $new_inputs,
    ];
}

function clearMap(array $map, array $starting_positions)
{
    foreach ($starting_positions as $starting_position) {
        $map[$starting_position[0]][$starting_position[1]] = '.';
    }

    return $map;
}

function part1(array $lines): int
{
    $map = parseLines($lines);
    $startings = [];

    $nb_iterations = 64;
    foreach ($map as $row => $line) {
        foreach ($line as $col => $char) {
            if ($char === 'S') {
                $startings[] = [$row, $col];
                break;
            }
        }
    }
    for ($i = 0; $i < $nb_iterations; $i++) {

        $new_inputs = [];
        foreach ($startings as $start_pos) {
            $result = updateMap($map, $start_pos);
            $map = $result['map'];
            $new_inputs = array_merge($result['new_inputs'], $new_inputs);
        }
        $map = clearMap($map, $startings);
        $startings = $new_inputs;
    }

    return count($startings);
}

################################
########### PART 2 #############
################################
function arrayToKey(array $array): string
{
    return implode('-', $array);
}
function resolvePartialPart(array $map, array $start, int $start_steps): int
{
    $queue = [[
        'row' => $start[0],
        'col' => $start[1],
        'steps' => $start_steps,
    ]];
    $seen[arrayToKey($start)] = true;
    $garden_pots = 0;

    while (count($queue) > 0) {
        $current = array_shift($queue);
        $row = $current['row'];
        $col = $current['col'];
        $steps = $current['steps'];

        // keep even size
        if ($steps % 2 === 0) {
            $garden_pots++;
        }

        if ($steps === 0) {
            continue;
        }

        $all_directions = [
            [0, 1],
            [1, 0],
            [0, -1],
            [-1, 0],
        ];
        foreach ($all_directions as $direction) {
            $new_row = $row + $direction[0];
            $new_col = $col + $direction[1];
            if (
                $new_row < 0 || $new_row >= count($map) ||  // out of bounds
                $new_col < 0 || $new_col >= count($map[0])  || // out of bounds
                $map[$new_row][$new_col] === '#'  || //wall
                array_key_exists(arrayToKey([$new_row, $new_col]), $seen) // already seen
            ) {
                continue;
            }
            $seen[arrayToKey([$new_row, $new_col])] = true;
            $queue[] = [
                'row' => $new_row,
                'col' => $new_col,
                'steps' => $steps - 1,
            ];
        }
    }

    return $garden_pots;
}
function part2(array $lines): int
{
    $map = parseLines($lines);
    $steps = 26501365;
    $starting = [];
    $size = count($map);


    foreach ($map as $row => $line) {
        foreach ($line as $col => $char) {
            if ($char === 'S') {
                $starting = [$row, $col];
                break;
            }
        }
    }

    $grid_width = intdiv($steps, $size) - 1;

    $odd = pow(intdiv($grid_width, 2) * 2 + 1, 2);
    $even = pow(intdiv($grid_width + 1, 2) * 2, 2);

    $odd_points = resolvePartialPart($map, $starting, $size * 2 + 1);
    $even_points = resolvePartialPart($map, $starting, $size * 2);

    $corner_top = resolvePartialPart($map, [$size - 1, $starting[1]], $size - 1);
    $corner_right = resolvePartialPart($map, [$starting[0], 0], $size - 1);
    $corner_bottom = resolvePartialPart($map, [0, $starting[1]], $size - 1);
    $corner_left = resolvePartialPart($map, [$starting[0], $size - 1], $size - 1);

    $small_top_right = resolvePartialPart($map, [$size - 1, 0], intdiv($size, 2) - 1);
    $small_top_left = resolvePartialPart($map, [$size - 1, $size - 1], intdiv($size, 2) - 1);
    $small_bottom_right = resolvePartialPart($map, [0, 0], intdiv($size, 2) - 1);
    $small_bottom_left = resolvePartialPart($map, [0, $size - 1], intdiv($size, 2) - 1);

    $large_top_right = resolvePartialPart($map, [$size - 1, 0], intdiv($size * 3, 2) - 1);
    $large_top_left = resolvePartialPart($map, [$size - 1, $size - 1], intdiv($size * 3, 2) - 1);
    $large_bottom_right = resolvePartialPart($map, [0, 0], intdiv($size * 3, 2) - 1);
    $large_bottom_left = resolvePartialPart($map, [0, $size - 1], intdiv($size * 3, 2) - 1);


    return  $odd * $odd_points +
            $even * $even_points +
            $corner_top + $corner_right + $corner_bottom + $corner_left +
            ($grid_width +1) * ($small_top_right + $small_top_left + $small_bottom_right + $small_bottom_left) +
            $grid_width * ($large_top_right + $large_top_left + $large_bottom_right + $large_bottom_left);
}

display('Part1: ' . part1($lines));
display('Part2: ' . part2($lines));
