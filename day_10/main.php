<?php

$debug = array_key_exists(1, $argv) && $argv[1] === 'debug';

$file_content = file_get_contents(__DIR__ . '/input.txt');
$lines = explode(PHP_EOL, $file_content);


if ($debug) {
    $lines = [
        '...........',
        '.S-------7.',
        '.|F-----7|.',
        '.||.....||.',
        '.||.....||.',
        '.|L-7.F-J|.',
        '.|..|.|..|.',
        '.L--J.L--J.',
        '...........',
    ];
}

function display(string $result): void
{
    echo $result . PHP_EOL;
}

function getMap(array $lines): array
{
    $map = [];
    $start_position = null;
    foreach ($lines as $row => $line) {
        if($line === '') {
            continue;
        }
        $map[] = str_split($line);
        if (false !== strpos($line, 'S')) {
            $start_position = [$row, strpos($line, 'S')];
        }
    }

    return [
        'map' => $map,
        'start_position' => $start_position,
    ];
}

function getLoop($start_position, $map): array
{
    $queue = [$start_position];
    $loop = [];
    while (count($queue) > 0) {
        $current_position = array_shift($queue);
        $row = $current_position[0];
        $col = $current_position[1];
        $current_case = $map[$row][$col];
        if (
            $row > 0 &&
            in_array($current_case, ['S', '|', 'J', 'L']) &&
            !in_array([$row - 1, $col], $loop) &&
            in_array($map[$row - 1][$col], ['|', '7', 'F'])
        ) {
            $queue[] = [$row - 1, $col];
            $loop[] = [$row - 1, $col];
        }

        if (
            $row < count($map) - 1 &&
            in_array($current_case, ['S', '|', '7', 'F']) &&
            !in_array([$row + 1, $col], $loop) &&
            in_array($map[$row + 1][$col], ['|', 'J', 'L'])
        ) {
            $queue[] = [$row + 1, $col];
            $loop[] = [$row + 1, $col];
        }

        if (
            $col > 0 &&
            in_array($current_case, ['S', '-', 'J', '7']) &&
            !in_array([$row, $col - 1], $loop) &&
            in_array($map[$row][$col - 1], ['-', 'F', 'L'])
        ) {
            $queue[] = [$row, $col - 1];
            $loop[] = [$row, $col - 1];
        }

        if (
            $col < count($map[$row]) - 1 &&
            in_array($current_case, ['S', '-', 'F', 'L']) &&
            !in_array([$row, $col + 1], $loop) &&
            in_array($map[$row][$col + 1], ['-', 'J', '7'])
        ) {
            $queue[] = [$row, $col + 1];
            $loop[] = [$row, $col + 1];
        }
    }

    return $loop;
}

################################
########### PART 1 #############
################################

function part1(array $lines): int
{
    $map = getMap($lines);
    $start_position = $map['start_position'];
    $map = $map['map'];

    $loop = getLoop($start_position, $map);

    return ceil(count($loop) / 2);
}

################################
########### PART 2 #############
################################

function printMap($map): void
{
    foreach ($map as $line) {
        echo implode('', $line) . PHP_EOL;
    }
}

function part2(array $lines): int
{
    $map = getMap($lines);
    $start_position = $map['start_position'];
    $map = $map['map'];

    $loop = [$start_position];
    $queue = [$start_position];

    // char possible to close the loop
    $maybe_s = ["|", "-", "J", "L", "7", "F"];

    while (count($queue) > 0) {
        $position = array_shift($queue);
        $row = $position[0];
        $col = $position[1];
        $char = $map[$row][$col];

        // top direction
        if (
            $row > 0 && // we are not at the top of the map
            in_array($char, ['S', '|', 'J', 'L']) && // we can go to the top
            in_array($map[$row - 1][$col], ['|', '7', 'F']) && // the top is connected
            !in_array([$row - 1, $col], $loop) // we didn't already go to the top
        ) {
            $queue[] = [$row - 1, $col];
            $loop[] = [$row - 1, $col];
            if ($char === 'S') {
                $maybe_s = array_filter($maybe_s, function ($item) {
                    return in_array($item, ['|', 'J', 'L']);
                });
            }
        }

        // bottom direction
        if (
            $row < count($map) - 1 && // we are not at the bottom of the map
            in_array($char, ['S', '|', '7', 'F']) && // we can go to the bottom
            in_array($map[$row + 1][$col], ['|', 'J', 'L']) && // the bottom is connected
            !in_array([$row + 1, $col], $loop) // we didn't already go to the bottom
        ) {
            $queue[] = [$row + 1, $col];
            $loop[] = [$row + 1, $col];
            if ($char === 'S') {
                $maybe_s = array_filter($maybe_s, function ($item) {
                    return in_array($item, ['|', '7', 'F']);
                });
            }
        }

        // left direction
        if (
            $col > 0 && // we are not at the left of the map
            in_array($char, ['S', '-', '7', 'J']) && // we can go to the left
            in_array($map[$row][$col - 1], ['-', 'L', 'F']) && // the left is connected
            !in_array([$row, $col - 1], $loop) // we didn't already go to the left
        ) {
            $queue[] = [$row, $col - 1];
            $loop[] = [$row, $col - 1];
            if ($char === 'S') {
                $maybe_s = array_filter($maybe_s, function ($item) {
                    return in_array($item, ['-', '7', 'J']);
                });
            }
        }

        // right direction
        if (
            $col < count($map[$row]) - 1 && // we are not at the right of the map
            in_array($char, ['S', '-', 'L', 'F']) && // we can go to the right
            in_array($map[$row][$col + 1], ['-', 'J', '7']) && // the right is connected
            !in_array([$row, $col + 1], $loop) // we didn't already go to the right
        ) {
            $queue[] = [$row, $col + 1];
            $loop[] = [$row, $col + 1];
            if ($char === 'S') {
                $maybe_s = array_filter($maybe_s, function ($item) {
                    return in_array($item, ['-', 'L', 'F']);
                });
            }
        }
    }

    if(count($maybe_s) > 1) {
        throw new Exception('Too many possibilities');
    }
    $char_to_close = array_shift($maybe_s);
    $map[$start_position[0]][$start_position[1]] = $char_to_close;

    foreach ($map as $row => $line) {
        foreach ($line as $col => $char) {
            if(!in_array([$row, $col], $loop)) {
                $map[$row][$col] = '.';
            }
        }
    }

    $outside = [];
    foreach ($map as $row => $line) {
        $within = false;
        $up = null;
        foreach ($line as $col => $char) {
            if($char === "|") {
                $within = !$within;
            }elseif($char === '-') {
                // nothing to do
            }elseif(in_array($char, ['L', 'F'])) {
                $up = $char === 'L';
            }elseif(in_array($char, ['J', '7'])) {
                if($char != ($up ? 'J' : '7')) {
                    $within = !$within;
                }
                $up = null;
            }elseif($char === '.'){
                // nothing to do
            }else{
                throw new Exception('Unknown char : '.$char);
            }
            if(!$within) {
                if($row === 140 && $col === 0){
                    var_dump($char);
                }else{
                    $outside[] = [$row, $col];
                }
            }
        }
    }
    $merged = array_merge($outside, $loop);
    $merged = array_unique($merged, SORT_REGULAR);

    return count($map) * count($map[0]) - count($merged);
}
display('Part 1: ' . part1($lines));
display('Part 2: ' . part2($lines));
