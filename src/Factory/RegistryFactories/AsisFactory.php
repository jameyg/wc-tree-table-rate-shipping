<?php
namespace Trs\Factory\RegistryFactories;

use Trs\Factory\Interfaces\ISingleFactory;


class AsisFactory implements ISingleFactory
{
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __invoke()
    {
        return $this->value;
    }

    private $value;
}