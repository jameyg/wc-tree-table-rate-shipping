<?php
namespace Trs\Mapping\Lazy\Wrappers;


abstract class AbstractLazyWrapper
{
    public function __construct($loader)
    {
        $this->loader = $loader;
    }

    protected function load()
    {
        if (!isset($this->wrappee)) {
            $load = $this->loader;
            $this->wrappee = $load();
        }

        return $this->wrappee;
    }

    private $loader;
    private $wrappee;
}