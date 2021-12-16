<?php
namespace Trs;


class Log
{
    /**
     * @param string $msg
     * @param array $context
     */
    public static function error($msg, array $context = [])
    {
        $contextStr = $context ? ' ' . self::stringify($context) : '';

        $logger = self::$logger;
        $logger("TRS: {$msg}{$contextStr}");
    }

    public static $logger = 'error_log';

    private static function stringify($value, $level = 0)
    {
        if ($level > 6) {
            /** @lang text */
            return '<max nesting level reached>';
        }

        switch (gettype($value)) {
            case 'NULL':
                return 'null';
            case 'boolean':
                return $value ? 'true' : 'false';
            case 'integer':
            case 'double':
                return (string)$value;
            case 'string':
                return "'" . self::removeNewlines($value) . "'";
            case 'resource':
            case 'resource (closed)':
                return '<resource>';
            case 'array':
                $first = true;
                $r = "[";
                foreach ($value as $k => $v) {
                    if ($first) {
                        $first = false;
                    } else {
                        $r .= ", ";
                    }
                    $v = self::stringify($v, $level + 1);
                    $r .= "{$k}: {$v}";
                }
                $r .= "]";
                return $r;
            case 'object':
                $s = json_encode($value);
                if ($s === false) {
                    return '<failed to json_encode>';
                }
                return self::removeNewlines($s);
            default:
                return '<unknown type>';
        }
    }

    private static function removeNewlines($s)
    {
        return strtr($s, [
            "\r" => '<\r>',
            "\n" => '<\n>',
        ]);
    }
}
