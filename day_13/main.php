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
    foreach ($map as $index => $line) {
        echo $index . ' : ' . $line . PHP_EOL;
    }
}

function rotatePatern90(array $patern): array
{
    $patern90 = [];
    $with = count(str_split($patern[0]));
    $height = count($patern);

    for ($i = 0; $i < $with; $i++) {
        $line = '';
        for ($j = 0; $j < $height; $j++) {
            $line .= $patern[$j][$i];
        }
        $patern90[] = strrev($line);
    }

    return $patern90;
}

function parseLines(array $lines): array
{
    $paterns = [];
    $current_patern = [];
    foreach ($lines as $line) {
        if ($line === '') {
            $paterns[] = $current_patern;
            $current_patern = [];
            continue;
        }
        $current_patern[] = $line;
    }
    return $paterns;
}

################################
########### PART 1 #############
################################

function getReflections(array $patern, ?callable $checkDifferences = null): int
{
    for ($index = 1; $index < count($patern); $index++) {
        $previous_block_range = [0, $index];
        $next_block_range = [$index, count($patern) - 1];
        $previous_block = implode('', array_reverse(array_slice($patern, ...$previous_block_range)));
        $next_block = implode('', array_slice($patern, ...$next_block_range));
        $previous_block_length = strlen($previous_block);
        $next_block_length = strlen($next_block);

        $previous_block = substr($previous_block, 0, $next_block_length);
        $next_block = substr($next_block, 0, $previous_block_length);

        if ($checkDifferences !== null && $checkDifferences($previous_block, $next_block) ||
            ($checkDifferences === null && $previous_block === $next_block)
        ) {
            return $index;
        }
    }
    return 0;
}

function part1(array $lines): int
{
    $paterns = parseLines($lines);
    $total = 0;
    foreach ($paterns as $patern) {
        $total += getReflections($patern) * 100;
        $total += getReflections(rotatePatern90($patern));
    }
    return $total;
}

################################
########### PART 2 #############
################################
function checkDifferences(string $previous_block, string $next_block): bool
{
    $nb_differences = 0;
    for ($i = 0; $i < strlen($previous_block); $i++) {
        if ($previous_block[$i] !== $next_block[$i]) {
            $nb_differences++;
            if($nb_differences > 1) return false;
        }
    }
    return $nb_differences === 1;
}
function part2(array $lines): int
{
    $paterns = parseLines($lines);
    $total = 0;
    foreach ($paterns as $patern) {
        $total += getReflections($patern, 'checkDifferences') * 100;
        $total += getReflections(rotatePatern90($patern), 'checkDifferences');
    }
    return $total;
}



display('Part 1: ' . part1($lines));
display('Part 2: ' . part2($lines));
