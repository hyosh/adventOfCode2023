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
        $exploded_line = explode(' -> ', $line);
        $name  = $exploded_line[0];
        $mode = 'broadcaster';
        if ($name !== 'broadcaster') {
            $mode = str_starts_with($name, '%') ? 'flip-flop' : 'inverter';
            $name = substr($name, 1);
        }

        $outputs = explode(', ', $exploded_line[1]);
        $parsed_lines[$name] = [
            'mode' => $mode,
            'outputs' => $outputs,
            'flip-status' => 'off',
            'memory' => [],
        ];
    }

    foreach ($parsed_lines as $name => $module) {
        foreach ($module['outputs'] as $output) {
            if (array_key_exists($output, $parsed_lines) && $parsed_lines[$output]['mode'] === 'inverter') {
                $parsed_lines[$output]['memory'][$name] = 'low';
            }
        }
    }

    return $parsed_lines;
}

function gcd($a, $b)
{
    return $b ? gcd($b, $a % $b) : $a;
}

function dumpJson(array $data): void
{
    echo json_encode($data) . PHP_EOL;
}

function cycle(array $configuration, callable $callback)
{
    $queue = [];
    $queue[] = [
        'from' => 'button',
        'input' => 'broadcaster',
        'signal' => 'low',
    ];
    while (count($queue) > 0) {
        $current = array_shift($queue);
        $input = $current['input'];
        $signal = $current['signal'];
        $from = $current['from'];
        $module = $configuration[$input] ?? null;
        $callback($current, $module);

        if (!$module) {
            continue;
        }

        $module = $configuration[$input];
        if ($module['mode'] === 'broadcaster') {
            $outputs = $module['outputs'];
            foreach ($outputs as $output) {
                $queue[] = [
                    'from' => $input,
                    'input' => $output,
                    'signal' => $signal,
                ];
            }
        }

        if ($module['mode'] === 'flip-flop') {
            if ($signal === 'low') {
                $module['flip-status'] = $module['flip-status'] === 'off' ? 'on' : 'off'; // flip
                $signal = $module['flip-status'] === 'off' ? 'low' : 'high';
                foreach ($module['outputs'] as $output) {
                    $queue[] = [
                        'from' => $input,
                        'input' => $output,
                        'signal' => $signal,
                    ];
                }
            }
        }

        if ($module['mode'] === 'inverter') {
            $module['memory'][$from] = $signal;
            $all_high = true;
            foreach ($module['memory'] as $memory) {
                if ($memory === 'low') {
                    $all_high = false;
                    break;
                }
            }
            $signal = $all_high ? 'low' : 'high';
            foreach ($module['outputs'] as $output) {
                $queue[] = [
                    'from' => $input,
                    'input' => $output,
                    'signal' => $signal,
                ];
            }
        }

        $configuration[$input] = $module;
    }

    return $configuration;
}


################################
########### PART 1 #############
################################
function part1(array $lines): int
{
    $configuration = parseLines($lines);
    $nb_iterations = 1000;
    $counts = [
        'low' => 0,
        'high' => 0,
    ];

    for ($i = 0; $i < $nb_iterations; $i++) {
        $configuration = cycle($configuration, function ($current_queue,$module) use (&$counts) {
            $counts[$current_queue['signal']]++;
        });
    }
    return $counts['low'] * $counts['high'];
}

################################
########### PART 2 #############
################################

function part2(array $lines): int
{
    $configuration = parseLines($lines);
    $nb_cycles = 0;

    $rx_module_calling = null;
    foreach ($configuration as $name => $module) {
        if(in_array('rx', $module['outputs'])) {
            $rx_module_calling = $name;
            break;
        }
    }

    $seen = [];
    $cycle_length = [];

    foreach ($configuration as $name => $module) {
        if(in_array($rx_module_calling, $module['outputs'])) {
            $seen[$name] = 0;
        }
    }

    $continue = true;
    $result = 1;
    while($continue)
    {
        $nb_cycles++;
        $configuration = cycle($configuration, function ($current_queue, $module) use (&$seen, &$cycle_length, $rx_module_calling, $nb_cycles, &$continue, &$result) {
            // we only care about the rx module with a high signal
            // to call rx module with a low signal, all parents must be high
            // so to find when rx will be called with a low signal we complile cyle of each parent module for high signal
            // and the result will be the lcm of all the cycle lengths
            if($module && $current_queue['input'] === $rx_module_calling && $current_queue['signal'] === 'high') {
                // we count the number of times we see the module
                $seen[$current_queue['from']]++;
                // if we see it for the first time, we store the cycle length
                if(!array_key_exists($current_queue['from'], $cycle_length)) {
                    $cycle_length[$current_queue['from']] = $nb_cycles;
                }
                // if we have seen all the modules, we can stop
                $all_seen = true;
                foreach ($seen as $name => $count) {
                    if($count === 0) {
                        $all_seen = false;
                        break;
                    }
                }
                if($all_seen && $continue) {
                    $continue = false;
                    // we compute the lcm of all the cycle lengths
                    foreach ($cycle_length as $length) {
                        $result *= $length / gcd($result, $length);
                    }
                }
            }
        });
    }


    return $result;
}


display('Part1: ' . part1($lines));
display('Part2: ' . part2($lines));
