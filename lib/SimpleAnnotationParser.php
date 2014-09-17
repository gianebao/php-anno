<?php

class SimpleAnnotationParser {

    const SINGLE_FLAG = '/@:annotation\ +(.*)/';
    const TAG = '/@:annotation\ +(.*)\r?\n/m';
    const API_RESPONSE_ALL = '/@return\ +API\ +Responds:\r?\n((\ +\*\ {2,}.*\r?\n)*)/m';
    const API_RESPONSE_ITEM = '/\ {2,}(\w+)\ +(\w+)((\.\w+)*)\ +(.*\r?\n(\ +\*\ {4,}.*\r?\n)*)/m';
    const RESPONSE = '/@return\ +(\w+)\ +(.*\r?\n(\ +\*\ {2,}.*\r?\n)*)/m';
    const PARAMS = '/@param\ +(\w+)\ +(\w+)\ +\{(\d+)(\,\d+)?\}\ +(.*\r?\n(\ +\*\ {2,}.*\r?\n)*)/m';
    const STRIP = '/^\/?\ *\*(\ {:indentCount}|\*|\/|(\r?\n))/m';
    
    public static function standard_indents($comment)
    {
        return str_replace("\t", '    ', $comment);
    }
    
    public static function classComment($comment)
    {
        $output = array();
        
        $comment = SimpleAnnotationParser::standard_indents($comment);
        
        SimpleAnnotationParser::_singleFlag('ignore', $comment, $output);

        if (!empty($output['ignore']))
        {
            return $output;
        }

        SimpleAnnotationParser::_multiple(
            'see',
            SimpleAnnotationParser::_tag('see', $comment),
            $output
        );

        SimpleAnnotationParser::_unique(
            'package',
            SimpleAnnotationParser::_tag('package', $comment),
            $output
        );

        SimpleAnnotationParser::_unique(
            'version',
            SimpleAnnotationParser::_tag('version', $comment),
            $output
        );
        
        $output['description'] = SimpleAnnotationParser::_strip($comment);

        return $output;
    }

    public static function methodComment($comment)
    {
        $output = array();
        
        $comment = SimpleAnnotationParser::standard_indents($comment);
        SimpleAnnotationParser::_singleFlag('ignore', $comment, $output);

        if (!empty($output['ignore']))
        {
            return $output;
        }

        SimpleAnnotationParser::_multiple(
            'see',
            SimpleAnnotationParser::_tag('see', $comment),
            $output
        );

        SimpleAnnotationParser::_params($comment, $output);
        SimpleAnnotationParser::_APIResponse($comment, $output);
        SimpleAnnotationParser::_return($comment, $output);

        $output['description'] = SimpleAnnotationParser::_strip($comment);

        return $output;
    }

    protected static function _singleFlag($anno, & $comment, array & $output)
    {
        $regex = strtr(SimpleAnnotationParser::SINGLE_FLAG, array(':annotation' => $anno));
        $status = preg_match_all($regex, $comment);

        if (empty($status))
        {
            return false;
        }

        $comment = preg_replace($regex, "\n", $comment);
        $output[$anno] = true;
    }

    protected static function _tag($anno, & $comment)
    {
        $regex = strtr(SimpleAnnotationParser::TAG, array(':annotation' => $anno));

        $status = preg_match_all($regex, $comment, $matches, PREG_SET_ORDER);

        if (empty($status))
        {
            return false;
        }

        $comment = preg_replace($regex, "\n", $comment);

        return $matches;
    }

    protected static function _multiple($anno, $matches, array & $output)
    {
        if (empty($matches))
        {
            return false;
        }

        $matches = array_pop($matches);

        if (!empty($matches[1]))
        {
            $output[$anno] = $matches[1];
        }
    }

    protected static function _unique($anno, $matches, array & $output)
    {
        if (empty($matches))
        {
            return false;
        }

        $array = array();
        foreach ($matches as $match)
        {
            if (!empty($match[1]))
            {
                $array[] = $match[1];
            }
        }

        $output[$anno] = $array;
    }

    protected static function _APIResponse(& $comment, array & $output)
    {
        $regex_all = SimpleAnnotationParser::API_RESPONSE_ALL;

        $params = array();
        $status = preg_match($regex_all, $comment, $matches);

        if (empty($status))
        {
            return false;
        }

        $regex = SimpleAnnotationParser::API_RESPONSE_ITEM;

        $status = preg_match_all($regex, $matches[1], $matches, PREG_SET_ORDER);

        if (empty($status))
        {
            return false;
        }

        foreach ($matches as $match)
        {
            SimpleAnnotationParser::_APIResponseFields($match, $params);
        }

        $output['response'] = $params;
        $comment = preg_replace($regex_all, "\n", $comment);
    }

    protected static function _APIResponseFields($match, array & $output)
    {
        $name = $match[2];
        $subname = ltrim($match[4], '.');
        if (empty($output[$name]))
        {
            $output[$name] = array();
        }

        $details = array(
            'name' => empty($subname) ? $name: $subname,
            'type' => $match[1],
            'description' => SimpleAnnotationParser::_strip(trim($match[5]), 4)
        );

        if (empty($subname))
        {
            return $output[$name] = $details;
        }

        SimpleAnnotationParser::_assignRecurse($details, explode('.', trim($match[3], '.')), $output[$name]);
    }

    private static function _assignRecurse($value, $key, array & $array)
    {
        if (empty($key))
        {
            return false;
        }

        $i = '.' . array_shift($key);

        if (empty($array[$i]))
        {
            $array[$i] = array();
        }
        if (empty($key))
        {
            return $array[$i] = $value;
        }

        SimpleAnnotationParser::_assignRecurse($value, $key, $array[$i]);
    }

    protected static function _return(& $comment, array & $output)
    {
        $regex = SimpleAnnotationParser::RESPONSE;

        $params = array();
        $status = preg_match_all($regex, $comment, $matches, PREG_SET_ORDER);

        if (empty($status))
        {
            return false;
        }

        $matches = array_pop($matches);

        $output['response'] = array(
            'type' => $matches[1],
            'description' => SimpleAnnotationParser::_strip(trim($matches[2]))
        );

        $comment = preg_replace($regex, "\n", $comment);
    }

    protected static function _params(& $comment, array & $output)
    {
        $regex = SimpleAnnotationParser::PARAMS;

        $params = array();
        $status = preg_match_all($regex, $comment, $matches, PREG_SET_ORDER);

        if (empty($status))
        {
            return false;
        }

        $comment = preg_replace($regex, "\n", $comment);

        foreach ($matches as $match)
        {
            $params[] = array(
                'name' => $match[2],
                'type' => $match[1],
                'optional' => 0 === (int) $match[3],
                'length' => array(
                    'min' => (int) $match[3],
                    'max' => (int) trim($match[4], ',')
                ),
                'description' => SimpleAnnotationParser::_strip(trim($match[5]))
            );
        }

        $output['requests'] = $params;
    }

    protected static function _strip($comment, $indents = 1)
    {
        $regex = strtr(SimpleAnnotationParser::STRIP, array(':indentCount' => $indents));
        return SimpleAnnotationParser::toLiteralWhitespace(trim(preg_replace($regex, '', $comment)));
    }

    public static function toLiteralWhitespace($string)
    {
        return str_replace(array("\t"), '\t',
            str_replace(array("\r\n", "\r", "\n"), "\n", $string)
        );
    }

}
