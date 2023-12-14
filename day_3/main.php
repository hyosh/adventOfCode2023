<?php

$debug = array_key_exists(1, $argv) && $argv[1] === 'debug';

$file_content = file_get_contents(__DIR__ . '/input.txt');
$lines = explode(PHP_EOL, $file_content);

if ($debug) {
    $lines = [
        '467..114..',
        '...*......',
        '..35..633.',
        '......#...',
        '617*......',
        '.....+.58.',
        '..592.....',
        '......755.',
        '...$.*....',
        '.664.598..'
    ];
}

function display(string $result): void
{
    echo $result . PHP_EOL;
}


################################
########### PART 1 #############
################################
function part1(array $lines): int
{
    $result = 0;

    for ($i = 0; $i < count($lines); $i++) {
        $tmp_digit = '';
        $is_valid = false;
        for ($j = 0; $j < strlen($lines[$i]); $j++) {
            $char = $lines[$i][$j];
            if (is_numeric($char)) {
                $tmp_digit .= $char;
                $chars_arround = [
                    $lines[$i][$j - 1] ?? '',
                    $lines[$i][$j + 1] ?? '',
                    $lines[$i - 1][$j] ?? '',
                    $lines[$i + 1][$j] ?? '',
                    $lines[$i - 1][$j - 1] ?? '',
                    $lines[$i - 1][$j + 1] ?? '',
                    $lines[$i + 1][$j - 1] ?? '',
                    $lines[$i + 1][$j + 1] ?? '',
                ];
                $chars_arround = implode('', $chars_arround);
                $chars_arround = str_replace(['.'], '', $chars_arround);
                $is_valid = $is_valid || preg_match('/[^A-Za-z0-9]/', $chars_arround);
            } else {
                if ($tmp_digit !== '') {
                    if ($is_valid) {
                        $result += (int) $tmp_digit;
                    }
                    $tmp_digit = '';
                    $is_valid = false;
                }
            }
        }

        if ($tmp_digit !== '') {
            if ($is_valid) {
                $result += (int) $tmp_digit;
            }
            $tmp_digit = '';
            $is_valid = false;
        }
    }

    return $result;
}

################################
########### PART 2 #############
################################

function part2(array $lines)
{
    $result = 0;
    $results_part = [];
    for ($i = 0; $i < count($lines); $i++) {
        for ($j = 0; $j < strlen($lines[$i]); $j++) {
            $char = $lines[$i][$j];
            // if not a number continue
            if (!is_numeric($char)) {
                continue;
            }
            $numval = '';
            // save the position of the number
            $k = $j;
            // get the number
            while ($k < strlen($lines[$i]) && is_numeric($lines[$i][$k])) {
                $numval .= $lines[$i][$k];
                $k++;
            }
            $numval = (int) $numval;

            // check the 8 directions around the number and save the result in an array with star position as key
            for ($l = $j - 1; $l < $k + 1; $l++) {
                // top part
                if (($lines[$i - 1][$l] ?? '') == '*') {
                    $results_part[$i - 1 . '-' . $l][] = $numval;
                }
                //  same line part
                if (($lines[$i][$l] ?? '') == '*') {
                    $results_part[$i . '-' . $l][] = $numval;
                }
                // bottom part
                if (($lines[$i + 1][$l] ?? '') == '*') {
                    $results_part[$i + 1 . '-' . $l][] = $numval;
                }
            }

            // set the position of the number to the last position of the number
            $j = $k;
        }
    }

    foreach ($results_part as $key => $value) {
        if (count($value) == 2) {
            $result += $value[0] * $value[1];
        }
    }

    return [
        'result' => $result,
        'results_part' => $results_part,
    ];
}


display('Part1: ' . part1($lines));
$part2 = part2($lines);
display('Part2: ' . $part2['result']);
