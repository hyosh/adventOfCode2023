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

function parseRating(string $line): array
{
    $rating = [];
    $line_trimed = trim($line, '{}');
    $line_parts = explode(',', $line_trimed);
    foreach ($line_parts as $line_part) {
        $line_part = explode('=', $line_part);
        $rating[$line_part[0]] = $line_part[1];
    }

    return $rating;
}

function parseRule(string $line): array
{
    $line_parts = explode('{', $line);
    $rule_name = trim($line_parts[0]);
    $rule_parts = trim($line_parts[1], '}');
    $rules_explode = explode(',', $rule_parts);
    $rules_explode = array_map('trim', $rules_explode);

    return [
        'name' => $rule_name,
        'rules' => $rules_explode,
        'raw' => $line,
    ];
}

function parseLines(array $lines): array
{
    $parsed_lines = [];
    $end_rules = false;
    foreach ($lines as $line) {
        if ($line === '') {
            $end_rules = true;
            continue;
        }
        if ($end_rules) {
            $parsed_lines['ratings'][] = parseRating($line);
        } else {
            $rule = parseRule($line);
            $parsed_lines['rules'][$rule['name']] = [
                'raw' => $rule['raw'],
                'rules' => $rule['rules'],
            ];
        }
    }

    return $parsed_lines;
}

################################
########### PART 1 #############
################################

function checkRating(array $rating, array $rules): int
{

    $current_rule = 'in';
    while ($current_rule !== 'A' && $current_rule !== 'R') {
        $rule = $rules[$current_rule]['rules'];
        foreach ($rule as $rule_part) {
            if (strpos($rule_part, '<') !== false || strpos($rule_part, '>') !== false) {
                $letter = substr($rule_part, 0, 1);
                $sign = substr($rule_part, 1, 1);
                $rest = explode(':', substr($rule_part, 2));
                $value = $rest[0];
                $next_rule = $rest[1];

                $valid =  array_key_exists($letter, $rating);
                if ($valid) {
                    switch ($sign) {
                        case '<':
                            $valid = $rating[$letter] < $value;
                            break;
                        case '>':
                            $valid = $rating[$letter] > $value;
                            break;
                    }
                }

                if ($valid) {
                    $current_rule = $next_rule;
                    break;
                }
            } else {
                // default value
                $current_rule = $rule_part;
                break;
            }
        }
    }
    if ($current_rule === 'R') {
        return 0;
    } else {
        return array_sum($rating);
    }
}

function part1(array $lines): int
{
    $parsed_lines = parseLines($lines);
    $rules = $parsed_lines['rules'];
    $ratings = $parsed_lines['ratings'];
    $total = 0;
    foreach ($ratings as $rating) {
        $total += checkRating($rating, $rules);
    }
    return $total;
}

################################
########### PART 2 #############
################################
function getNbAcceptedPossibilites(array $rules, string $rule_name, array $possibilities): int
{
    if ($rule_name === 'R') {
        return 0;
    }
    if ($rule_name === 'A') {
        $product = 1;
        foreach ($possibilities as $possibility) {
            $product *= $possibility[1] - $possibility[0] + 1;
        }
        return $product;
    }

    $total = 0;
    $rule = $rules[$rule_name]['rules'];
    foreach ($rule as $rule_part) {
        if (strpos($rule_part, '<') !== false || strpos($rule_part, '>') !== false) {
            $letter = substr($rule_part, 0, 1);
            $sign = substr($rule_part, 1, 1);
            $rest = explode(':', substr($rule_part, 2));
            $value = $rest[0];
            $next_rule = $rest[1];

            $ranges = $possibilities[$letter];
            if ($sign === '<') {
                $tmp_range_min = [
                    $ranges[0],
                    min($ranges[1], $value - 1),
                ];
                $tmp_range_max = [
                    max($ranges[0], $value),
                    $ranges[1],
                ];
            }
            else{
                $tmp_range_min = [
                   max($value + 1, $ranges[0]),
                    $ranges[1],
                ];
                $tmp_range_max = [
                    $ranges[0],
                    min($ranges[1], $value),
                ];
            }

            if($tmp_range_min[0] <= $tmp_range_min[1]) {
               $possibilities_clone = [...$possibilities];
                $possibilities_clone[$letter] = $tmp_range_min;
                $total += getNbAcceptedPossibilites($rules, $next_rule, $possibilities_clone);
            }
            if($tmp_range_max[0] <= $tmp_range_max[1]) {
                $possibilities[$letter] = $tmp_range_max;
            }
        } else {
            // default value
            $total += getNbAcceptedPossibilites($rules, $rule_part, $possibilities);
        }
    }


    return $total;
}
function part2(array $lines): int
{
    $parsed_lines = parseLines($lines);
    $rules = $parsed_lines['rules'];
    return getNbAcceptedPossibilites($rules, 'in', [
        'x' => [1, 4000],
        'm' => [1, 4000],
        'a' => [1, 4000],
        's' => [1, 4000],
    ]);
}
display('Part 1: ' . part1($lines));
display('Part 2: ' . part2($lines));
