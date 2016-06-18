<?php

namespace Aa\ArrayValidator;

use Aa\ArrayValidator\Exceptions\ConstraintReaderException;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;
use Symfony\Component\Validator\Constraints\EqualTo;

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

    /**
     * @param array  $definitions
     * @param string $keyPrefix
     *
     * @return array
     */
    public function read(array &$definitions, $keyPrefix = '')
    {
        $keyPrefix = $keyPrefix ? $keyPrefix.'/' : '';

        $constraints = [];

        foreach ($definitions as $key => &$keyDefinitions) {
            if(is_array($keyDefinitions)) {
                foreach ($keyDefinitions as $index => $definition) {
                    try {
                        $annotations = $this->parser->parse('@'.$definition);
                    } catch(AnnotationException $exception) {
                        throw new ConstraintReaderException($key, $index, 0, $exception);
                    }
                    $constraints[$keyPrefix.$key][] = $annotations[0];
                }
                continue;
            }

            $constraints[$keyPrefix.$key] = [new EqualTo(['value' => $keyDefinitions])];
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
