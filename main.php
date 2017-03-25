<?php

/**
 * Default Challenge
 */
define("DEFAULT_CHALLENGE", 3);

/**
 * Require Libraries
 */
require "lib/Rotor.php";
require "lib/Engine.php";
require "lib/Enigma.php";

/**
 * Use namespace Enigma
 */
use Enigma\Enigma;

/**
 * Determine Challange from the first command line argument
 * Options are: 1, 2 or 3
 */
$challenge = constant("DEFAULT_CHALLENGE");
if (!empty($argv[1])) {
    if ($argv[1] == 1 || $argv[1] == 2 || $argv[1] == 3) {
        $challenge = $argv[1];
    }
    else {
        echo "Usage: php " . basename(__FILE__) . " [challenge]\n";
        echo "[challenge] can be 1, 2 or 3\n";
        exit();
    }
}

/**
 * Load config file
 */
require("c" . $challenge . "/config.php");

/**
 * Initialize Enigma Solver
 */
try {
    $enigma = new Enigma($config);
} catch (\Exception $e) {
    echo 'Caught exception: '.  $e->getMessage(). PHP_EOL;
    exit();
}

/**
 * Input file loaded from config file
 */
$file = file_get_contents($config['inputFile']);

/**
 * Initialize some variables
 */
$strlen = strlen( $file ) - 1;
$output = "";
$pos = 0;

/**
 * Loop and decrypt every character in file
 */
for ($i = 0; $i <= $strlen; $i++) {
    $char = $file[$i];
    if ($char == "\n") {
        $result = $char;
    }
    else {
        $result = $enigma->cipher($char);
        $enigma->rotate($pos);
        $pos++;
    }
    $output .= $result;
}

/**
 * Save result to output file defined in config
 */
file_put_contents($config['outputFile'], $enigma->decryptSpaces($output));
echo "Writing result to output file " . $config['outputFile'] . PHP_EOL;

/**
 * Show the time it took for the challange to be resolved
 */
$time = round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 3);
echo "Challenge $challenge completed in $time seconds" .PHP_EOL;