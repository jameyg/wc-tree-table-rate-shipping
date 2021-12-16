<?php
namespace Trs\Migrations;

use TrsVendors\Dgm\Shengine\Migrations\AbstractConfigStorage;


class ConfigStorage extends AbstractConfigStorage
{
    public function forEachRule($fromConfig, $callback)
    {
        $fromConfig['rule'] = self::visitRulesRecursively($fromConfig['rule'], $callback);
        return $fromConfig;
    }


    protected function read($key)
    {
        $config = parent::read($key);
        $config['rule'] = json_decode($config['rule'], true);
        return $config;
    }

    protected function write($key, $config)
    {
        $config['rule'] = json_encode($config['rule']);
        parent::write($key, $config);
    }


    static private function visitRulesRecursively(array $rule, $ruleFilter)
    {
        $rule = $ruleFilter($rule);

        if (!empty($rule['children'])) {
            foreach ($rule['children'] as &$child) {
                $child = self::visitRulesRecursively($child, $ruleFilter);
            }
        }

        return $rule;
    }
}