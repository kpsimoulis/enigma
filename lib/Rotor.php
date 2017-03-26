<?php

namespace Enigma;

/**
 * Enigma Engine Rotor
 * This rotor contains 26 unique alphabet letters in a random order
 *
 * @package     Enigma
 * @author      Konstantinos Psimoulis <kosta@noima.com>
 */
class Rotor
{
    /**
     * @var string
     */
    public $key = "";

    /**
     * @var string
     */
    private $original = "";

    /**
     * @var int
     */
    private $rotation = 0;

    /**
     * Rotor constructor.
     *
     * @param string $key
     * @throws \InvalidArgumentException If key is not 26 characters long
     */
    public function __construct($key)
    {
        $this->key = $key;
        $this->original = $key;
        if (strlen($this->key) != 26) {
            throw new \InvalidArgumentException('Rotor keys must have 26 characters');
        }
    }

    /**
     * Add a missing key
     *
     * @param string $char
     * @param int $pos
     */
    public function add($char, $pos)
    {
        $initial_pos = $this->getOriginalPosition($pos);
        if (strpos($this->key, $char) === false) {
            $this->key[$initial_pos] = $char;
        }
    }

    /**
     * Given the current position and return the original position
     *
     * @param $pos
     * @return int
     */
    public function getOriginalPosition($pos)
    {
        return ($pos + $this->rotation) % 26;
    }

    /**
     * Get current rotated key
     *
     * @return string
     */
    public function getCurrentKey()
    {
        $pos = $this->rotation % 26;
        return substr($this->key, $pos) . substr($this->key, 0, $pos);
    }

    /**
     * Reset the Rotor back to original position
     */
    public function reset()
    {
        $this->rotation = 0;
    }

    /**
     * Reset the key back to the original key
     */
    public function resetKey()
    {
        $this->key = $this->original;
    }

    /**
     * Perform rotation
     */
    public function rotate()
    {
        $this->rotation++;
    }

    /**
     * Check if the rotor is incomplete
     *
     * @return bool
     */
    public function isIncomplete()
    {
        return (strpos($this->key, '?') !== false);
    }


}