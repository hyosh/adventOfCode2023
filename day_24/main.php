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
        $data = explode(' @ ', $line);
        $heilstone = new Heilstone(
            new Vector(...explode(', ', $data[0])),
            new Vector(...explode(', ', $data[1])),
        );
        $parsed_lines[] = $heilstone;
    }

    return $parsed_lines;
}

function dumpJson(array $data): void
{
    echo json_encode($data) . PHP_EOL;
}

class Vector
{
    public function __construct(public float $x, public float $y, public float $z)
    {
    }
}

class Heilstone
{
    public int $c;
    public function __construct(public Vector $position, public Vector $velocity)
    {
        $this->c = $velocity->y * $position->x - $velocity->x * $position->y;
    }

    public function getIntersectPoint(Heilstone $heilstone): ?Vector
    {
        $A1 = $this->velocity->y;
        $B1 = -$this->velocity->x;
        $C1 = $this->c;

        $A2 = $heilstone->velocity->y;
        $B2 = -$heilstone->velocity->x;
        $C2 = $heilstone->c;

        if ($A1 * $B2 === $A2 * $B1) {
            return null;
        }

        $delta = $A1 * $B2 - $A2 * $B1;
        $x = ($C1 * $B2 - $C2 * $B1) / $delta;
        $y = ($C2 * $A1 - $C1 * $A2) / $delta;
        return new Vector($x, $y, 0);
    }
}

################################
########### PART 1 #############
################################


function part1(array $lines): int
{
    global $debug;
    $parsed_lines = parseLines($lines);

    $min = $debug ? 7 : 200000000000000;
    $max = $debug ? 27 : 400000000000000;

    $total = 0;
    foreach ($parsed_lines as $index => $heilstone) {
        $rest_heilstones = array_slice($parsed_lines, 0, $index);
        foreach ($rest_heilstones as $heilstone2) {
            if ($intersectionPoint = $heilstone->getIntersectPoint($heilstone2)) {
                if (
                    $min <= $intersectionPoint->x && $intersectionPoint->x <= $max &&
                    $min <= $intersectionPoint->y && $intersectionPoint->y <= $max
                ) {
                    $checked = true;
                    foreach ([$heilstone, $heilstone2] as $hs) {
                        if (
                            (($intersectionPoint->x -  $hs->position->x) * $hs->velocity->x) < 0  &&
                            (($intersectionPoint->y -  $hs->position->y) * $hs->velocity->y) < 0
                        ) {
                            $checked = false;
                            break;
                        }
                    }
                    if ($checked) {
                        $total++;
                    }
                }
            }
        }
    }

    return $total;
}

################################
########### PART 2 #############
################################

function part2(array $lines): int
{
    global $debug;
    $filename = $debug ? 'debug.txt' : 'input.txt';
    exec('python3 part2.py '.$filename, $output);
    return $output[0];
}

display('Part1: ' . part1($lines));
display('Part2: ' . part2($lines));
