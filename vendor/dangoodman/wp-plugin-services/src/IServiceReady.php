<?php
namespace TrsVendors\Dgm\PluginServices;


interface IServiceReady
{
    /**
     * Check whether the service is able or wants to be run.
     *
     * @return bool
     */
    function ready();
}