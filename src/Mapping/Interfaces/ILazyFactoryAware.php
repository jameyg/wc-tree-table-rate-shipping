<?php
namespace Trs\Mapping\Interfaces;


interface ILazyFactoryAware
{
    /**
     * @param ILazyFactory $lazyFactory
     */
    function setLazyFactory(ILazyFactory $lazyFactory);
}