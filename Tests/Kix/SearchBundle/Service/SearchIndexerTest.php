<?php
/**
 * @author Stepan Anchugov <kixxx1@gmail.com>
 */
namespace Kix\SearchBundle\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SearchIndexerTest extends WebTestCase
{

    /**
     *
     */
    public function testIndexEntity()
    {
        $container = $this->createClient()->getContainer();
        $indexer = new \Kix\SearchBundle\Service\SearchIndexer($container);

    }

}
