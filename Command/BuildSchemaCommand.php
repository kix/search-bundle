<?php
/**
 * @author Stepan Anchugov <kixxx1@gmail.com>
 */

namespace Kix\SearchBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand as Command;

use Doctrine\Bundle\DoctrineBundle\Mapping\MetadataFactory;

use Kix\SearchBundle\Annotation;

/**
 * Builds a ```schema.xml``` file from entity metadata
 *
 * @package Search
 */
class BuildSchemaCommand extends Command
{

    /**
     * @var array
     */
    private $fields = array();

    /**
     * @var array
     */
    private $fieldTypes = array();

    /**
     * @var bool
     */
    private $verbose = true;

    /**
     * If we should dump the resulting XML into stdout
     *
     * @var bool
     */
    private $dumpXml = true;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this
            ->setName('Kix:search:build-schema')
            ->setDescription('Build Solr schema from entity annotations')
            ->addArgument('bundle', InputArgument::OPTIONAL, 'Bundle containing entities to index')
        ;
    }

    private function log($msg)
    {
        if ($this->verbose) {
            $this->output->writeln($msg);
        }
    }

    /**
     * Gets entities marked as indexed and parses their annotations
     * to create an XML schema
     *
     * @todo use jms serializer bundle to build xml
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        /** @var $kernel \Symfony\Component\HttpKernel\Kernel */
        $kernel = $this->getContainer()->get('kernel');

        /** @var $reader \Doctrine\Common\Annotations\FileCacheReader */
        $reader = $this->getContainer()->get('annotation_reader');
        $em     = $this->getContainer()->get('doctrine.orm.entity_manager');

        $searchFactory = new \Kix\SearchBundle\Service\MetadataFactory($em, $reader);

        $managedBundles = $this->getContainer()->getParameter('wt_pro_search.bundles');

        /** @var $factory \Doctrine\Bundle\DoctrineBundle\Mapping\MetadataFactory */
        $factory = new MetadataFactory($this->getContainer()->get('doctrine'));

        foreach ($kernel->getBundles() as $bundle) {
            $this->log('Checking bundle '. $bundle->getName());
            /** var $bundle \Symfony\Component\HttpKernel\Bundle\Bundle */
            if (in_array($bundle->getName(), $managedBundles)) {
                $this->log(sprintf('Bundle <info>%s</info> is managed, checking entities', $bundle->getName()));
                /** @var $meta \Doctrine\Bundle\DoctrineBundle\Mapping\ClassMetadataCollection */
                $meta = $factory->getBundleMetadata($bundle)->getMetadata();

                /** @var $classMetadata \Doctrine\ORM\Mapping\ClassMetadata */
                foreach ($meta as $classMetadata) {
                    $className = $classMetadata->getReflectionClass()->getName();
                    $this->log(sprintf("- Checking <info>%s</info> class", $className));
                    $classMeta = $searchFactory->getMetadataForClassname($className);
                    $this->processClassMeta($classMeta);
                }
            }
        }

        $this->log('<comment>Building XML</comment>');
        $xml = $this->buildXml();

        echo $xml->asXML();
    }

    /**
     * Processes metadata definition into an abstract array
     *
     * @param $meta
     */
    private function processClassMeta($meta)
    {
        foreach ($meta as $propertySpec) {
            $this->log(sprintf('  - Checking <info>%s</info> property', $propertySpec['property']->name));

            if ($propertySpec['meta'] instanceof Annotation\MultiIndexed) {
                foreach ($propertySpec['meta']->types as $type) {
                    $this->fieldTypes [$type] = true;
                }

                foreach ($propertySpec['meta']->types as $name => $type) {
                    $fieldSpec = array();

                    foreach(array('indexed', 'stored', 'required', 'multiValued') as $prop) {
                        $fieldSpec[$prop] = $propertySpec['meta']->$prop;
                    }
                    $fieldSpec['type'] = $type;

                    $this->fields [$name] = $fieldSpec;

                }
            } elseif ($propertySpec['meta'] instanceof Annotation\Indexed) {
                $this->fieldTypes [$propertySpec['meta']->type] = true;
                $this->fields [$propertySpec['property']->name] = (array) $propertySpec['meta'];
            }
        }
    }

    /**
     * Builds XML
     */
    private function buildXml()
    {
        $xml = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\"?><schema></schema>");

        $xml->addAttribute("encoding", "UTF-8");
        $xml->addAttribute("name", "workOrder");
        $xml->addAttribute("version", "1.5");
        $xml->fields = new \SimpleXMLElement('<fields/>');

        foreach ($this->fields as $name => $field) {
            /*
            foreach ($field as $key => $prop) {
                if ($prop) $field[$key] = 'true';
                else $field[$key] = 'false';
            };
            */
            $fieldXml = $xml->fields->addChild("field");

            $fieldXml->addAttribute("name",        $name);
            $fieldXml->addAttribute("type",        $field['type']);
            $fieldXml->addAttribute("indexed",     $field['indexed']);
            $fieldXml->addAttribute("stored",      $field['stored']);
            $fieldXml->addAttribute("required",    $field['required']);
            $fieldXml->addAttribute("multiValued", $field['multiValued']);
        }

        $typesXml = $xml->addChild("types");

        foreach (array_keys($this->fieldTypes) as $type) {
            $typeXml = $typesXml->addChild("fieldType");
            $typeXml->addAttribute("name", $type);
            $typeXml->addAttribute("class", "");
            $typeXml->addAttribute("precisionStep", 0);
            $typeXml->addAttribute("positionIncrementGap", 0);
        }

        return $xml;
    }
}
