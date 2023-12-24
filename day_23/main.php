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

function parseLines(array $lines): array
{
    $parsed_lines = [];
    foreach ($lines as $index => $line) {
        if ($line === '') {
            continue;
        }
        $parsed_lines[] = str_split($line);
    }

    return $parsed_lines;
}

function posToString(array $pos): string
{
    return $pos[0] . ',' . $pos[1];
}

function dumpJson(array $data): void
{
    echo json_encode($data) . PHP_EOL;
}


function getMaxDistance(array $pos, array $end, array $graph, array &$seen): int
{
    if ($pos === $end) {
        return 0;
    }

    $max_distance = 0;
    $seen[posToString($pos)] = true;
    foreach ($graph[posToString($pos)] as $neighbour => $distance) {
        if (array_key_exists($neighbour, $seen)) {
            continue;
        }
        $max_distance = max($max_distance, $distance + getMaxDistance(explode(',', $neighbour), $end, $graph, $seen));
    }
    unset($seen[posToString($pos)]);
    return $max_distance;
}

function resolve(array $map): int
{
    $start_pos = [0, array_search('.', $map[0])];
    $end_pos = [count($map) - 1, array_search('.', $map[count($map) - 1])];

    $graph = [
        posToString($start_pos) => [],
        posToString($end_pos) => [],
    ];

    $directions = [
        '^' => [[-1, 0]],
        '>' => [[0, 1]],
        'v' => [[1, 0]],
        '<' => [[0, -1]],
        '.' => [[-1, 0], [0, 1], [1, 0], [0, -1]],
    ];

    // where we will need to take a decision
    $intersections = [
        $start_pos,
        $end_pos,
    ];

    for ($row = 0; $row < count($map); $row++) {
        for ($col = 0; $col < count($map[$row]); $col++) {
            if ($map[$row][$col] === '#') {
                continue;
            }

            $neighbours = 0;
            foreach ($directions['.'] as $direction) {
                $new_row = $row + $direction[0];
                $new_col = $col + $direction[1];
                if (
                    $new_row < 0 || $new_row >= count($map) ||  // out of bounds
                    $new_col < 0 || $new_col >= count($map[$new_row]) || // out of bounds
                    $map[$new_row][$new_col] === '#' // wall
                ) {
                    continue;
                }
                $neighbours++;
            }
            if ($neighbours >= 3) {
                $intersections[] = [$row, $col];
                $graph[posToString([$row, $col])] = [];
            }
        }
    }


    foreach ($intersections as $intersection) {
        $row = $intersection[0];
        $col = $intersection[1];

        $stack = [[0, $row, $col]];
        $seen = [
            posToString([$row, $col]) => true,
        ];

        while (count($stack) > 0) {
            $current = array_pop($stack);
            $distance = $current[0];
            $row = $current[1];
            $col = $current[2];

            if ($distance != 0 && in_array([$row, $col], $intersections)) {
                $graph[posToString($intersection)][posToString([$row, $col])] = $distance;
                continue;
            }

            foreach ($directions[$map[$row][$col]] as $direction) {
                $new_row = $row + $direction[0];
                $new_col = $col + $direction[1];
                if (
                    $new_row >= 0 && $new_row < count($map) && // out of bounds
                    $new_col >= 0 && $new_col < count($map[$new_row]) && // out of bounds
                    $map[$new_row][$new_col] !== '#' && // wall
                    !array_key_exists(posToString([$new_row, $new_col]), $seen) // already seen
                ) {
                    $seen[posToString([$new_row, $new_col])] = true;
                    $stack[] = [$distance + 1, $new_row, $new_col];
                }
            }
        }
    }

    $seen = [
        posToString($start_pos) => true,
    ];

    return getMaxDistance($start_pos, $end_pos, $graph, $seen);
}
################################
########### PART 1 #############
################################


function part1(array $lines): int
{
    $map = parseLines($lines);
    return resolve($map);
}

################################
########### PART 2 #############
################################

function part2(array $lines): int
{
    $map = parseLines($lines);
    for($row = 0; $row < count($map); $row++) {
        for($col = 0; $col < count($map[$row]); $col++) {
            if ($map[$row][$col] === '#') {
                continue;
            }
            // errase < > ^ v
            $map[$row][$col] = '.';
        }
    }
    return resolve($map);
}

display('Part1: ' . part1($lines));
display('Part2: ' . part2($lines));
