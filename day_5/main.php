<?php
$debug = array_key_exists(1, $argv) && $argv[1] === 'debug';

$file_content = file_get_contents(__DIR__ . '/input.txt');
$lines = explode(PHP_EOL, $file_content);

if ($debug) {
    $lines = [
        'seeds: 79 14 55 13',
        '',
        ' seed-to-soil map:',
        '50 98 2',
        '52 50 48',
        '',
        'soil-to-fertilizer map:',
        '0 15 37',
        '37 52 2',
        '39 0 15',
        '',
        'fertilizer-to-water map:',
        '49 53 8',
        '0 11 42',
        '42 0 7',
        '57 7 4',
        '',
        'water-to-light map:',
        '88 18 7',
        '18 25 70',
        '',
        'light-to-temperature map:',
        '45 77 23',
        '81 45 19',
        '68 64 13',
        '',
        'temperature-to-humidity map:',
        '0 69 1',
        '1 0 69',
        '',
        'humidity-to-location map:',
        '60 56 37',
        '56 93 4',
    ];
}

function display(string $result): void
{
    echo $result . PHP_EOL;
}


################################
########### PART 1 #############
################################
function parseLines(array $lines): array
{
    $seeds = explode(' ', str_replace('seeds: ', '', $lines[0]));
    $maps = [];
    $maps['seed-to-soil'] = [];
    $maps['soil-to-fertilizer'] = [];
    $maps['fertilizer-to-water'] = [];
    $maps['water-to-light'] = [];
    $maps['light-to-temperature'] = [];
    $maps['temperature-to-humidity'] = [];
    $maps['humidity-to-location'] = [];

    $current_map = null;
    foreach ($lines as $line) {
        if (strpos($line, 'seeds: ') !== false  || empty($line)) {
            continue;
        }

        if (strpos($line, 'seed-to-soil map:') !== false) {
            $current_map = 'seed-to-soil';
            continue;
        }
        if (strpos($line, 'soil-to-fertilizer map:') !== false) {
            $current_map = 'soil-to-fertilizer';
            continue;
        }
        if (strpos($line, 'fertilizer-to-water map:') !== false) {
            $current_map = 'fertilizer-to-water';
            continue;
        }
        if (strpos($line, 'water-to-light map:') !== false) {
            $current_map = 'water-to-light';
            continue;
        }
        if (strpos($line, 'light-to-temperature map:') !== false) {
            $current_map = 'light-to-temperature';
            continue;
        }
        if (strpos($line, 'temperature-to-humidity map:') !== false) {
            $current_map = 'temperature-to-humidity';
            continue;
        }
        if (strpos($line, 'humidity-to-location map:') !== false) {
            $current_map = 'humidity-to-location';
            continue;
        }


        $maps[$current_map][] = explode(' ', $line);
    }


    return [
        'seeds' => $seeds,
        'maps' => $maps,
    ];
}

function getDestination(array $map, int $position, bool $debug = false): int
{
    $destination = null;
    foreach ($map as $map_line) {
        $destination_range_start = intval($map_line[0]);
        $source_range_start = intval($map_line[1]);
        $range_length = intval($map_line[2]);
        $source_range_end = $source_range_start + $range_length;

        if ($debug) {
            var_dump([
                'destination_range_start' => $destination_range_start,
                'source_range_start' => $source_range_start,
                'source_range_end' => $source_range_end,
                'position' => $position,
                'between' => $source_range_start <= $position && $position <= $source_range_end,
                'new_position' => $destination_range_start + ($position - $source_range_start),
            ]);
        }

        if ($source_range_start <= $position && $position <= $source_range_end) {
            $destination = $destination_range_start + ($position - $source_range_start);
            break;
        }
    }

    if ($destination === null) {
        $destination = $position;
    }

    return $destination;
}


function part1(array $lines): int
{
    $total = INF;
    $parsed_lines = parseLines($lines);
    $seeds = $parsed_lines['seeds'];
    $maps = $parsed_lines['maps'];

    foreach ($seeds as $seed) {
        foreach ($maps as $key => $map) {
            $seed = getDestination($map, $seed);
        }
        $total = min($total, $seed);
    }

    return $total;
}


################################
########### PART 2 #############
################################

function part2(array $lines): int
{
    $parsed_lines = parseLines($lines);
    $seeds = $parsed_lines['seeds'];
    $maps = $parsed_lines['maps'];
    $seeds_grouped = [];

    // we group the seeds to avoid to much iterations
    for ($i = 0; $i < count($seeds); $i += 2) {
        $seed_start = $seeds[$i];
        $seed_max = $seeds[$i + 1] + $seed_start;
        $seeds_grouped[] = [$seed_start, $seed_max];
    }

    // we iterate over the maps
    foreach ($maps as $map) {
        // init the new seeds grouped
        $new_seeds_grouped = [];
        // we iterate over the seeds
        while (count($seeds_grouped) > 0) {
            // we get the first seed
            $seed_group = array_shift($seeds_grouped);
            $seed_start = $seed_group[0];
            $seed_max = $seed_group[1];
            $found = false;
            // we iterate over the map
            foreach ($map as $map_line) {
                $destination_range_start = intval($map_line[0]);
                $source_range_start = intval($map_line[1]);
                $range_length = intval($map_line[2]);

                // we check if the seed is in the map
                $max_start = max($seed_start, $source_range_start);
                $min_end = min($seed_max, $source_range_start + $range_length);

                // if the seed is in the map, we add the new seeds to the new seeds grouped
                if($max_start < $min_end) {
                    $found = true;
                    // we add the new seeds to the new seeds grouped
                    $new_seeds_grouped[] = [
                        $max_start - $source_range_start + $destination_range_start,
                        $min_end - $source_range_start + $destination_range_start,
                    ];
                    // we add the seeds outside the map to the new seeds grouped
                    if($max_start > $seed_start) {
                        $seeds_grouped[] = [
                            $seed_start,
                            $max_start,
                        ];
                    }
                    // we add the seeds outside the map to the new seeds grouped
                    if($seed_max > $min_end) {
                        $seeds_grouped[] = [
                            $min_end,
                            $seed_max,
                        ];
                    }

                    break;
                }
            }
            // if the seed is not in the map, we add it to the new seeds grouped
            if(!$found){
                $new_seeds_grouped[] = [
                    $seed_start,
                    $seed_max,
                ];
            }
        }

        $seeds_grouped = $new_seeds_grouped;
    }

    return min($seeds_grouped)[0];
}

display('Part 1: ' . part1($lines));
display('Part 2: ' . part2($lines));
