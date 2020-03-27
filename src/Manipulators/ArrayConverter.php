<?php

namespace Cerbero\Dto\Manipulators;

/**
 * The array converter.
 *
 */
class ArrayConverter
{
    /**
     * The class instance.
     *
     * @var self
     */
    protected static $instance;

    /**
     * The registered conversions.
     *
     * @var array
     */
    protected $conversions = [];

    /**
     * The cached value converters.
     *
     * @var ValueConverter[]
     */
    protected $cachedConverters = [];

    /**
     * Instantiate the class
     *
     */
    protected function __construct()
    {
        //
    }

    /**
     * Retrieve the class instance
     *
     * @return self
     */
    public static function instance(): self
    {
        return static::$instance = static::$instance ?: new static();
    }

    /**
     * Set the given value conversions
     *
     * @param array $conversions
     * @return self
     */
    public function setConversions(array $conversions): self
    {
        $this->conversions = $conversions;

        return $this;
    }

    /**
     * Retrieve the value conversions
     *
     * @return array
     */
    public function getConversions(): array
    {
        return $this->conversions;
    }

    /**
     * Convert the given item into an array
     *
     * @param mixed $item
     * @return mixed
     */
    public function convert($item)
    {
        if (is_object($item) && $converter = $this->getConverterByInstance($item)) {
            return $converter->fromDto($item);
        }

        if (is_iterable($item)) {
            $result = [];

            foreach ($item as $key => $value) {
                $result[$key] = $this->convert($value);
            }

            return $result;
        }

        return $item;
    }

    /**
     * Retrieve the converter for the given object instance
     *
     * @param object $instance
     * @return ValueConverter|null
     */
    public function getConverterByInstance($instance): ?ValueConverter
    {
        $class = get_class($instance);

        if (isset($this->cachedConverters[$class])) {
            return $this->cachedConverters[$class];
        }

        foreach ($this->getConversions() as $type => $class) {
            if (is_a($instance, $type)) {
                $converter = $this->resolveConverter($class);
                return $this->cachedConverters[$class] = $this->cachedConverters[$type] = $converter;
            }
        }

        return null;
    }

    /**
     * Retrieve the instance of the given converter
     *
     * @param string $converter
     * @return ValueConverter
     */
    protected function resolveConverter(string $converter): ValueConverter
    {
        return new $converter();
    }

    /**
     * Retrieve the converter for the given class
     *
     * @param string $class
     * @return ValueConverter|null
     */
    public function getConverterByClass(string $class): ?ValueConverter
    {
        if (isset($this->cachedConverters[$class])) {
            return $this->cachedConverters[$class];
        }

        if ($converter = $this->conversions[$class] ?? null) {
            return $this->cachedConverters[$class] = $this->resolveConverter($converter);
        }

        return null;
    }
}