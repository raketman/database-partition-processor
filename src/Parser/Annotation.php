<?php

namespace Raketman\DatabasePartitionProcessor\Parser;


use Raketman\DatabasePartitionProcessor\Annotation\RaketmanDatePartition;

class Annotation
{
    /**
     * @param $file
     * @return RaketmanDatePartition
     * @throws \ReflectionException|\Exception
     */
    public function parse($file)
    {
        // подключим файл, найдем последний загруженный класс
        require_once $file;

        $clases = get_declared_classes();
        $class = array_pop($clases);

        $reflection = new \ReflectionClass($class);

        $phpDoc =  $reflection->getDocComment();

        $pos = 0;
        $annotationName = 'RaketmanDatePartition';

        while($annotationPos =  strpos($phpDoc, $annotationName, $pos)) {
            if (substr($phpDoc, $annotationPos-1, 1) !== '"') {
                break;
            }

            $pos= $annotationPos + strlen($annotationName);
        }

        $annotationPosEnd = strpos($phpDoc, ')', $annotationPos) ;
        if (false == $annotationPosEnd) {
            throw new \Exception("Не удалось определить {$annotationName}, нет закрывающего элемента");
        }


        $annotationPhpDoc = substr($phpDoc, $annotationPos, $annotationPosEnd- $annotationPos + 1);



        $annotationObject = new RaketmanDatePartition();

        $annotationReflection = new \ReflectionClass(RaketmanDatePartition::class);
        foreach ($annotationReflection->getProperties() as $property) {
            $propertyPos = strpos($annotationPhpDoc, $property->getName());

            if (false === $propertyPos && is_null($property->getValue($annotationObject))) {
                throw new \Exception("Не удалось найти обязательное свойство {$property->getName()}");
            }

            $endPropertyPos = strpos($annotationPhpDoc,",", $propertyPos);

            if (false == $endPropertyPos) {
                $endPropertyPos = strpos($annotationPhpDoc,")", $propertyPos);
            }

            if (false == $endPropertyPos) {
                throw new \Exception("Не удалось определить свойство {$property->getName()}, нет закрывающего элемента");
            }

            $propertyPhpDoc = substr($annotationPhpDoc, $propertyPos, $endPropertyPos - $propertyPos);

            $parts = explode("=", $propertyPhpDoc);

            if (count($parts) !== 2)  {
                throw new \Exception("Не удалось найти значение свойства {$property->getName()}");
            }


            $value = str_replace(['"', "\r", " ", "\s", "\r\n", "*", "\n"], "", $parts[1]);
            $annotationObject->{$property->getName()} = $value;
        }

       return $annotationObject;
    }
}