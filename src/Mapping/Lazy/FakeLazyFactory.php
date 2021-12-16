<?php
namespace Trs\Mapping\Lazy;

use Trs\Mapping\Interfaces\ILazyFactory;


class FakeLazyFactory implements ILazyFactory
{
    public function lazyArray($loader, $count = null, $readonly = false)
    {
        return $loader();
    }

    public function lazyCalculator($loader)
    {
        return $loader();
    }

    public function lazyMatcher($loader)
    {
        return $loader();
    }
}