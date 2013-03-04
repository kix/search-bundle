<?php
/**
 * @author Stepan Anchugov <kixxx1@gmail.com>
 */

namespace Kix\SearchBundle\Service\Exception;

/**
 * @package Search
 */
class SearchIndexerException extends \Exception
{

    public static function fieldGetterNotFound($fieldName, $getter)
    {
        return new self(
            sprintf(
                "Expected property getter method `%s` for field `%s` not found",
                $getter, $fieldName
            )
        );
    }

}
