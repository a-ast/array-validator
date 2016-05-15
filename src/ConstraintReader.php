<?php

namespace Aa\ArrayValidator;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;

class ConstraintReader
{
    const DEFAULT_NAMESPACE = 'Symfony\Component\Validator\Constraints';

    /**
     * @var DocParser
     */
    private $parser;

    function __construct()
    {
        $this->parser = new DocParser();
        $this->parser->addNamespace(self::DEFAULT_NAMESPACE);

        AnnotationRegistry::registerLoader($this->getClassLoaderCallback());
    }

    public function read(array &$definitions)
    {
        $constraints = [];

        foreach ($definitions as $key => &$keyDefinitions) {
            foreach ($keyDefinitions as $definition) {
                $annotations = $this->parser->parse('@'.$definition);
                $constraints[$key][] = $annotations[0];
            }
        }

        return $constraints;
    }

    /**
     * @return callable
     */
    protected function getClassLoaderCallback()
    {
        return function ($className) {

            try {
                new \ReflectionClass($className);

                return true;
            } catch(\ReflectionException $exception) {
                return false;
            }
        };
    }
}
