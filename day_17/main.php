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

function addToBinaryTree(array $tree, $item, int $weigth): array
{
    if ($tree === []) {
        return [
            'item' => $item,
            'weigth' => $weigth,
            'left' => [],
            'right' => [],
        ];
    }

    if ($item < $tree['item']) {
        $tree['left'] = addToBinaryTree($tree['left'], $item, $weigth);
    } else {
        $tree['right'] = addToBinaryTree($tree['right'], $item, $weigth);
    }

    return $tree;
}

function getLowestItem(array &$tree): array
{
    if ($tree['left'] === []) {
        $item = $tree['item'];
        $tree = $tree['right'];
        return $item;
    }

    return getLowestItem($tree['left']);
}


function resolve(array $map, int $max_nb_time, ?int $min_same_direction = null): int
{
    $seen = [];
    $queue = addToBinaryTree([], [
        'heat' => 0,
        'position' => [0, 0],
        'vecteur' => [0, 0],
        'nb_times' => 0,
    ], 0);

    while (count($queue) > 0) {
        $current = getLowestItem($queue);
        $heat = $current['heat'];
        $position = $current['position'];
        $vecteur = $current['vecteur'];
        $nb_times = $current['nb_times'];


        // we reached the end
        if ($position[0] === count($map) - 1 && $position[1] === count($map[0]) - 1) {
            return $heat;
        }

        // we already saw this position with this vector and this number of times
        $key = implode('-', $position) . ',' . implode('-', $vecteur) . ',' . $nb_times;
        if (array_key_exists($key, $seen)) {
            continue;
        }
        // we add this position to the seen positions
        $seen[$key] = true;

        // we can go in the same direction if we didn't go more than 3 times in the same direction
        // and if we don't go in the opposite direction
        if ($nb_times < $max_nb_time && $vecteur !== [0, 0]) {
            $new_position = addVectors($position, $vecteur);
            $position_heat = $map[$new_position[0]][$new_position[1]] ?? null;
            if ($position_heat !== null) {
                $queue  = addToBinaryTree($queue, [
                    'heat' => $heat + $position_heat,
                    'position' => $new_position,
                    'vecteur' => $vecteur,
                    'nb_times' => $nb_times + 1,
                ], $heat + $position_heat);
            }
        }

        if (is_null($min_same_direction) || ($nb_times >= $min_same_direction || $vecteur === [0, 0])) {
            // search for new directions
            $directions = ['down', 'right', 'up', 'left'];
            foreach ($directions as $direction) {
                $new_vecteur = directionToVector($direction);
                if ($new_vecteur !== $vecteur && $new_vecteur !== [-$vecteur[0], -$vecteur[1]]) {
                    $new_position = addVectors($position, $new_vecteur);
                    $position_heat = $map[$new_position[0]][$new_position[1]] ?? null;
                    if ($position_heat !== null) {
                        $queue = addToBinaryTree($queue, [
                            'heat' => $heat + $position_heat,
                            'position' => $new_position,
                            'vecteur' => $new_vecteur,
                            'nb_times' => 1,
                        ], $heat + $position_heat);
                    }
                }
            }
        }

    }

    return 0;
}



################################
########### PART 1 #############
################################
function part1(array $lines): int
{
    $parsed_lines = parseLines($lines);
    return resolve($parsed_lines, 3);
}

################################
########### PART 2 #############
################################
function part2(array $lines): int
{
    $parsed_lines = parseLines($lines);
    return resolve($parsed_lines, 10, 4);
}
display('Part 1: ' . part1($lines));
display('Part 2: ' . part2($lines));
