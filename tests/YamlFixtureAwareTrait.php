<?php

namespace Aa\ArrayValidator\Tests;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

trait YamlFixtureAwareTrait
{
    /**
     * @param string $fileName
     * @param string $testDir
     * 
     * @return array
     *
     * @throws ParseException
     */
    protected function getDataFromFixtureFile($fileName, $testDir = '')
    {
        $testDir = '' === $testDir ? '' : '/'.$testDir;
        $filePath = __DIR__.sprintf('%s/fixtures/%s.yml', $testDir, $fileName);
        $fixtureData = file_get_contents($filePath);

        return Yaml::parse($fixtureData);
    }
}
