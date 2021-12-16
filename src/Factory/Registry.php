<?php
namespace Trs\Factory;

use Closure;
use LogicException;
use Trs\Factory\Interfaces\IRegistry;
use Trs\Factory\Interfaces\ISingleFactory;
use Trs\Factory\RegistryFactories\AsisFactory;
use Trs\Factory\RegistryFactories\CreateFactory;
use Trs\Factory\RegistryFactories\ShareFactory;


class Registry implements IRegistry
{
    public function __construct()
    {
        $this->init();
    }

    public function get($id)
    {
        return isset($this->factories[$id]) ? call_user_func($this->factories[$id]) : null;
    }

    public function register($id, $factory)
    {
        if ($this->registered($id)) {
            throw new LogicException("Object with id '{$id}' is already registered");
        }

        if ($factory instanceof Closure) {
            $factory = $this->share($factory);
        } else if (!$factory instanceof ISingleFactory) {
            $factory = $this->asis($factory);
        }

        $this->set($id, $factory);
    }

    public function registerMany(array $factories)
    {
        foreach ($factories as $id => $factory) {
            $this->register($id, $factory);
        }
    }

    public function share($callback)
    {
        return new ShareFactory($callback);
    }

    public function create($callback)
    {
        return new CreateFactory($callback);
    }

    public function asis($value)
    {
        return new AsisFactory($value);
    }

    protected function init()
    {
    }

    protected function set($id, ISingleFactory $factory)
    {
        $this->factories[$id] = $factory;
    }

    protected function registered($id)
    {
        return isset($this->factories[$id]);
    }

    /** @var callable[] */
    private $factories = array();
}