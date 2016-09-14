<?php

namespace PhpLang\Phack\Lib;

trait EnumMethods {
    public static function assert($value) {
        if (!isset(static::$names[$value])) {
            throw new \InvariantException("$value is not a valid value for Enum ".static::class);
        }
        return $value;
    }

    public static function assertAll($values) {
        foreach ($values as $key => $value) {
            if (!isset(static::$names[$value])) {
                throw new \InvariantException("Key $key contains invalid value $value for Enum ".static::class);
            }
        }
        return $values;
    }

    public static function coerce($value) {
        return isset(static::$names[$value]) ? $value : null;
    }

    public static function getNames() {
        return static::$names;
    }

    public static function getValues() {
        return static::$values;
    }

    public static function isValid($value) {
        return isset(static::$names[$value]);
    }
}
