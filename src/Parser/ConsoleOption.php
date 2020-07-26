<?php

namespace Raketman\DatabasePartitionProcessor\Parser;


class ConsoleOption
{
    public function getList($argv, $options)
    {
        $result = array_combine(array_keys($options), array_map(function () {return null;}, $options));

        foreach ($argv as $argument) {
            foreach ($options as $var => $option) {
                if (0 !== strpos($argument, $option)) {
                    continue;
                }

                $result[$var] = str_replace($option . '=', '', $argument);
            }
        }

        return array_values($result);
    }
}