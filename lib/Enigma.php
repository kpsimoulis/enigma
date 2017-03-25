<?php

namespace Enigma;

/**
 * Enigma Solver
 * This is a Helper Class to use the Enigma Engine
 * It also evaluates if the challange can be solved
 *
 * @package     Enigma
 * @author      Konstantinos Psimoulis <kosta@noima.com>
 */
class Enigma
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * @var \Enigma\Engine
     */
    public $engine;

    /**
     * Enigma constructor.
     * @param array $config
     * @throws \Exception If challenge is not supported
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->engine = new Engine($this->config['r1'], $this->config['r2'], $this->config['r3'], $this->config['reflector']);

        if ($this->engine->isIncomplete() === TRUE) {
            $this->tryChallenge();
        }
    }

    /**
     * Check if Challenge can be solved
     *
     * @throws \Exception If challenge is not supported
     */
    private function tryChallenge() {

        if (!isset($this->config['sentence']) || strlen($this->config['sentence']) < 10) {
            throw new \Exception("Engine is incomplete, you will need a sentence of at least 10 characters to try a challenge");
        }
        else {
            $this->addSentence($this->config['sentence']);
        }

        // Challenge #2, R1 is incomplete
        if ($this->engine->r1CanBeDecoded()) {
            $this->engine->decryptRotorByName('R1');
        }
        // Challenge #3, R2 is incomplete
        else if ($this->engine->r2CanBeDecoded()) {
            $this->engine->decryptRotorByName('R2');
        }
        else {
            throw new \Exception("Only Rotor1 or Rotor2 can be incomplete but not both");
        }

    }

    /**
     * Add a known sentence that can help us with decryption
     *
     * @param string $str
     */
    public function addSentence($str) {
        $sentence = $this->encryptSpaces($str);
        $this->engine->setSentence($sentence);
        $messageLength = strlen($sentence);

        $msg = @file_get_contents($this->config['inputFile'], NULL, NULL, 0, $messageLength);
        if ($msg === FALSE) {
            throw new Exception("Cannot access '" . $this->config['inputFile'] . "' to read contents.");
        }
        else {
            $this->engine->setMsg(($msg));
        }
    }

    /**
     * It converts spaces to QQ
     *
     * @param string $str
     * @return string
     */
    public function encryptSpaces($str) {
        return str_replace(' ', 'QQ', $str);
    }

    /**
     * It converts QQ back to spaces
     *
     * @param string $str
     * @return string
     */
    public function decryptSpaces($str) {
        return str_replace('QQ', ' ', $str);
    }

    /**
     * Wrapper for Engine cipher
     *
     * @param string $char
     * @return string
     */
    public function cipher($char) {
        return $this->engine->cipher($char);
    }

    /**
     * Wrapper for Engine rotate
     *
     * @param int $counter
     */
    public function rotate($counter) {
        $this->engine->rotate($counter);
    }


}