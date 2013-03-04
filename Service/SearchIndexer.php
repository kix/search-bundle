<?php
/**
 * @author Stepan Anchugov <kixxx1@gmail.com>
 */
namespace Kix\SearchBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Kix\SearchBundle\Annotation\ComputedIndexed;
use Kix\SearchBundle\Service\Exception\SearchIndexerException as Exception;

/**
 * Receives messages containing entity class and id
 * Sends these entities off via SolrClient to index
 *
 * @package Search
 */
class SearchIndexer
{

    /**
     * @var \Solarium_Client
     */
    private $solr;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * Index requests processed
     *
     * @var integer
     */
    private $processed = 0;

    /**
     * @var \Symfony\Component\HttpKernel\Log\LoggerInterface|bool
     */
    private $logger = false;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->solr = $container->get('solarium.client');

        $reader = $this->container->get('annotation_reader');
        $this->em   = $this->container->get('doctrine.orm.entity_manager');
        $this->metaFactory = new MetadataFactory($this->em, $reader);
    }

    /**
     * @param \Symfony\Component\HttpKernel\Log\LoggerInterface $logger
     * @deprecated
     */
    public function setLogger(\Symfony\Component\HttpKernel\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @deprecated
     * @param $msg
     */
    private function log($msg)
    {
        if ($this->logger) {
            $this->logger->info($msg);
        } else {
            echo $msg;
        }
    }

    /**
     * Index a single entity
     *
     * @param $entity
     */
    public function indexEntity($entityClass, $id)
    {
        $metadata =  $this->metaFactory->getMetadataForClassname($entityClass);

        $entity = $this->em->find($entityClass, $id);
        /** @var \Solarium_Query_Update $update */
        $update = $this->solr->createUpdate();

        $doc = $update->createDocument();

        foreach ($metadata as $property => $spec) {
            if (!$fieldName = $spec['meta']->name) {
                $fieldName = $property;
            }

            if ($spec['meta'] instanceof ComputedIndexed) {
                foreach ($spec['meta']->types as $fieldName => $type) {
                    $call = $spec['meta']->calls[$fieldName];

                    // Presuming that field names match etc.
                    $getter = 'get' . ucfirst($property);
                    $val = $entity->$getter()->$call();

                    $val = $this->transformValue($val, $type);
                    $doc->setField($fieldName, $val);
                }
            } else {
                $call = 'get' . ucfirst($fieldName);
                $type = $spec['meta']->type;

                if (!method_exists($entity, $call)) {
                    throw Exception::fieldGetterNotFound($fieldName, $call);
                }

                $val = $this->transformValue($entity->$call(), $type);
                $doc->setField($fieldName, $val);
            }
        }

        $update->addDocument($doc);
        $update->addCommit();

        $result = $this->solr->update($update);
        $this->processed++;

        return ($result->getStatus() == 0);
    }

    /**
     * Primitive transformer, only knows about dates
     * Move to transformer class if necessary
     *
     * @param $value
     * @param $type
     * @return mixed
     */
    private function transformValue($value, $type)
    {
        if ($type == 'date' && $value instanceof \DateTime) {
            return $value->format('Y-m-d\TH:i:s\Z');
        } else {
            return $value;
        }
    }
}
