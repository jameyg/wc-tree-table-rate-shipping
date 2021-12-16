<?php
namespace Trs\Mapping\Interfaces;


interface IMapper
{
    /**
     * @param mixed $data
     * @param IReader $reader
     * @param IMappingContext $context
     * @return mixed
     */
    function read($data, IReader $reader, IMappingContext $context = null);
}