<?php
call_user_func(function() {

    require_once(__DIR__.'/src/PluginMeta.php');
    require_once(__DIR__.'/src/ClassLoader.php');
    require_once(__DIR__.'/src/Loader.php');

    $pluginMeta = new \Trs\PluginMeta(TRS_ENTRY_FILE);

    $classLoader = new \Trs\ClassLoader();
    $classLoader->setup($pluginMeta);

    $loader = new \Trs\Loader($pluginMeta);
    $loader->bootstrap();
});