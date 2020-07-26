<?php

namespace Raketman\DatabasePartitionProcessor\Parser;

use Raketman\DatabasePartitionProcessor\Annotation\RaketmanDatePartition;

class Locator
{
    /**
     * @param $dir
     * return []
     */
    public function locate($dir)
    {
        $files = [];

        // Проверим наличие аннотация в директориях каталога с классами
        $directoryIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        while ($directoryIterator->valid()) {
            $currentFilePath = $directoryIterator->key();
            // Нас интересуют только файлы
            if (is_file($currentFilePath)) {

                if (false !== strpos(file_get_contents($currentFilePath), RaketmanDatePartition::class)) {
                    $files[] = $currentFilePath;
                }
            }

            $directoryIterator->next();
        }

        return $files;
    }

    public function download($path)
    {

    }
}