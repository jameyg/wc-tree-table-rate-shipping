<?php
namespace Trs\Mapping\Lazy;

use Trs\Mapping\Interfaces\ILazyFactory;
use Trs\Mapping\Lazy\Wrappers\LazyArray;
use Trs\Mapping\Lazy\Wrappers\LazyCalculator;
use Trs\Mapping\Lazy\Wrappers\LazyMatcher;


class LazyFactory implements ILazyFactory
{
    public function lazyArray($loader, $count = null, $readonly = false)
    {
        return new LazyArray($loader, $count, $readonly);
    }

    public function lazyCalculator($loader)
    {
        return new LazyCalculator($loader);
    }

    public function lazyMatcher($loader)
    {
        return new LazyMatcher($loader);
    }
}