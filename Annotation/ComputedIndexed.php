<?php
/**
 * @author Stepan Anchugov <kixxx1@gmail.com>
 */

namespace Kix\SearchBundle\Annotation;

/**
 * Intended to support dependent entity property indexing.
 *
 *  * $types contains array of field types where the key is the field name
 *  * $calls contains method names. These methods will be called on object stored in the annotated property. Key is
 *    the corresponding field from {@see \Kix\SearchBundle\Annotation\MultiIndexed}
 *
 * @package Search
 * @package Annotations
 *
 * @Annotation
 */
class ComputedIndexed extends MultiIndexed
{
    /**
     * Array of calls to make on the property for fetching extra data
     *
     * @var array
     */
    public $calls;
}
