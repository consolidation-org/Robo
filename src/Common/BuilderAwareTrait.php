<?php

namespace Robo\Common;

use Robo\Collection\CollectionBuilder;
use Robo\Robo;
use Robo\Symfony\ConsoleIO;

trait BuilderAwareTrait
{
    /**
     * @var \Robo\Collection\CollectionBuilder
     */
    protected $builder;

    /**
     * @see \Robo\Contract\BuilderAwareInterface::setBuilder()
     *
     * @param \Robo\Collection\CollectionBuilder $builder
     *
     * @return $this
     */
    public function setBuilder(CollectionBuilder $builder)
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * @see \Robo\Contract\BuilderAwareInterface::getBuilder()
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * @return \Robo\Collection\CollectionBuilder
     *
     * @param \Robo\Symfony\ConsoleIO
     */
    protected function collectionBuilder($io = null)
    {
        // TODO: trigger_error if $io is null.
        if (!$io) {
            $io = new ConsoleIO(Robo::input(), Robo::output());
        }
        return $this->getBuilder()->newBuilder()->inflectIf($this)->inflectIf($io);
    }
}
