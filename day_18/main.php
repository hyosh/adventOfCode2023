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
        $data = explode(' ', $line);
        $parsed_lines[] = [
            'direction' => $data[0],
            'distance' => (int) $data[1],
        ];
    }

    return $parsed_lines;
}

function parseLinesV2(array $lines): array
{
    $parsed_lines = [];
    foreach ($lines as $line) {
        if ($line === '') {
            continue;
        }
        $data = explode(' ', $line);
        $color = trim($data[2], '()');
        $distance = substr($color, 0, 6);
        $distance = hexdec($distance);
        $direction = substr($color, 6);
        $d = null;
        switch($direction) {
            case '0':
                $d = 'R';
                break;
            case '1':
                $d = 'D';
                break;
            case '2':
                $d = 'L';
                break;
            case '3':
                $d = 'U';
                break;
        }
        $parsed_lines[] = [
            'direction' => $d,
            'distance' => $distance,
        ];
    }

    return $parsed_lines;
}


function directionToVector(string $direction): array
{
    if ($direction === 'D') {
        return [1, 0];
    }
    if ($direction === 'U') {
        return [-1, 0];
    }
    if ($direction === 'R') {
        return [0, 1];
    }
    if ($direction === 'L') {
        return [0, -1];
    }
}


function resolve(array $parsed_lines): int
{
    $points = [[0, 0]];
    $total_distance = 0;
    foreach ($parsed_lines as $parsed_line) {
        $vector = directionToVector($parsed_line['direction']);
        $current_position = $points[count($points) - 1];
        $points[] = [
            $current_position[0] + $vector[0] * $parsed_line['distance'],
            $current_position[1] + $vector[1] * $parsed_line['distance'],
        ];
        $total_distance += $parsed_line['distance'];
    }

    $area = 0;
    // https://en.wikipedia.org/wiki/Shoelace_formula
    // calculate area of polygon
    for ($i = 0; $i < count($points); $i++) {
        $a = $points[$i];
        $b = $points[$i - 1] ?? $a;
        $c = $points[($i + 1) % count($points)];
        $area += $a[0] * ($b[1] - $c[1]);
    }

    $area = abs($area) / 2;
    $area = $area - $total_distance / 2 + 1;

    // return area + total_distance;
    return $area + $total_distance;
}
################################
########### PART 1 #############
################################
function part1(array $lines): int
{
    $parsed_lines = parseLines($lines);
    return resolve($parsed_lines);
}

################################
########### PART 2 #############
################################
function part2(array $lines): int
{
    $parsed_lines = parseLinesV2($lines);
    return resolve($parsed_lines);
}
display('Part 1: ' . part1($lines));
display('Part 2: ' . part2($lines));
