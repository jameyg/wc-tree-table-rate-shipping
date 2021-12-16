<?php
namespace TrsVendors\Dgm\Shengine\Migrations\Interfaces\Migrations;


interface IRuleMigration
{
    /**
     * @param mixed $rule
     * @return mixed
     */
    function migrateRule($rule);
}