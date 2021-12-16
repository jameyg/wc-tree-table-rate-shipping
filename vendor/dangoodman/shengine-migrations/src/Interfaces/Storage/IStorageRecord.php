<?php
namespace TrsVendors\Dgm\Shengine\Migrations\Interfaces\Storage;


interface IStorageRecord
{
    /**
     * @return mixed
     */
    function get();

    /**
     * @param mixed $value
     */
    function set($value);
}