<?php
$debug = array_key_exists(1, $argv) && $argv[1] === 'debug';

$file_content = file_get_contents(__DIR__ . '/input.txt');
$lines = explode(PHP_EOL, $file_content);

function display(string $result) :void
{
    echo $result . PHP_EOL;
}

################################
########### PART 1 #############
################################
function part1(array $lines): int
{
    $total = 0;
    foreach ($lines as $word) {
        preg_match_all('/\d{1}/', $word, $matches);
        $numbers = $matches[0];
        $number =($numbers[0] ?? '') .''.($numbers[count($numbers) - 1]?? '');
        $total += intval($number);
    }

    return $total;
}

################################
########### PART 2 #############
################################
function extractDigits(string $word) :string
{
    $digits = [];
    $numbers = ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
    $i = 0;
    while($i < strlen($word)){
       $char = $word[$i];
         if(is_numeric($char)){
            $digits[] = $char;
         }else{
            foreach ($numbers as $index => $number) {
                $part = substr($word, $i, strlen($number));
                if($part === $number){
                    $digits[] = $index + 1;
                    break;
                }
            }
        }
        $i++;
    }
    return implode('', $digits);

}
function part2(array $lines) :int
{
    $lines_parsed = [];
    foreach ($lines as $word) {
        $word_parsed = extractDigits($word);
        $lines_parsed[] = $word_parsed;
    }

    return part1($lines_parsed);
}

display('Part 1: ' . part1($lines));
display('Part 2: ' . part2($lines));
