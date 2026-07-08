<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

declare(strict_types=1);

namespace DASPRiD\Enum;

use DASPRiD\Enum\Exception\ExpectationException;
use DASPRiD\Enum\Exception\IllegalArgumentException;
use IteratorAggregate;
use Serializable;
use Traversable;

/**
 * A specialized map implementation for use with enum type keys.
 *
 * All of the keys in an enum map must come from a single enum type that is specified, when the map is created. Enum
 * maps are represented internally as arrays. This representation is extremely compact and efficient.
 *
 * Enum maps are maintained in the natural order of their keys (the order in which the enum constants are declared).
 * This is reflected in the iterators returned by the collection views {@see self::getiterator()} and
 * {@see self::values()}.
 *
 * Iterators returned by the collection views are not consistent: They may or may not show the effects of modifications
 * to the map that occur while the iteration is in progress.
 *
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class EnumMap implements IteratorAggregate, Serializable {
    /**
     * The class name of the key.
     *
     * @var string
     */
    private $keytype;

    /**
     * The type of the value.
     *
     * @var string
     */
    private $valuetype;

    /**
     * @var bool
     */
    private $allownullvalues;

    /**
     * All of the constants comprising the enum, cached for performance.
     *
     * @var array<int, AbstractEnum>
     */
    private $keyuniverse;

    /**
     * Array representation of this map. The ith element is the value to which universe[i] is currently mapped, or null
     * if it isn't mapped to anything, or NullValue if it's mapped to null.
     *
     * @var array<int, mixed>
     */
    private $values;

    /**
     * @var int
     */
    private $size = 0;

    /**
     * Creates a new enum map.
     *
     * @param string $keytype the type of the keys, must extend AbstractEnum
     * @param string $valuetype the type of the values
     * @param bool $allownullvalues whether to allow null values
     * @throws IllegalArgumentException when key type does not extend AbstractEnum
     */
    public function __construct(string $keytype, string $valuetype, bool $allownullvalues) {
        if (! is_subclass_of($keytype, AbstractEnum::class)) {
            throw new IllegalArgumentException(sprintf(
                'Class %s does not extend %s',
                $keytype,
                AbstractEnum::class
            ));
        }

        $this->keytype = $keytype;
        $this->valuetype = $valuetype;
        $this->allownullvalues = $allownullvalues;
        $this->keyuniverse = $keytype::values();
        $this->values = array_fill(0, count($this->keyuniverse), null);
    }

    /**
     * Serialize the enum map to an array.
     *
     * @return array
     */
    public function __serialize(): array {
        $values = [];

        foreach ($this->values as $ordinal => $value) {
            if (null === $value) {
                continue;
            }

            $values[$ordinal] = $this->mask_null($value);
        }

        return [
            'keytype' => $this->keytype,
            'valuetype' => $this->valuetype,
            'allownullvalues' => $this->allownullvalues,
            'values' => $values,
        ];
    }

    /**
     * Unserialize the enum map from an array.
     *
     * @param array $data The data to unserialize
     * @return void
     */
    public function __unserialize(array $data): void {
        $this->unserialize(serialize($data));
    }

    /**
     * Checks whether the map types match the supplied ones.
     *
     * You should call this method when an EnumMap is passed to you and you want to ensure that it's made up of the
     * correct types.
     *
     * @param string $keytype The key type to check
     * @param string $valuetype The value type to check
     * @param bool $allownullvalues Whether null values should be allowed
     * @throws ExpectationException when supplied key type mismatches local key type
     * @throws ExpectationException when supplied value type mismatches local value type
     * @throws ExpectationException when the supplied map allows null values, abut should not
     */
    public function expect(string $keytype, string $valuetype, bool $allownullvalues): void {
        if ($keytype !== $this->keytype) {
            throw new ExpectationException(sprintf(
                'Callee expected an EnumMap with key type %s, but got %s',
                $keytype,
                $this->keytype
            ));
        }

        if ($valuetype !== $this->valuetype) {
            throw new ExpectationException(sprintf(
                'Callee expected an EnumMap with value type %s, but got %s',
                $keytype,
                $this->keytype
            ));
        }

        if ($allownullvalues !== $this->allownullvalues) {
            throw new ExpectationException(sprintf(
                'Callee expected an EnumMap with nullable flag %s, but got %s',
                ($allownullvalues ? 'true' : 'false'),
                ($this->allownullvalues ? 'true' : 'false')
            ));
        }
    }

    /**
     * Returns the number of key-value mappings in this map.
     */
    public function size(): int {
        return $this->size;
    }

    /**
     * Returns true if this map maps one or more keys to the specified value.
     *
     * @param mixed $value The value to search for
     * @return bool True if the value is in the map
     */
    public function containsvalue($value): bool {
        return in_array($this->mask_null($value), $this->values, true);
    }

    /**
     * Returns true if this map contains a mapping for the specified key.
     *
     * @param AbstractEnum $key The key to check
     * @return bool True if the key is in the map
     */
    public function containskey(AbstractEnum $key): bool {
        $this->check_key_type($key);
        return null !== $this->values[$key->ordinal()];
    }

    /**
     * Returns the value to which the specified key is mapped, or null if this map contains no mapping for the key.
     *
     * More formally, if this map contains a mapping from a key to a value, then this method returns the value;
     * otherwise it returns null (there can be at most one such mapping).
     *
     * A return value of null does not necessarily indicate that the map contains no mapping for the key; it's also
     * possible that hte map explicitly maps the key to null. The {@see self::containskey()} operation may be used to
     * distinguish these two cases.
     *
     * @param AbstractEnum $key The key to retrieve
     * @return mixed The value associated with the key, or null if not found
     */
    public function get(AbstractEnum $key) {
        $this->check_key_type($key);
        return $this->unmask_null($this->values[$key->ordinal()]);
    }

    /**
     * Associates the specified value with the specified key in this map.
     *
     * If the map previously contained a mapping for this key, the old value is replaced.
     *
     * @param AbstractEnum $key The key to associate with the value
     * @param mixed $value The value to associate with the key
     * @return mixed the previous value associated with the specified key, or null if there was no mapping for the key.
     *               (a null return can also indicate that the map previously associated null with the specified key.)
     * @throws IllegalArgumentException when the passed values does not match the internal value type
     */
    public function put(AbstractEnum $key, $value) {
        $this->check_key_type($key);

        if (! $this->is_valid_value($value)) {
            throw new IllegalArgumentException(sprintf('Value is not of type %s', $this->valuetype));
        }

        $index = $key->ordinal();
        $oldvalue = $this->values[$index];
        $this->values[$index] = $this->mask_null($value);

        if (null === $oldvalue) {
            ++$this->size;
        }

        return $this->unmask_null($oldvalue);
    }

    /**
     * Removes the mapping for this key frm this map if present.
     *
     * @param AbstractEnum $key The key to remove
     * @return mixed the previous value associated with the specified key, or null if there was no mapping for the key.
     *               (a null return can also indicate that the map previously associated null with the specified key.)
     */
    public function remove(AbstractEnum $key) {
        $this->check_key_type($key);

        $index = $key->ordinal();
        $oldvalue = $this->values[$index];
        $this->values[$index] = null;

        if (null !== $oldvalue) {
            --$this->size;
        }

        return $this->unmask_null($oldvalue);
    }

    /**
     * Removes all mappings from this map.
     */
    public function clear(): void {
        $this->values = array_fill(0, count($this->keyuniverse), null);
        $this->size = 0;
    }

    /**
     * Compares the specified map with this map for quality.
     *
     * Returns true if the two maps represent the same mappings.
     */
    public function equals(self $other): bool {
        if ($this === $other) {
            return true;
        }

        if ($this->size !== $other->size) {
            return false;
        }

        return $this->values === $other->values;
    }

    /**
     * Returns the values contained in this map.
     *
     * The array will contain the values in the order their corresponding keys appear in the map, which is their natural
     * order (the order in which the num constants are declared).
     */
    public function values(): array {
        return array_values(array_map(function ($value) {
            return $this->unmask_null($value);
        }, array_filter($this->values, function ($value): bool {
            return null !== $value;
        })));
    }

    /**
     * Serialize the enum map to a string.
     *
     * @return string
     */
    public function serialize(): string {
        return serialize($this->__serialize());
    }

    /**
     * Unserialize the enum map from a string.
     *
     * @param string $serialized The serialized data
     * @return void
     */
    public function unserialize($serialized): void {
        $data = unserialize($serialized);
        $this->__construct($data['keytype'], $data['valuetype'], $data['allownullvalues']);

        foreach ($this->keyuniverse as $key) {
            if (array_key_exists($key->ordinal(), $data['values'])) {
                $this->put($key, $data['values'][$key->ordinal()]);
            }
        }
    }

    /**
     * Get iterator for the enum map.
     *
     * @return Traversable
     */
    public function getiterator(): Traversable {
        foreach ($this->keyuniverse as $key) {
            if (null === $this->values[$key->ordinal()]) {
                continue;
            }

            yield $key => $this->unmask_null($this->values[$key->ordinal()]);
        }
    }

    /**
     * Mask null values by replacing them with NullValue singleton.
     *
     * @param mixed $value The value to mask
     * @return mixed
     */
    private function mask_null($value) {
        if (null === $value) {
            return NullValue::instance();
        }

        return $value;
    }

    /**
     * Unmask null values by replacing NullValue singleton with actual null.
     *
     * @param mixed $value The value to unmask
     * @return mixed
     */
    private function unmask_null($value) {
        if ($value instanceof NullValue) {
            return null;
        }

        return $value;
    }

    /**
     * Checks whether the passed key matches the internal key type.
     *
     * @param AbstractEnum $key The key to check
     * @throws IllegalArgumentException when the passed key does not match the internal key type
     */
    private function check_key_type(AbstractEnum $key): void {
        if (get_class($key) !== $this->keytype) {
            throw new IllegalArgumentException(sprintf(
                'Object of type %s is not the same type as %s',
                get_class($key),
                $this->keytype
            ));
        }
    }

    /**
     * Checks whether the value is valid for this enum map.
     *
     * @param mixed $value The value to check
     * @return bool True if the value is valid
     */
    private function is_valid_value($value): bool {
        if (null === $value) {
            if ($this->allownullvalues) {
                return true;
            }

            return false;
        }

        switch ($this->valuetype) {
            case 'mixed':
                return true;

            case 'bool':
            case 'boolean':
                return is_bool($value);

            case 'int':
            case 'integer':
                return is_int($value);

            case 'float':
            case 'double':
                return is_float($value);

            case 'string':
                return is_string($value);

            case 'object':
                return is_object($value);

            case 'array':
                return is_array($value);
        }

        return $value instanceof $this->valuetype;
    }
}
