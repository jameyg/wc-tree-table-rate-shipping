<?php
namespace Trs\Migrations;

use TrsVendors\Dgm\Shengine\Migrations\Interfaces\Migrations\IRuleMigration;


class Migration_1_7_6 implements IRuleMigration
{
    /**
     * @param array $rule
     * @throws Migration_1_7_6\InvalidConditionException
     * @return array
     */
    public function migrateRule($rule)
    {
        self::modernizeTermConditions($rule);
        self::modernizeProgressiveRates($rule);
        return $rule;
    }

    /**
     * @throws Migration_1_7_6\InvalidConditionException
     */
    static public function modernizeTermConditions(array &$rule)
    {
        if (!isset($rule['conditions']['list'])) {
            return;
        }

        foreach ($rule['conditions']['list'] as &$condition) {
            try {
                $condition = self::modernizeTermCondition($condition);
            } catch (Migration_1_7_6\UnknownTaxonomyException $e) {
                // skip & move forward
            }
        }
    }

    static public function modernizeProgressiveRates(array &$rule)
    {
        if (empty($rule['operations']['list'])) {
            return;
        }

        $operations = array();
        foreach ($rule['operations']['list'] as $operation) {

            if (@$operation['operation'] === 'add' && isset($operation['calculator'])) {

                $calculator = &$operation['calculator'];

                if (in_array(@$calculator['calculator'], array('weight', 'count', 'volume'), true) && isset($calculator['steps'])) {

                    $calculator['cost'] = $calculator['steps']['next']['cost'];
                    $calculator['step'] = $calculator['steps']['next']['size'];

                    if (!empty($calculator['extended'])) {

                        $calculator['skip'] = $calculator['steps']['first']['size'];

                        $operations[] = array(
                            'operation' => 'add',
                            'calculator' => array(
                                'calculator' => 'const',
                                'value' => $calculator['steps']['first']['cost'],
                            ),
                        );
                    }

                    unset($calculator['extended']);
                    unset($calculator['steps']);
                }
            }

            $operations[] = $operation;
        }

        $rule['operations']['list'] = $operations;
    }

    /**
     * @throws Migration_1_7_6\UnknownTaxonomyException
     * @throws Migration_1_7_6\InvalidConditionException
     */
    static public function modernizeTermCondition(array $ruleConditionConfig)
    {
        static $taxonomies = array(
            'classes' => 'shipping_class',
            'tags' => 'tag',
            'categories' => 'category',
        );

        static $operators = array(
            'intersect' => 'any',
            'disjoint' => 'no',
            'superset' => 'all',
            'subset' => 'any&only',
            'equal' => 'all&only',
        );

        $taxonomy = @$taxonomies[$ruleConditionConfig['condition']];
        if (!isset($taxonomy)) {
            throw new Migration_1_7_6\UnknownTaxonomyException("Unknown taxonomy", $ruleConditionConfig);
        }

        $operator = @$operators[$ruleConditionConfig['operator']];
        if (!isset($operator)) {
            throw new Migration_1_7_6\InvalidConditionException("Unknown operator", $ruleConditionConfig);
        }


        $values = array_map(
            function ($term) use ($taxonomy) { return "{$taxonomy}:{$term}"; },
            (array)$ruleConditionConfig['value']
        );

        // 'Equal' used in older version produces false for empty values list, while newer 'all&only' produces true.
        // Change operator in order to keep previous config's behavior.
        if ($operator === 'all&only' && empty($values)) {
            $operator = 'any';
        }

        return array(
            'condition' => 'terms',
            'operator' => $operator,
            'value' => $values,
        );
    }
}


namespace Trs\Migrations\Migration_1_7_6;

use Exception;
use Trs\Migrations\Migration_1_7_6;


class InvalidConditionException extends Exception
{
    public function __construct($message, $config, Exception $previous = null)
    {
        $config = json_encode($config);
        $message = "Error reading condition config: {$message}. Condition: '{$config}'.";
        parent::__construct($message, 0, $previous);
    }
}

class UnknownTaxonomyException extends InvalidConditionException
{
}


return new Migration_1_7_6();