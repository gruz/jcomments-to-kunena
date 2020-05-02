<?php

namespace gruz\JCommentsToKunenaCli;

trait CliOutput
{
    private $foregroundColors = array();

    private $backgroundColors = array();

    public function __initCLIOutput()
    {
        // Set up shell colors
        $this->foregroundColors['black'] = '0;30';
        $this->foregroundColors['dark_gray'] = '1;30';
        $this->foregroundColors['blue'] = '0;34';
        $this->foregroundColors['light_blue'] = '1;34';
        $this->foregroundColors['green'] = '0;32';
        $this->foregroundColors['light_green'] = '1;32';
        $this->foregroundColors['cyan'] = '0;36';
        $this->foregroundColors['light_cyan'] = '1;36';
        $this->foregroundColors['red'] = '0;31';
        $this->foregroundColors['light_red'] = '1;31';
        $this->foregroundColors['purple'] = '0;35';
        $this->foregroundColors['light_purple'] = '1;35';
        $this->foregroundColors['brown'] = '0;33';
        $this->foregroundColors['yellow'] = '1;33';
        $this->foregroundColors['light_gray'] = '0;37';
        $this->foregroundColors['white'] = '1;37';

        $this->backgroundColors['black'] = '40';
        $this->backgroundColors['red'] = '41';
        $this->backgroundColors['green'] = '42';
        $this->backgroundColors['yellow'] = '43';
        $this->backgroundColors['blue'] = '44';
        $this->backgroundColors['magenta'] = '45';
        $this->backgroundColors['cyan'] = '46';
        $this->backgroundColors['light_gray'] = '47';
    }

    // Returns colored string
    public function getColoredString($string, $foregroundColor = null, $backgroundColor = null) // phpcs:ignore
    {
        $coloredString = "";

        // Check if given foreground color found
        if (isset($this->foregroundColors[$foregroundColor])) {
            $coloredString .= "\033[" . $this->foregroundColors[$foregroundColor] . "m";
        }

        // Check if given background color found
        if (isset($this->backgroundColors[$backgroundColor])) {
            $coloredString .= "\033[" . $this->backgroundColors[$backgroundColor] . "m";
        }

        // Add string and end coloring
        $coloredString .= $string . "\033[0m";

        return $coloredString;
    }

    // Returns all foreground color names
    public function getForegroundColors() // phpcs:ignore
    {
        return array_keys($this->foregroundColors);
    }

    // Returns all background color names
    public function getBackgroundColors() // phpcs:ignore
    {
        return array_keys($this->backgroundColors);
    }

    public function out($str = '', $br = true, $n = 0) // phpcs:ignore
    {
        if (null === $br) {
            $br = true;
        }

        if ($n > 0) {
            $str = str_repeat(' ', $n) . $str . str_repeat(' ', $n);
        }

        preg_match_all('/\[(.*)\](.*)\[\/.*\]/Ui', $str, $matches);

        foreach ($matches[0] as $k => $value) {
            $colors = $matches[1][ $k];
            $colors = explode('|', $colors);
            $f_color = $colors[0];
            $b_color = isset($colors[1]) ? $colors[1] : null;

            $colored = $this->getColoredString($matches[2][$k], $f_color, $b_color);

            $str = str_replace($matches[0][$k], $colored, $str);
        }

        parent::out($str, $br);
    }
}
