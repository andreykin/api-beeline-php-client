<?php

namespace Beeline;

class BeelineResponseParser
{
    /**
     * @param string $content
     * @return SimpleXMLElement
     */
    public static function parseXML(string $content)
    {
        return new \SimpleXMLElement($content);
    }
}