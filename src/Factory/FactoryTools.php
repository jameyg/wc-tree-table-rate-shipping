<?php
namespace Trs\Factory;

use TrsVendors\Dgm\Arrays\Arrays;


class FactoryTools
{
    public static function resolveObjectIdToClass($objectId, $classSuffix, $exampleClass)
    {
        $class = self::getClassNamespace($exampleClass).'\\'.self::underscore2camelcase($objectId).$classSuffix;
        return class_exists($class) ? $class : null;
    }

    public static function getClassNamespace($class)
    {
        return substr($class, 0, (int)strrpos($class, '\\')) ?: null;
    }

    private static function underscore2camelcase($string)
    {
        return join('', Arrays::map(explode('_', $string), 'ucfirst'));
    }
}