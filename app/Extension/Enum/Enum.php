<?php
namespace App\Extension\Enum;

use ReflectionClass;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Facades\Lang;
abstract class Enum
{
    use Macroable;
   
    protected static $constCacheArray = [];
    
    protected static function getConstants(): array
    {
        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, static::$constCacheArray)) {
            $reflect = new ReflectionClass($calledClass);
            static::$constCacheArray[$calledClass] = $reflect->getConstants();
        }
        return static::$constCacheArray[$calledClass];
    }
    
    public static function getKeys(): array
    {
        return array_keys(static::getConstants());
    }
    
    public static function getValues(): array
    {
        return array_values(static::getConstants());
    }
    
    public static function getKey($value): string
    {
        return array_search($value, static::getConstants(), true);
    }
    
    public static function getValue(string $key)
    {
        return static::getConstants()[$key];
    }
    
    public static function getDescription($value): string
    {
        return 
            static::getLocalizedDescription($value) ??
            static::getFriendlyKeyName(static::getKey($value));
    }
    
    protected static function getLocalizedDescription($value): ?string
    {
        if (static::isLocalizable())
        {
            $localizedStringKey = static::getLocalizationKey() . '.' . $value;
            if (Lang::has($localizedStringKey))
            {
                return __($localizedStringKey);
            }
        }
        return null;
    }
    
    public static function getRandomKey(): string
    {
        $keys = static::getKeys();
        return $keys[array_rand($keys)];
    }
    
    public static function getRandomValue()
    {
        $values = static::getValues();
        return $values[array_rand($values)];
    }
    
    public static function toArray(): array
    {
        return static::getConstants();
    }
    
    public static function toSelectArray(): array
    {
        $array = static::toArray();
        $selectArray = [];
        foreach ($array as $key => $value) {
            $selectArray[$value] = static::getDescription($value);
        }
        return $selectArray;
    }
   
    public static function hasKey(string $key): bool
    {
        return in_array($key, static::getKeys(), true);
    }
    
    public static function hasValue($value, bool $strict = true): bool
    {
        $validValues = static::getValues();
        if ($strict) {
            return in_array($value, $validValues, true);
        }
        return in_array((string) $value, array_map('strval', $validValues), true);
    }
    
    protected static function getFriendlyKeyName(string $key): string
    {
        if (ctype_upper(str_replace('_', '', $key))) {
            $key = strtolower($key);
        }
        return ucfirst(str_replace('_', ' ', snake_case($key)));
    }
    
    protected static function isLocalizable()
    {
        return isset(class_implements(static::class)[static::class]);
    }
    
    public static function getLocalizationKey()
    {
        return 'enums.' . static::class;
    }
}