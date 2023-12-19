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
        echo implode('',$line) . PHP_EOL;
    }
}

function getVectorDirection(array $vector) :string
{
    $x = $vector[0];
    $y = $vector[1];

    if ($x === 0 && $y === 1) {
        return 'down';
    }
    if ($x === 0 && $y === -1) {
        return 'up';
    }
    if ($x === 1 && $y === 0) {
        return 'right';
    }
    if ($x === -1 && $y === 0) {
        return 'left';
    }
}

function directionToVector(string $direction): array
{
    if ($direction === 'down') {
        return [0, 1];
    }
    if ($direction === 'up') {
        return [0, -1];
    }
    if ($direction === 'right') {
        return [1, 0];
    }
    if ($direction === 'left') {
        return [-1, 0];
    }
}

function addVectors(array $vector1, array $vector2): array
{
    $x = $vector1[0] + $vector2[0];
    $y = $vector1[1] + $vector2[1];

    return [$x, $y];
}

$loop = [];
$energized = [];
function navigate(array $map, array $position, array $vector, int $energizedCount = 0): array
{
    global $loop, $energized;
    $new_position = addVectors($position, $vector);
    $char = $map[$new_position[1]][$new_position[0]] ?? null;

    $direction = getVectorDirection($vector);

        if ($char === null) {
        return [
            'map' => $map,
            'total' => $energizedCount,
        ];
    }

    $key_loop = $new_position[0] . '-' . $new_position[1] . '-' . $direction;

    if (array_key_exists($key_loop, $loop)) {
        return [
            'map' => $map,
            'total' => $energizedCount,
        ];
    }

    $loop[$key_loop] = true;
    $key_energized = $new_position[0] . '-' . $new_position[1];
    if(!array_key_exists($key_energized, $energized)) {
        $energized[$key_energized] = true;
        $energizedCount++;
    }

    $vectors_possible = getVectorFromCharDirection($char, $direction);
    do{
        $vector_direction = array_shift($vectors_possible);
        $result = navigate($map, $new_position, $vector_direction, $energizedCount);
        $energizedCount = $result['total'];
    }while(count($vectors_possible) > 0);

    return [
        'map' => $map,
        'total' => $energizedCount,
    ];
}

/**
 * gestion des cas simples pour le point de départ
 */
function getVectorFromCharDirection(string $char, $direction): array
{
    if ($char === '/') {
        if ($direction === 'down') {
            return [directionToVector('left')];
        }elseif($direction === 'up') {
            return [directionToVector('right')];
        }elseif($direction === 'right') {
            return [directionToVector('up')];
        }elseif($direction === 'left') {
            return [directionToVector('down')];
        }
    }elseif($char === '\\') {
        if ($direction === 'down') {
            return [directionToVector('right')];
        }elseif($direction === 'up') {
            return [directionToVector('left')];
        }elseif($direction === 'right') {
            return [directionToVector('down')];
        }elseif($direction === 'left') {
            return [directionToVector('up')];
        }
    }elseif($char === '-') {
        if($direction === 'down' || $direction === 'up') {
            $vector_left = directionToVector('left');
            $vector_right = directionToVector('right');
            return [$vector_left, $vector_right];
        }else{
            return [directionToVector($direction)];
        }
    }elseif($char === '|') {
        if($direction === 'right' || $direction === 'left') {
            $vector_up = directionToVector('up');
            $vector_down = directionToVector('down');
            return [$vector_up, $vector_down];
        }else{
            return [directionToVector($direction)];
        }
    }
    return [directionToVector($direction)];
}

################################
########### PART 1 #############
################################
function part1(array $lines): int
{
    $map = parseLines($lines);
    $initial_position = [0, 0];
    $first_char = $map[$initial_position[1]][$initial_position[0]];
    $initial_vector = getVectorFromCharDirection($first_char, 'right')[0];

    return navigate($map, $initial_position, $initial_vector)['total'];
}

################################
########### PART 1 #############
################################
function clearEnergyMap(array $energizedMap): array
{
   $new_energizedMap = [];
    foreach ($energizedMap as $row) {
         $new_energizedMap[] = str_split(str_pad('', count($row), '.'));
    }

    return $new_energizedMap;
}

function resetLoopEnergized() :void
{
    global $loop, $energized;
    $loop = [];
    $energized = [];
}

function part2(array $lines): int
{
    $map = parseLines($lines);
    $max_result = 0;

    foreach($map as $r => $row) {
        resetLoopEnergized();
        $max_result = max($max_result, navigate($map, [$r, 0], directionToVector('down'), 0)['total']);
        resetLoopEnergized();
        $max_result = max($max_result, navigate($map, [$r, count($row) - 1], directionToVector('up'), 0)['total']);
    }

    foreach($map[0] as $c => $char) {
        resetLoopEnergized();
        $max_result = max($max_result, navigate($map, [0, $c], directionToVector('left'), 0)['total']);
        resetLoopEnergized();
        $max_result = max($max_result, navigate($map, [count($map) - 1, $c], directionToVector('right'), 0)['total']);
    }

    return $max_result;
}


display('Part 1: ' . part1($lines));
display('Part 2: ' . part2($lines));
