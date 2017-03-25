<?php

namespace Enigma;

/**
 * Enigma Engine
 * This engine contains 4 Rotors
 *
 * @package     Enigma
 * @author      Konstantinos Psimoulis <kosta@noima.com>
 */
class Engine
{
    /**
     * The English Alphabet
     */
    const ALPHABET = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

    /**
     * Position of Rotor 1 inside the engine
     */
    const R1_POS = 1;

    /**
     * Position of Rotor 2 inside the engine
     */
    const R2_POS = 2;

    /**
     * Position of Rotor 3 inside the engine
     */
    const R3_POS = 3;

    /**
     * Position of Reflector Rotor inside the engine
     */
    const REFLECTOR_POS = 4;

    /**
     * @var \Enigma\Rotor
     */
    private $r1;

    /**
     * @var \Enigma\Rotor
     */
    private $r2;

    /**
     * @var \Enigma\Rotor
     */
    private $r3;

    /**
     * @var \Enigma\Rotor
     */
    private $reflector;

    /**
     * @var string
     */
    private $sentence = "";

    /**
     * @var string
     */
    private $msg = "";

    /**
     * Cipher conversion data for debugging purposes
     *
     * @var array
     */
    public $cipherData = [];

    /**
     * Engine constructor.
     *
     * @param string $r1Key
     * @param string $r2Key
     * @param string $r3Key
     * @param string $reflectorKey
     */
    public function __construct($r1Key, $r2Key, $r3Key, $reflectorKey)
    {

        try {

            $this->r1 = new Rotor($r1Key);
            $this->r2 = new Rotor($r2Key);
            $this->r3 = new Rotor($r3Key);
            $this->reflector = new Rotor($reflectorKey);

        } catch (\Exception $e) {
            echo 'Engine initialization failed: ',  $e->getMessage(), "\n";
        }

    }

    /**
     * Reset every Rotor back to original position
     */
    private function resetRotors() {
        $this->r1->reset();
        $this->r2->reset();
        $this->r3->reset();
        $this->reflector->reset();
    }

    /**
     * Rotate Rotors according to the counter
     *
     * @param int $counter
     */
    public function rotate($counter) {
        $counter++;
        if ($counter % 3 == 0) {
            $this->r1->rotate();
            $this->r2->rotate();
            $this->r3->rotate();
        }
        else if ($counter %2 == 0) {
            $this->r1->rotate();
            $this->r2->rotate();
        }
        else {
            $this->r1->rotate();
        }
    }


    /**
     * Cipher Character
     *
     * Alphabet: 0
     * R1: 1
     * R2: 2
     * R3: 3
     * REFLECTOR: 4
     * RETURN R3: 5
     * RETURN R2: 6
     * RETURN R1: 7
     *
     * @param $char
     * @param int $end
     * @param int $start
     * @return string
     */
    public function cipher($char, $end = 7, $start = 0) {

        if ($start == 0) {
            $this->cipherData = array();
            $this->cipherData[0] = $char;
        }

        if ($start == $end) {
            return $this->cipherData[$start];
        }
        else {
            $this->cipherData[$start + 1] = $this->getNext($char, $start);
            return $this->cipher($this->cipherData[$start+1], $end, $start+1);
        }

    }

    /**
     * Get Next Character according to Rotor position
     *
     * @param $char
     * @param int $rotorPos
     * @return string
     * @throws \Exception
     */
    private function getNext($char, $rotorPos = 0) {

        if ($rotorPos == 0 || $rotorPos == 6) {
            $rotor = $this->r1;
        }
        else if ($rotorPos == 1 || $rotorPos == 5) {
            $rotor = $this->r2;
        }
        else if ($rotorPos == 2 || $rotorPos == 4) {
            $rotor = $this->r3;
        }
        else if ($rotorPos == 3) {
            $rotor = $this->reflector;
        }
        else {
            throw new \InvalidArgumentException("Invalid Rotor $rotorPos");
        }

        if ($rotorPos > 3) {
            $from = $rotor->getCurrentKey();
            $to = self::ALPHABET;
        }
        else {
            $from = self::ALPHABET;
            $to = $rotor->getCurrentKey();
        }

        $pos = strpos($from, $char);

        if ($pos === false) {
            throw new \Exception("Failed to decrypt $char");
        }
        else {
            return $to[$pos];
        }

    }


    /**
     * Decrypt Rotor by Name
     *
     * @param string $rotorName
     */
    public function decryptRotorByName($rotorName) {

        $rotor = $this->getRotorByName($rotorName);

        if ($rotor->key == '??????????????????????????') {
            $this->decryptRotorByGuessingFirstLetter($rotorName);
        }

        else {

            while ($rotor->isIncomplete() !== false) {
                $this->decryptRotorPartiallyIncomplete($rotorName);
                $this->resetRotors();
            }

            echo "$rotorName decrypted successfully: " .$rotor->key. "\n";

        }
    }

    /**
     * Decrypt a Rotor key with at least one known letter
     * It returns a string with all characters that were added
     *
     * @param string $rotorName
     * @return string
     */
    public function decryptRotorPartiallyIncomplete($rotorName) {

        $rotor = $this->getRotorByName($rotorName);
        $rotorPos = constant('self::'.$rotorName.'_POS');
        $missingPos = (7 - $rotorPos);
        $addPos = ($rotorPos - 1);
        $messageLength = strlen($this->sentence);
        $result = '';

        for ($i = 0; $i < $messageLength; $i++) {

            if ($rotor->isIncomplete() === false) {
                break;
            }

            $msg = $this->cipher($this->msg[$i], $rotorPos);
            $sentence = $this->cipher($this->sentence[$i], $rotorPos);

            if ($msg == '?' && $sentence != '?') {
                $missing = $this->cipher($this->sentence[$i], $missingPos);
                $pos = strpos(self::ALPHABET, $this->cipher($this->msg[$i], $addPos));
                $rotor->add($missing, $pos);
                $result .= $missing;

            }
            else if ($sentence == '?' && $msg != '?') {
                $missing = $this->cipher($this->msg[$i], $missingPos);
                $pos = strpos(self::ALPHABET, $this->cipher($this->sentence[$i], $addPos));
                $rotor->add($missing, $pos);
                $result .= $missing;
            }
            $this->rotate($i);
        }

        return $result;

    }

    /**
     * Decrypt a Rotor that all the keys are unknown by guessing the first letter
     *
     * @param string $rotorName
     * @throws \Exception
     */
    private function decryptRotorByGuessingFirstLetter($rotorName) {

        $rotor = $this->getRotorByName($rotorName);
        $popular = $this->getMostPopularRotorLetter($rotorName);
        $popularPos = null;
        $previous = '';

        for ($i = 0; $i < 26; $i++) {
            $rotor->add($popular, $i);
            while ($rotor->isIncomplete() !== false) {
                $result = $this->decryptRotorPartiallyIncomplete($rotorName);
                $this->resetRotors();
                if ($result == $previous) {
                    $rotor->resetKey();
                    break;
                }
                $previous = $result;
            }
            if ($rotor->isIncomplete() === false) {
                $popularPos = $i;
                break;
            }
        }

        if ($rotor->isIncomplete() === false) {
            echo "$rotorName decrypted successfully: " .$rotor->key. " by guessing most popular letter $popular in position $popularPos\n";
        }
        else {
            throw new \Exception("Failed to decrypt Rotor $rotorName");
        }


    }

    /**
     * Get the most common letter in order to help with decryption
     *
     * @param string $rotorName
     * @return string
     */
    public function getMostPopularRotorLetter($rotorName) {
        $rotorPos = (constant('self::'.$rotorName.'_POS') - 1);
        $result = array();
        $messageLength = strlen($this->sentence);

        for ($i = 0; $i < $messageLength; $i++) {

            $msg = $this->cipher($this->msg[$i], $rotorPos);
            $sentence = $this->cipher($this->sentence[$i], $rotorPos);


            if (isset($result[$msg])) {
                $result[$msg]++;
            }
            else {
                $result[$msg] = 1;
            }
            if (isset($result[$sentence])) {
                $result[$sentence]++;
            }
            else {
                $result[$sentence] = 1;
            }
            $this->rotate($i);
        }
        arsort($result);
        $this->resetRotors();
        return array_keys($result)[0];
    }

    /**
     * Set a known sentence that can help us with decryption
     *
     * @param string $sentence
     */
    public function setSentence($sentence)
    {
        $this->sentence = $sentence;
    }

    /**
     * The equivalent encrypted message of the sentence above
     *
     * @param string $msg
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;
    }

    /**
     * Check if engine has any incomplete Rotors
     *
     * @return bool
     */
    public function isIncomplete() {
        return ($this->r1->isIncomplete() || $this->r2->isIncomplete() || $this->r3->isIncomplete() || $this->reflector->isIncomplete());
    }

    /**
     * Check if Rotor 1 can be decoded
     *
     * @return bool
     */
    public function r1CanBeDecoded() {
        return ($this->r1->isIncomplete() === TRUE && $this->r2->isIncomplete() == FALSE && $this->r3->isIncomplete() == FALSE && $this->reflector->isIncomplete() == FALSE);
    }

    /**
     * Check if Rotor 2 can be decoded
     *
     * @return bool
     */
    public function r2CanBeDecoded() {
        return ($this->r1->isIncomplete() === FALSE && $this->r2->isIncomplete() == TRUE && $this->r3->isIncomplete() == FALSE && $this->reflector->isIncomplete() == FALSE);
    }

    /**
     * Get Rotor by name
     *
     * @param string $rotorName
     * @return \Enigma\Rotor
     * @throws \Exception
     */
    private function getRotorByName($rotorName) {

        $rotorName = strtoupper($rotorName);

        if ($rotorName == 'R1') {
            return $this->r1;
        }
        else if ($rotorName == 'R2') {
            return $this->r2;
        }
        else if ($rotorName == 'R3') {
            return $this->r3;
        }
        else if ($rotorName == 'REFLECTOR') {
            return $this->reflector;
        }
        else {
            throw new \Exception("Rotor $rotorName not supported");
        }

    }

    /**
     * Helper function to show current Rotors
     */
    public function show() {
        $this->debug(self::ALPHABET);
        $this->debug($this->r1->getCurrentKey());
        $this->debug($this->r2->getCurrentKey());
        $this->debug($this->r3->getCurrentKey());
        $this->debug($this->reflector->getCurrentKey());
    }

    /**
     * Helper function that prints debugging information
     *
     * @param $msg
     */
    private function debug($msg) {
        echo "$msg\n";
    }


}