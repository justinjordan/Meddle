<?php

namespace Sxule\Meddle;

class Parser
{
    /**
     * Adds $ to variables
     *
     * @param string $input Meddle statement
     *
     * @return string PHP statement
     */
    public static function decorateVariables(string $input)
    {
        $inQuotes = false;
        $escaped = false;
        $closer = null;

        $variableIndex = null;
        $foundVariables = [];

        /** find variables */
        for ($index = 0, $len = strlen($input); $index < $len; $index++) {
            $letter = $input[$index];

            switch ($letter) {
                case '"':
                case "'":
                    if (!$inQuotes) {
                        /** start quote block */
                        $inQuotes = true;
                        $closer = $letter;
                    } elseif ($inQuotes && $letter === $closer && !$escaped) {
                        /** end quote block */
                        $inQuotes = false;
                        $closer = null;
                    }
                    break;
                
                default:
                    /**
                     * part of variable name
                     */
                    if (preg_match('/[a-z0-9_]/i', $letter) && !$inQuotes && $variableIndex === null) {
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
            $escaped = $letter === '\\';
        }

        /** add $ to variables */
        $skip = ['true', 'false', 'null', 'as'];
        $output = preg_replace_callback('/\w+/', function ($m) use ($skip, $foundVariables) {
            $op = $m[0];
            if (!in_array($op, $skip) && in_array($op, $foundVariables)) {
                $op = '$' . $op;
            }
            return $op;
        }, $input);

        return $output;
    }
}
