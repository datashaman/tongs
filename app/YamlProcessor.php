<?php

namespace Datashaman\Tongs;

use Symfony\Component\Yaml\Yaml;
use Webuni\FrontMatter\Processor\ProcessorInterface;

final class YamlProcessor implements ProcessorInterface
{
    public function parse($string)
    {
        return Yaml::parse($string, Yaml::PARSE_DATETIME);
    }

    public function dump($data)
    {
        if (is_array($data) && empty($data)) {
            return '';
        }

        return Yaml::dump($data);
    }
}
