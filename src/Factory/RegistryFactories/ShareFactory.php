<?php
namespace Trs\Factory\RegistryFactories;

use Trs\Factory\Interfaces\ISingleFactory;


class ShareFactory implements ISingleFactory
{
    public function __construct($factory)
    {
        $this->factory = $factory;
    }

    public function __invoke()
    {
        if (!isset($this->object)) {
            $factory = $this->factory;
            $this->object = $factory();
            unset($this->factory);
        }

        return $this->object;
    }

    private $factory;
    private $object;
}