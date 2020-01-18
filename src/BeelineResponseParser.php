<?php

namespace Beeline;

use SimpleXMLElement;

class BeelineResponseParser
{
    /**
     * @param string $content
     * @return SimpleXMLElement
     */
    public static function parseXML(string $content)
    {
        return new SimpleXMLElement($content);
    }
}