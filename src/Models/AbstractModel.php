<?php
/**
 * AbstractModel.php
 *
 * @package   wp-queue
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace AshleyFae\WpQueue\Models;

use DateTime;
use Exception;
use ReflectionObject;

abstract class AbstractModel
{
    /** @var array Names of all valid database columns. */
    protected array $dbColumnNames = [];

    /** @var array Names of properties that will appear in {@see Serializable::toArray()} */
    protected array $serializablePropertyNames = [];

    /**
     * The attributes that should be cast.
     *
     * @var string[]
     */
    protected array $casts = [];

    protected static array $supportedCastTypes = [
        'array',
        'bool',
        'date',
        'datetime',
        'int',
        'float',
        'string',
    ];

    public function __construct(array $properties = [])
    {
        $this->seed($properties);
    }

    public function seed(array $properties): AbstractModel
    {
        foreach ($this->castAttributes($properties) as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        return $this;
    }

    protected function castAttributes(array $attributes = []): array
    {
        $newAttributes = [];

        foreach($attributes as $key => $value) {
            $newAttributes[$key] = $this->castAttribute($key, $value);
        }

        return $newAttributes;
    }

    protected function castAttribute(string $propertyName, mixed $propertyValue) : mixed
    {
        // Let null be null.
        if (is_null($propertyValue)) {
            return null;
        }

        if (! $this->isCastedProperty($propertyName)) {
            return $propertyValue;
        }

        try {
            return match ($this->casts[$propertyName] ?? 'string') {
                'array' => is_array($propertyValue) ? $propertyValue : json_decode($propertyValue, true),
                'bool' => (bool) $propertyValue,
                'date' => (new DateTime($propertyValue))->setTime(0, 0, 0, 0),
                'datetime' => new DateTime($propertyValue),
                'float' => (float) $propertyValue,
                'int' => (int) $propertyValue,
                'string' => (string) $propertyValue,
                default => $propertyValue,
            };
        } catch(Exception $e) {
            return null;
        }
    }

    protected function isCastedProperty(string $propertyName) : bool
    {
        return array_key_exists($propertyName, $this->casts) &&
            in_array($this->casts[$propertyName], static::$supportedCastTypes);
    }

    protected function unCastAttribute(string $propertyName, mixed $propertyValue) : mixed
    {
        // Let null be null.
        if (is_null($propertyValue)) {
            return null;
        }

        if (! $this->isCastedProperty($propertyName)) {
            return (string) $propertyValue;
        }

        if (is_array($propertyValue)) {
            return json_encode($propertyValue);
        } elseif($propertyValue instanceof DateTime) {
            if ($this->casts[$propertyName] === 'date') {
                $format = 'Y-m-d';
            } else {
                $format = 'Y-m-d H:i:s';
            }

            return $propertyValue->format($format);
        } elseif(is_bool($propertyValue)) {
            return (int) $propertyValue;
        } else {
            return (string) $propertyValue;
        }
    }

    public function toArray(): array
    {
        $properties = [];
        // public properties only
        foreach((new ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isInitialized($this)) {
                $properties[$property->getName()] = $property->getValue($this);
            }
        }

        if (! empty($this->serializablePropertyNames)) {
            $properties = array_intersect_key($properties, array_flip($this->serializablePropertyNames));
        }

        return $properties;
    }

    public function toDbArray(): array
    {
        $data = $this->toArray();

        if ($this->dbColumnNames) {
            $data = array_intersect_key($data, array_flip($this->dbColumnNames));
        }

        $preparedData = [];
        foreach($data as $key => $value) {
            $preparedData[$key] = $this->unCastAttribute(propertyName: $key, propertyValue: $value);
        }

        return $preparedData;
    }
}
