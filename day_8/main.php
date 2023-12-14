<?php

$debug = array_key_exists(1, $argv) && $argv[1] === 'debug';

$file_content = file_get_contents(__DIR__ . '/input.txt');
$lines = explode(PHP_EOL, $file_content);

if ($debug) {
    $lines = [
        'LR',
        '',
        '11A = (11B, XXX)',
        '11B = (XXX, 11Z)',
        '11Z = (11B, XXX)',
        '22A = (22B, XXX)',
        '22B = (22C, 22C)',
        '22C = (22Z, 22Z)',
        '22Z = (22B, 22B)',
        'XXX = (XXX, XXX)',
    ];
}

function display(string $result): void
{
    echo $result . PHP_EOL;
}

function parseLines(array $lines): array
{
    $result = [
        'instructions' => [],
        'nodes' => [],
        'starting_nodes' => [],
    ];

    foreach ($lines as $index => $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        if ($index === 0) {
            $result['instructions'] = str_split($line);
            continue;
        }

        $parts = explode(' = ', $line);
        if (str_ends_with($parts[0], 'A')) {
            $result['starting_nodes'][] = $parts[0];
        }
        $result['nodes'][$parts[0]] = array_map('trim', explode(',', str_replace(['(', ')'], '', $parts[1])));
    }

    return $result;
}

################################
########### PART 1 #############
################################

function part1(array $lines): int
{
    $result = 0;
    $game = parseLines($lines);
    $start = 'AAA';
    $end = 'ZZZ';
    $instruction_index = 0;
    do {
        $instruction = $game['instructions'][$instruction_index];
        $instruction_index++;
        $node = $game['nodes'][$start];
        $start = $node[$instruction === 'R' ? 1 : 0];
        $result++;
        if ($instruction_index === count($game['instructions'])) {
            $instruction_index = 0;
        }
    } while ($start !== $end);
    return $result;
}

################################
########### PART 2 #############
################################

function part2(array $lines): int
{

    $game = parseLines($lines);
    $starts = $game['starting_nodes'];
    $steps = $game['instructions'];
    $nodes = $game['nodes'];
    // store the number of steps for each starting node to reach the end
    $nb_steps = [];

    // iterate over all starting nodes
    foreach ($starts as $start) {
        $current_steps = $steps;
        $step_count = 0;
        do{
            $step_count++;
            $start = $nodes[$start][$current_steps[0] === 'R' ? 1 : 0];
            $current_steps = array_slice($current_steps, 1);
            // if we have reached the end, reset the steps
            if (count($current_steps) === 0) {
                $current_steps = $steps;
            }
        }while(!str_ends_with($start, 'Z'));
        $nb_steps[] = $step_count;
    }

    // lcm = least common multiple
    // https://en.wikipedia.org/wiki/Least_common_multiple
    // we are looking for the number of steps to reach the end for all starting nodes
    // so we need to find the least common multiple of all these numbers
    // for example, if we have 3 starting nodes with 2, 3 and 4 steps to reach the end
    // we need to find the least common multiple of 2, 3 and 4
    // lcm(2, 3, 4) = 12
    $lcm = 1;
    foreach ($nb_steps as $num) {
        $lcm = $lcm * $num / gcd($lcm, $num);
    }

    return $lcm;
}
// greatest common divisor
function gcd($a, $b)
{
    return $b ? gcd($b, $a % $b) : $a;
}

// display('Part 1: ' . part1($lines));
display('Part 2: ' . part2($lines));
