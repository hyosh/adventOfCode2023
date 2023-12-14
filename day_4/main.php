<?php
$debug = array_key_exists(1, $argv) && $argv[1] === 'debug';

$file_content = file_get_contents(__DIR__ . '/input.txt');
$lines = explode(PHP_EOL, $file_content);

if ($debug) {
    $lines = [
        'Card 1: 41 48 83 86 17 | 83 86  6 31 17  9 48 53',
        'Card 2: 13 32 20 16 61 | 61 30 68 82 17 32 24 19',
        'Card 3:  1 21 53 59 44 | 69 82 63 72 16 21 14  1',
        'Card 4: 41 92 73 84 69 | 59 84 76 51 58  5 54 83',
        'Card 5: 87 83 26 28 32 | 88 30 70 12 93 22 82 36',
        'Card 6: 31 18 13 56 72 | 74 77 10 23 35 67 36 11',
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
    $total = 0;

    foreach ($lines as $line) {
        if (empty($line)) {
            continue;
        }

        $line = explode(':', $line)[1];
        $line = explode('|', $line);
        $winners = array_filter(array_map('trim', explode(' ', $line[0])), function ($number) {
            return $number !== '';
        });
        $hand = array_filter(array_map('trim', explode(' ', $line[1])), function ($number) {
            return $number !== '';
        });
        $points = 0;
        foreach ($hand as $number) {
            if (in_array($number, $winners)) {
                $points++;
            }
        }

        if ($points > 0) {
            $worth_points = pow(2, $points - 1);
            $total += $worth_points;
        }
    }

    return $total;
}

################################
########### PART 2 #############
################################

function part2(array &$lines): int
{
    $scratchcards = [];
    foreach ($lines as $idx => $line) {
        if (empty($line)) {
            continue;
        }

        $line = explode(':', $line)[1];
        $line = explode('|', $line);
        $winners = array_filter(array_map('trim', explode(' ', $line[0])), function ($number) {
            return $number !== '';
        });
        $hand = array_filter(array_map('trim', explode(' ', $line[1])), function ($number) {
            return $number !== '';
        });

        $points = 0;
        foreach ($hand as $number) {
            if (in_array($number, $winners)) {
                $points++;
            }
        }

        $scratchcards[$idx] = $scratchcards[$idx] ?? 0;
        $scratchcards[$idx]++;

        if ($points > 0) {
            for($i = 1; $i <= $points; $i++) {
                $scratchcards[$idx + $i] = $scratchcards[$idx + $i] ?? 0;
                $scratchcards[$idx + $i]+= $scratchcards[$idx];
            }
        }

    }
    return array_sum($scratchcards);
}

display('Part 1: ' . part1($lines));
display('Part 2: ' . part2($lines));
