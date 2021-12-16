<?php
namespace TrsVendors\Dgm\Shengine\Conditions\Common\Enum;


class EqualEnumCondition extends \TrsVendors\Dgm\Shengine\Conditions\Common\Enum\AbstractEnumCondition
{
    public function isSatisfiedBy($value)
    {
        $value = $this->normalize($value);
        $other = $this->normalize($this->other);
        
        if (count($value) != count($other)) {
            return false;
        }
        
        sort($value);
        sort($other);
        if ($value != $other) {
            return false;
        }
        
        return true;
    }
}