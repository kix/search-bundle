<?php
/**
 * @author Stepan Anchugov <kixxx1@gmail.com>
 */

namespace Kix\SearchBundle\Annotation;

use Kix\SearchBundle\Annotation\AbstractIndexed;

/**
 * Used when one field indexes as two values
 *
 * @package Search
 * @package Annotations
 *
 * @Annotation
 */
class MultiIndexed extends AbstractIndexed
{

    /**
     * Array of declared field types, where key is field name and value is field type
     *
     * @var array
     */
    public $types;

}
