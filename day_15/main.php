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

function parseLine(array $lines): array
{
    return explode(',', $lines[0]);
}

function getValue(string $hash): int
{
    $char_position = 0;
    $current = 0;

    while ($char_position < strlen($hash)) {
        $current += ord($hash[$char_position]);
        $current *= 17;
        $current = $current % 256;
        $char_position++;
    }

    return $current;
}


################################
########### PART 1 #############
################################
function part1(array $lines): int
{
    $lines = parseLine($lines);

    return array_sum(array_map('getValue', $lines));
}

################################
########### PART 2 #############
################################
function part2(array $lines): int
{
    $lines = parseLine($lines);
    $boxs = [];
    $focals = [];

    foreach ($lines as $hash) {
        if (strpos($hash, '-') !== false) {
            $hash = explode('-', $hash);
            $box_number = getValue($hash[0]);
            if(array_key_exists($box_number, $boxs)) {
                $idx = array_search($hash[0], $boxs[$box_number]);
                if($idx !== false) {
                    unset($boxs[$box_number][$idx]);
                }
            }
        } elseif (strpos($hash, '=') !== false) {
            $hash = explode('=', $hash);
            $box_number = getValue($hash[0]);
            if(!array_key_exists($box_number, $boxs)) {
                $boxs[$box_number] = [];
            }
            if(!in_array($hash[0], $boxs[$box_number])) {
                $boxs[$box_number][] = $hash[0];
            }
            $focals[$hash[0]] = $hash[1];

        }
    }

    $total = 0;
    foreach ($boxs as $box_number => $box) {
        $box_number++;
        $focus_key = 0;
        foreach ($box as $label) {
            $focus_key++;
            $total += $box_number * $focus_key * $focals[$label];
        }
    }
    return $total;
}

display('Part 1: ' . part1($lines));
display('Part 2: ' . part2($lines));
