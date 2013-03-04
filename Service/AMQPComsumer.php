<?php
/**
 * @author Stepan Anchugov <kixxx1@gmail.com>
 */
namespace Kix\SearchBundle\Service;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * An AMQP wrapper
 */
class AMQPComsumer implements ConsumerInterface
{

    /**
     * @param SearchIndexer $indexer
     */
    public function __construct(SearchIndexer $indexer)
    {
        $this->indexer = $indexer;
    }

    /**
     * That's where the work is done
     *
     * @param \PhpAmqpLib\Message\AMQPMessage $msg
     */
    public function execute(AMQPMessage $msg)
    {
        extract(unserialize($msg->body));
        /** @var string $entityClass
         *  @var int $id */

        $this->indexer->indexEntity($entityClass, $id);
    }

}
