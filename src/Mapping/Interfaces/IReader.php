<?php
namespace Trs\Mapping\Interfaces;




interface IReader
{
    /**
     * @param string $objectType
     * @param mixed $data
     * @param IMappingContext $context
     * @return mixed
     */
    function read($objectType, $data, IMappingContext $context = null);
}