<?php

namespace Sxule\Meddle;

class Parser
{
    public static function parse(string $input) : string
    {
        $output = $input;

        $output = self::decorateVariables($output);
        $output = self::convertDotsToBrackets($output);

        return $output;
    }

    public static function convertDotsToBrackets(string $input) : string
    {
        $output = preg_replace_callback('/[\.][\$]([a-z_][a-z0-9_]*)/i', function ($m) {
            return "['$m[1]']";
        }, $input);

        return $output;
    }

    /**
     * Adds $ to variables
     *
     * @param string $input Meddle statement
     *
     * @return string PHP statement
     */
    public static function decorateVariables(string $input) : string
    {
        $inQuotes = false;
        $escaped = false;
        $closer = null;

        $variableIndex = null;
        $foundVariables = [];

        /** find variables by looping through characters */
        for ($index = 0, $len = strlen($input); $index < $len; $index++) {
            $character = $input[$index];

            switch ($character) {
                case '"':
                case "'":
                    if (!$inQuotes) {
                        /** start quote block */
                        $inQuotes = true;
                        $closer = $character;
                    } elseif ($inQuotes && $character === $closer && !$escaped) {
                        /** end quote block */
                        $inQuotes = false;
                        $closer = null;
                    }
                    break;
                
                default:
                    /**
                     * part of variable name
                     */
                    if (preg_match('/[a-z0-9_]/i', $character) && !$inQuotes && $variableIndex === null) {
                        $variableIndex = $index;
                    }

                    $nextIndex = $index + 1;
                    if ($variableIndex !== null && ($nextIndex >= $len || !preg_match('/[a-z0-9_]/i', $input[$nextIndex]))) {
                        /** get variable name */
                        $variable = substr($input, $variableIndex, $nextIndex - $variableIndex);

                        /** check variable name validity */
                        if (preg_match('/[a-z_][a-z0-9_]*/i', $variable)) {
                            $foundVariables[] = $variable;
                        }

                        /** reset index */
                        $variableIndex = null;
                    }
                    break;
            }

            /** escape next character */
            $escaped = $character === '\\';
        }

        /** add $ to variables */
        $skip = ['true', 'false', 'null', 'as'];
        $output = preg_replace_callback('/\w+/', function ($m) use ($skip, $foundVariables) {
            $output = $m[0];

            if (!in_array($output, $skip) && in_array($output, $foundVariables)) {
                $output = '$' . $output;
            }

            return $output;
        }, $input);

        return $output;
    }
}
