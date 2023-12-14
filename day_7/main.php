<?php

$debug = array_key_exists(1, $argv) && $argv[1] === 'debug';

$file_content = file_get_contents(__DIR__ . '/input.txt');
$lines = explode(PHP_EOL, $file_content);

if ($debug) {
    $lines = [
        '32T3K 765',
        'T55J5 684',
        'KK677 28',
        'KTJJT 220',
        'QQQJA 483',
    ];
}

function display(string $result): void
{
    echo $result . PHP_EOL;
}

const CARDS_ORDER = '23456789TJQKA';
const CARDS_ORDER_PART2 = 'J23456789TQKA';

function parseLines(array $lines): array
{
    $hands = [];
    foreach ($lines as $line) {
        if ($line === '') {
            continue;
        }
        $line = explode(' ', $line);
        $hands[] = [
            'cards' => array_shift($line),
            'points' => intval(implode('', $line)),
        ];
    }
    return $hands;
}

function resolve(array $lines, callable $getHandValue, string $card_order): int
{
    $result = 0;

    $hands = parseLines($lines);
    $ranked_hands = [];
    foreach ($hands as $hand) {
        $cards = $hand['cards'];
        $value = $getHandValue($cards);
        $ranked_hands[$value][] = [
            'cards' => $cards,
            'points' => $hand['points'],
            'cards_values' => implode('', array_map(function ($card) use ($card_order) {
                return str_pad(strpos($card_order, $card), 2, '0', STR_PAD_LEFT);
            }, str_split($cards))),
        ];
    }
    $rank = 1;
    ksort($ranked_hands);
    foreach ($ranked_hands as &$hands) {
        $lastcard_value = null;
        usort($hands, function ($a, $b) {
            return $a['cards_values'] > $b['cards_values'];
        });
        foreach ($hands as $hand) {
            $result += $hand['points'] * $rank;
            if ($lastcard_value !== $hand['cards_values']) {
                $rank++;
                $lastcard_value = $hand['cards_values'];
            }
        }
    }
    return $result;
}
################################
########### PART 1 #############
################################



function getHandValue(string $hand): int
{
    $hand_by_cards = [];
    foreach (str_split($hand) as $card) {
        $hand_by_cards[$card] = ($hand_by_cards[$card] ?? 0) + 1;
    }

    $points = 0;

    // five of a kind
    if (in_array(5, $hand_by_cards)) {
        $points = 100000;
    }
    // four of a kind
    elseif (in_array(4, $hand_by_cards)) {
        $points = 10000;
    }
    // full house
    elseif (in_array(3, $hand_by_cards) && in_array(2, $hand_by_cards)) {
        $points = 1000;
    }
    // three of a kind
    elseif (in_array(3, $hand_by_cards)) {
        $points = 100;
    }
    // two pairs
    elseif (count(array_keys($hand_by_cards, 2)) === 2) {
        $points = 30;
    }
    // one pair
    elseif (in_array(2, $hand_by_cards)) {
        $points = 5;
    }
    return $points;
}

function part1(array $lines): int
{
    return resolve($lines, 'getHandValue', CARDS_ORDER);
}

################################
########### PART 2 #############
################################

function getAllJockerRemplacementPossibilities(string $hand, int $index = 0)
{
    if($index === strlen($hand)){
        return [$hand];
    }
    $possibilities = [];

    $card = $hand[$index];
    if($card === 'J'){
        foreach (str_split('23456789TQKA') as $card) {
            $new_hand = $hand;
            $new_hand[$index] = $card;
            $possibilities = array_merge($possibilities, getAllJockerRemplacementPossibilities($new_hand, $index + 1));
        }
    }else{
        $possibilities = array_merge($possibilities, getAllJockerRemplacementPossibilities($hand, $index + 1));;
    }

    return $possibilities;
}

function getHandValue_v2(string $hand): int
{
    $possibilities = getAllJockerRemplacementPossibilities($hand);
    return max(array_map('getHandValue', $possibilities));
}

function part2(array $lines): int
{
    return resolve($lines, 'getHandValue_v2', CARDS_ORDER_PART2);
}

display('Part 1: ' . part1($lines));
display('Part 2: ' . part2($lines));
