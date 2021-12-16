<?php
namespace Trs\Woocommerce\Model\Shipping;

use InvalidArgumentException;
use TrsVendors\Dgm\SimpleProperties\SimpleProperties;
use Trs\Woocommerce\Model\Shipping\Exceptions\MalformedPersistentId;


/**
 * @property-read bool $global
 * @property-read string $id
 */
class ShippingMethodPersistentId extends SimpleProperties
{
    public function __construct($global, $id)
    {
        $global = (bool)$global;

        $id = (string)$id;
        if (!$id) {
            throw new InvalidArgumentException('Method id must not be empty.');
        }

        $this->global = $global;
        $this->id = $id;
    }

    public function serialize()
    {
        return ($this->global ? 'g' : 'i') . '/' . $this->id;
    }

    /**
     * @throws MalformedPersistentId
     */
    public static function unserialize($string)
    {
        if (!is_string($string)) {
            throw new InvalidArgumentException();
        }

        $global = null; {
            $start = substr($string, 0, 2);
            if ($start === 'g/') {
                $global = true;
            } elseif ($start === 'i/') {
                $global = false;
            } else {
                self::malformed($string);
            }
        }

        $id = substr($string, 2);
        if ($id === '') {
            self::malformed($string);
        }

        return new self($global, $id);
    }

    public function __toString()
    {
        return $this->serialize();
    }

    protected $global;
    protected $id;

    static private function malformed($string)
    {
        throw new MalformedPersistentId("Couldn't parse a malformed persistent shipping method id '{$string}'");
    }
}