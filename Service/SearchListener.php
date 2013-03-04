<?php
/**
 * @author Stepan Anchugov <kixxx1@gmail.com>
 */

namespace Kix\SearchBundle\Service;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\Annotations\AnnotationRegistry;

use \Symfony\Component\DependencyInjection\ContainerInterface;

use Kix\SearchBundle\Service\SearchIndexer;
use Kix\MainBundle\Entity;

/**
 * Detects indexable entities and sends their IDs as messages into AMQP
 * @package Search
 */
class SearchListener
{

    /**
     * If we should use AMQP to queue index requests
     * All the stuff goes synchronous and slowpoke if this is ```false```
     *
     * @var bool
     */
    private $queued;

    /**
     * @var SearchIndexer
     */
    private $indexer;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor!
     *
     * @todo Switch to a more precise DI
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->producer = $this->container->get('old_sound_rabbit_mq.index_entity_producer');
        $this->queued   = $this->container->getParameter('indexer_queued');

        if (!$this->queued) {
            $this->indexer = $container->get('Kix.indexer');
        }
    }

    /**
     * Hook that tells indexed entities against non-indexed
     * and sends indexed ones to the queue or the indexer
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // @todo Use annotations
        /** @var $entity Entity\WorkOrder */
        if ($entity instanceof Entity\WorkOrder) {
            $this->processEntity($entity);
        }
    }

    /**
     * Process a given entity
     * Depending on settings it either passes the entity to queue
     * or indexes it synchronously via given SearchIndexer
     *
     * @param $entity
     * @return bool
     */
    private function processEntity($entity)
    {
        if ($this->queued) {
            return $this->producer->publish(serialize(
                    array(
                        'entityClass' => get_class($entity),
                        'id'           => $entity->getId(),
                    )));
        } else {
            return $this->indexer->indexEntity(
                get_class($entity),
                $entity->getId()
            );
        }
    }
}
