<?php

namespace Aa\ArrayValidator;

use Symfony\Component\Yaml\Yaml;

trait YamlFixtureAwareTrait
{
    /**
     * @param string $fileName
     * @param string $testDir
     * 
     * @return array
     */
    protected function getDataFromFixtureFile($fileName, $testDir = '')
    {
        $filePath = sprintf('%s/%s.yml', $testDir, $fileName);
        $fixtureData = file_get_contents($filePath);

        return Yaml::parse($fixtureData);
    }
}
