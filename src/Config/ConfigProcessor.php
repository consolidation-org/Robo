<?php

namespace Robo\Config;

use Grasmash\YamlExpander\Expander;

/**
 * The config processor combines multiple configuration
 * files together, and processes them as necessary.
 */
class ConfigProcessor
{
    protected $processedConfig = [];
    protected $unprocessedConfig = [];

    /**
     * Extend the configuration to be processed with the
     * configuration provided by the specified loader.
     */
    public function extend(ConfigLoaderInterface $loader)
    {
        return $this->add($loader->export(), $loader->getSourceName());
    }

    /**
     * Extend the configuration to be processed with
     * the provided nested array.
     */
    public function add($data, $source = '')
    {
        if (empty($source)) {
            $this->unprocessedConfig[] = $data;
            return $this;
        }
        $this->unprocessedConfig[$source] = $data;
        return $this;
    }

    /**
     * Process all of the configuration that has been collected,
     * and return a nested array.
     */
    public function export()
    {
        if (!empty($this->unprocessedConfig)) {
            $this->processedConfig = $this->process(
                $this->processedConfig,
                $this->fetchUnprocessed()
            );
        }
        return $this->processedConfig;
    }

    /**
     * Get the configuration to be processed, and clear out the
     * 'unprocessed' list.
     * @return array
     */
    protected function fetchUnprocessed()
    {
        $toBeProcessed = $this->unprocessedConfig;
        $this->unprocessedConfig = [];
        return $toBeProcessed;
    }

    /**
     * Use a map-reduce to evaluate the items to be processed,
     * and merge them into the processed array.
     */
    protected function process($processed, $toBeProcessed)
    {
        $toBeReduced = array_map([$this, 'preprocess'], $toBeProcessed);
        return array_reduce($toBeReduced, [$this, 'reduceOne'], $processed);
    }

    /**
     * Process a single configuration file from the 'to be processed'
     * list. By default this is a no-op. Override this method to
     * provide any desired configuration preprocessing, e.g. dot-notation
     * expansion of the configuration keys, etc.
     */
    protected function preprocess($config)
    {
        return $config;
    }

    /**
     * Evaluate one item in the 'to be evaluated' list, and then
     * merge it into the processed configuration (the 'carry').
     */
    protected function reduceOne($processed, $config)
    {
        $evaluated = $this->evaluate($processed, $config);
        return static::arrayMergeRecursiveDistinct($processed, $evaluated);
    }

    /**
     * Evaluate one configuration item.
     */
    protected function evaluate($processed, $config)
    {
        return Expander::expandArrayProperties(
            $config,
            $processed
        );
    }

    /**
     * Merges arrays recursively while preserving.
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     *
     * @see http://php.net/manual/en/function.array-merge-recursive.php#92195
     * @see https://github.com/grasmash/bolt/blob/robo-rebase/src/Robo/Common/ArrayManipulator.php#L22
     */
    public static function arrayMergeRecursiveDistinct(
        array &$array1,
        array &$array2
    ) {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::arrayMergeRecursiveDistinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }
        return $merged;
    }
}
