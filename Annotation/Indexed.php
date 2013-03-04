<?php
/**
 * @author Stepan Anchugov <kixxx1@gmail.com>
 */

namespace Kix\SearchBundle\Annotation;

use Kix\SearchBundle\Annotation\AbstractIndexed;

/**
 * General indexed field
 *
 * @package Search
 * @package Annotations
 *
 * @Annotation
 */
class Indexed extends AbstractIndexed
{
    /**
     * Solr field type
     *
     * @var string
     */
    public $type;
}
