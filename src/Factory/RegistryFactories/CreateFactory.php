<?php
namespace Trs\Factory\RegistryFactories;

use Trs\Factory\Interfaces\ISingleFactory;


class CreateFactory implements ISingleFactory
{
    public function __construct($factory)
    {
        $this->factory = $factory;
    }

    public function __invoke()
    {
        $factory = $this->factory;
        return $factory();
    }

    private $factory;
}