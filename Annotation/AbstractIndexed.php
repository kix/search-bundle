<?php
/**
 * @author Stepan Anchugov <kixxx1@gmail.com>
 */

namespace Kix\SearchBundle\Annotation;

/**
 * Abstract indexed annotation
 * Contains general schema-related properties
 *
 * @package Search
 * @package Annotations
 */
abstract class AbstractIndexed {

    /**
     * Should the field be indexed?
     *
     * @var bool
     */
    public $indexed = true;

    /**
     * Should the field be stored?
     *
     * @var bool
     */
    public $stored  = true;

    /**
     * Is the field required?
     *
     * @var bool
     */
    public $required = false;

    /**
     * Is the field multi-valued?
     *
     * @var bool
     */
    public $multiValued = false;

    /**
     * Field name
     *
     * @var string
     */
    public $name = false;

}
