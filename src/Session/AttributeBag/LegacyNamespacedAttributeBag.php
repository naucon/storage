<?php

namespace Naucon\Storage\Session\AttributeBag;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use function array_key_exists;
use function count;

class LegacyNamespacedAttributeBag extends AttributeBag
{
    private string $namespaceCharacter;

    /**
     * @param string $storageKey         Session storage key
     * @param string $namespaceCharacter Namespace character to use in keys
     */
    public function __construct(string $storageKey = '_sf2_attributes', string $namespaceCharacter = '/')
    {
        $this->namespaceCharacter = $namespaceCharacter;
        parent::__construct($storageKey);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name): bool
    {
        $attributes = $this->resolveAttributePath($name);
        $name = $this->resolveKey($name);

        if (null === $attributes) {
            return false;
        }

        return array_key_exists($name, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, $default = null): mixed
    {
        $attributes = $this->resolveAttributePath($name);
        $name = $this->resolveKey($name);

        if (null === $attributes) {
            return $default;
        }

        return array_key_exists($name, $attributes) ? $attributes[$name] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value): void
    {
        $attributes = &$this->resolveAttributePath($name, true);
        $name = $this->resolveKey($name);
        $attributes[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($name): mixed
    {
        $retval = null;
        $attributes = &$this->resolveAttributePath($name);
        $name = $this->resolveKey($name);
        if (null !== $attributes && array_key_exists($name, $attributes)) {
            $retval = $attributes[$name];
            unset($attributes[$name]);
        }

        return $retval;
    }

    /**
     * Resolves a path in attributes property and returns it as a reference.
     *
     * This method allows structured namespacing of session attributes.
     *
     * @param string $name         Key name
     * @param bool   $writeContext Write context, default false
     *
     * @return array|null
     */
    protected function &resolveAttributePath(string $name, bool $writeContext = false): ?array
    {
        $array = &$this->attributes;
        $name = (str_starts_with($name, $this->namespaceCharacter)) ? substr($name, 1) : $name;

        // Check if there is anything to do, else return
        if (!$name) {
            return $array;
        }

        $parts = explode($this->namespaceCharacter, $name);
        if (count($parts) < 2) {
            if (!$writeContext) {
                return $array;
            }

            $array[$parts[0]] = [];

            return $array;
        }

        unset($parts[count($parts) - 1]);

        foreach ($parts as $part) {
            if (null !== $array && !array_key_exists($part, $array)) {
                if (!$writeContext) {
                    $null = null;

                    return $null;
                }

                $array[$part] = [];
            }

            $array = &$array[$part];
        }

        return $array;
    }

    /**
     * Resolves the key from the name.
     *
     * This is the last part in a dot separated string.
     *
     * @return string
     */
    protected function resolveKey(string $name): string
    {
        if (false !== $pos = strrpos($name, $this->namespaceCharacter)) {
            $name = substr($name, $pos + 1);
        }

        return $name;
    }
}
