<?php
/*
 * Copyright 2008 Sven Sanzenbacher
 *
 * This file is part of the naucon package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Naucon\Storage\Merge;

/**
 * Interface MergeStrategyInterface
 *
 * @package Naucon\Storage\Merge
 * @author Sven Sanzenbacher
 */
interface MergeStrategyInterface
{
    /**
     * @param   object      $nextModel
     * @param   object      $previousModel
     * @return  object      merged model
     */
    public function merge($nextModel, $previousModel);
}