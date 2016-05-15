<?php

namespace Aa\ArrayValidator;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;

class ConstraintReader
{
    const DEFAULT_NAMESPACE = 'Symfony\Component\Validator\Constraints';
    const DEFAULT_PATH = '../vendor/symfony/validator/Constraints/';

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

            $path = str_replace(self::DEFAULT_NAMESPACE.'\\', self::DEFAULT_PATH, $className).'.php';
            if (is_file($path)) {
                require $path;

                return true;
            }

            return false;
        };
    }
}
