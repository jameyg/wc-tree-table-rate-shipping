<?php
namespace Trs\Factory\Interfaces;

use Closure;
use LogicException;


interface IRegistry
{
    /**
     * @param string @id
     * @return object|null
     */
    function get($id);

    /**
     * @param string $id
     * @param ISingleFactory|Closure $factory
     * @throws LogicException If object with the same id is already registered
     */
    function register($id, $factory);

    /**
     * @param array $factories
     * @throws LogicException If object with the same id is already registered
     */
    function registerMany(array $factories);

    /**
     * @param callable $callback
     * @return ISingleFactory
     */
    function share($callback);

    /**
     * @param callable $callback
     * @return ISingleFactory
     */
    function create($callback);

    /**
     * @param mixed $value
     * @return ISingleFactory
     */
    function asis($value);
}