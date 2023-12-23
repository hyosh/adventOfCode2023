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
function dumpJson(array $data): void
{
    echo json_encode($data) . PHP_EOL;
}

function parseLines(array $lines): array
{
    $parsed_lines = [];
    foreach ($lines as $index => $line) {
        if ($line === '') {
            continue;
        }
        $points = explode('~', $line);
        $points = [
            ...explode(',', $points[0]),
            ...explode(',', $points[1]),
        ];
        $parsed_lines[] = array_map('intval', $points);

    }

    return $parsed_lines;
}

function overlaps($a, $b)
{
    return max($a[0], $b[0]) <= min($a[3], $b[3]) && max($a[1], $b[1]) <= min($a[4], $b[4]);
}

function getBricks(array $lines) :array
{
    $bricks = parseLines($lines);
    // sort by z position
    usort($bricks, function ($a, $b) {
        return $a[2] <=> $b[2];
    });

    // update z position
    // for each brick, find the max z position of the bricks that it overlaps
    foreach($bricks as $index => $brick) {
        $max_z = 1;
        $over_bricks = array_slice($bricks, 0, $index);
        foreach($over_bricks as $check) {
            if (overlaps($brick, $check)) {
                $max_z = max($max_z, $check[5] + 1);
            }
        }
        $brick[5] -= $brick[2] - $max_z;
        $brick[2] = $max_z;
        $bricks[$index] = $brick;
    }

    usort($bricks, function ($a, $b) {
        return $a[2] <=> $b[2];
    });

    $k_supports_v = [];
    $v_supports_k = [];
    foreach ($bricks as $index => $brick) {
        $k_supports_v[$index] = [];
        $v_supports_k[$index] = [];
    }
    // find the bricks that each brick supports
    foreach ($bricks as $j => $upper) {
        $upper_bricks = array_slice($bricks, 0, $j);
        foreach ($upper_bricks as $i => $lower) {
            if (overlaps($upper, $lower) && $upper[2] === $lower[5] + 1) {
                $k_supports_v[$i][] = $j;
                $v_supports_k[$j][] = $i;
            }
        }
    }

    return [
        'bricks' => $bricks,
        'k_supports_v' => $k_supports_v,
        'v_supports_k' => $v_supports_k,
    ];
}
################################
########### PART 1 #############
################################

function part1(array $lines): int
{
    $total = 0;
    $data = getBricks($lines);
    $bricks = $data['bricks'];
    $k_supports_v = $data['k_supports_v'];
    $v_supports_k = $data['v_supports_k'];
    // count the bricks that have at least 2 supports
    for($i = 0; $i < count($bricks); $i++) {
        $has_enough_supports = true;
        foreach($k_supports_v[$i] as $j) {
            $has_enough_supports = $has_enough_supports && count($v_supports_k[$j]) >= 2;
        }

        if ($has_enough_supports) {
            $total++;
        }

    }
    return $total;
}

################################
########### PART 2 #############
################################

function part2(array $lines): int
{
    $data = getBricks($lines);
    $bricks = $data['bricks'];
    $k_supports_v = $data['k_supports_v'];
    $v_supports_k = $data['v_supports_k'];
    $total = 0;

    for($i= 0; $i < count($bricks); $i++) {
        $q = [];
        foreach($k_supports_v[$i] as $j) {
            if (count($v_supports_k[$j]) === 1) {
                $q[] = $j;
            }
        }
        $falling = [$i, ...$q];

        while(count($q) > 0) {
            $j = array_shift($q);
            foreach($k_supports_v[$j] as $k) {
                if (!in_array($k, $falling) && count(array_diff($v_supports_k[$k], $falling)) === 0) {
                    $q[] = $k;
                    $falling[] = $k;
                }
            }
        }

        $total += count($falling) - 1;
    }
    return $total;
}


display('Part1: ' . part1($lines));
display('Part2: ' . part2($lines));
