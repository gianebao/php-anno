<?php

class TemplateHelper
{

    public static function mdResponse($data, $indents = 0)
    {
        echo '> ' . str_repeat('  ', $indents) . '* __' . $data['type'] 
            . '__: `' . $data['name'] . '` -- ' . $data['description'] . "\n";
        
        foreach ($data as $key => $value)
        {
            if (0 === strpos($key, '.'))
            {
                TemplateHelper::mdResponse($value, $indents + 2);
            }
        }
    }
    
    public static function permaName($string)
    {
        return strtolower(preg_replace('/[^a-z0-9_]/i', '-', $string));
    }
}