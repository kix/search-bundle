<?php
/**
 * @author Stepan Anchugov <kixxx1@gmail.com>
 */
namespace Kix\SearchBundle\Service;

use \Doctrine\ORM\EntityManager;

/**
 * Builds indexing metadata for entity classes from search annotations
 */
class MetadataFactory
{

    /**
     * Entity manager
     *
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * Annotation reader
     *
     * @var \Doctrine\Common\Annotations\Reader
     */
    private $reader;

    /**
     * @param $em EntityManager
     */
    public function __construct($em, \Doctrine\Common\Annotations\Reader $annotReader)
    {
        $this->em = $em;
        $this->reader = $annotReader;
    }

    /**
     * Does what it says
     *
     * @param string $className
     */
    public function getMetadataForClassname($className)
    {
        $meta = $this->em->getMetadataFactory()->getMetadataFor($className);
        $refl  = $meta->getReflectionClass();

        $fields = array();

        foreach ($refl->getProperties() as $property) {

            $fieldMeta = false;

            if ($meta = $this->reader->getPropertyAnnotation($property, '\\Kix\\SearchBundle\\Annotation\\Indexed')) {
                $fieldMeta = $meta;
            }
            if ($meta = $this->reader->getPropertyAnnotation(
                $property,
                '\\Kix\\SearchBundle\\Annotation\\ComputedIndexed'
            )) {
                $fieldMeta = $meta;
            }

            if ($fieldMeta) {
                $fields [$property->name] = array(
                    'property' => $property,
                    'meta' => $fieldMeta,
                );
            }
        }

        return $fields;
    }

}
