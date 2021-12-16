<?php
namespace Trs\Factory\Interfaces;


interface ISingleFactory
{
    /**
     * @return mixed
     */
    function __invoke();
}