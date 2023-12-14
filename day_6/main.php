<?php

$debug = array_key_exists(1, $argv) && $argv[1] === 'debug';

$file_content = file_get_contents(__DIR__ . '/input.txt');
$lines = explode(PHP_EOL, $file_content);

if ($debug) {
    $lines = [
        'Time:      7  15   30',
        'Distance:  9  40  200',
    ];
}

function display(string $result): void
{
    echo $result . PHP_EOL;
}

################################
########### PART 1 #############
################################

function parseLines(array $lines): array
{
    $times = explode(' ', str_replace('Time: ', '', $lines[0]));
    $times = array_filter($times, function ($value) {
        return $value !== '';
    });
    $times = array_values($times);
    $distances = explode(' ', str_replace('Distance: ', '', $lines[1]));
    $distances = array_filter($distances, function ($value) {
        return $value !== '';
    });
    $distances = array_values($distances);

    $races = [];
    for ($i = 0; $i < count($times); $i++) {
        $races[] = [
            'time' => intval($times[$i]),
            'distance' => intval($distances[$i]),
        ];
    }
    return $races;
}

function part1(array $lines): int
{
    $result = 1;


    $races = parseLines($lines);

    foreach ($races as $race) {
        $result *= getNbWayToBeat($race);
    }
    return $result;
}

function getNbWayToBeat(array $race): int
{
    $time_to_beat = $race['time'];
    $distance_to_beat = $race['distance'];

    $increment = 0;
    do {
        $min_speed_required = ceil($distance_to_beat / $time_to_beat) + $increment;
        $distance_travelled = ($time_to_beat - $min_speed_required) * $min_speed_required;
        $missing_distance = $distance_to_beat - $distance_travelled;
        $increment++;
    } while ($missing_distance > 0);

    $max_holding_duration = $time_to_beat - ceil($distance_to_beat / ($time_to_beat - $min_speed_required));

    return $max_holding_duration - $min_speed_required + 1;
}

################################
########### PART 2 #############
################################

function parseLines_v2(array $lines): array
{
    $time = str_replace('Time: ', '', $lines[0]);
    $time = intval(str_replace(' ', '', $time));
    $distance = str_replace('Distance: ', '', $lines[1]);
    $distance = intval(str_replace(' ', '', $distance));
    return [
        'time' => $time,
        'distance' => $distance,
    ];
}

function part2(array $lines): int
{
    $race = parseLines_v2($lines);

    return getNbWayToBeat($race);
}



display('Part 1: ' . part1($lines));
display('Part 2: ' . part2($lines));
