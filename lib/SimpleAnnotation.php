<?php

require __DIR__ . DIRECTORY_SEPARATOR . 'SimpleAnnotationParser.php';

class SimpleAnnotation {

    protected $_options = array();

    protected static $_verbose = false;
    
    public function __construct(array $options = array())
    {
        if (!empty($options['bootstrap']))
        {
            if (!is_file($options['bootstrap']))
            {
                SimpleAnnotation::message('boostrap must be a valid PHP executable.', true);
            }

            include $options['bootstrap'];
        }

        SimpleAnnotation::$_verbose = isset($options['verbose']);

        $this->_options = $options;
    }

    public static function getFromTemplate($data, $template, $output = '')
    {
        $hasHeader = !empty($output);
        foreach ($data as $item)
        {
            if (!empty($item['type']) && 'markdown' == $item['type'])
            {
                $output .= $item['description'];
            }
            else
            {
                ob_start();
                include $template;
                $output .= ob_get_clean();
            }
            $output .= "\n\n";
        }

        return $output;
    }
    
    public static function message ($message, $is_error = false)
    {
        
        if (SimpleAnnotation::$_verbose)
        {
            echo true === $is_error ? '[ERROR] ': '[MESSAGE] ';
            echo $message . "\n";
        }

        if (true === $is_error)
        {
            exit(1);
        }
    }

    public static function getOptions(array $options)
    {
        $uncaught_options  = $_SERVER['argv'];
        array_shift($uncaught_options);

        $previous = null;

        foreach ($uncaught_options as $option)
        {
            if (
                0 !== strpos($option, '-')

                && (
                    empty($previous)
                    || (
                        !empty($previous)
                        && (empty($options[$i = ltrim($previous, '-')]) || $option !== $options[$i])
                    )
                )
            )
            {
                $options[] = $option;
            }

            $previous = $option;
        }

        return $options;
    }

    public static function getFiles($path)
    {
        if (is_dir($path))
        {
            $directory = new RecursiveDirectoryIterator(realpath($path));
            $iterator = new RecursiveIteratorIterator($directory);
            $regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

            return array_keys(iterator_to_array($regex));

        }
        elseif (is_file($path))
        {
            return array(realpath($path));
        }

        SimpleAnnotation::message('File or Directory does not exist.', true);
    }
    
    public function filter(array $files)
    {
        $_output = array();

        foreach ($files as $file)
        {
            SimpleAnnotation::message('Evaluating `' . $file .'`...');
            $classes = array();
            $declared_classes = get_declared_classes();
            
            if (in_array($file, get_included_files()))
            {
                continue;
            }
            
            require $file;
            
            $classes = array_diff(get_declared_classes(), $declared_classes);
            
            if (empty($classes))
            {
                $this->parseSimple($file, $_output);
                continue;
            }
            
            foreach ($classes as $class)
            {
                $doc = $this->parseClass(new ReflectionClass($class));
                
                if (!empty($doc))
                {
                    $_output[] = $doc;
                }
            }
        }
        
        return $_output;
    }

    public function parseSimple($file, array & $output = array())
    {
        $tokens = token_get_all(file_get_contents($file));

        $i = 0;

        $contents = array();

        foreach ($tokens as $token)
        {
            if (T_DOC_COMMENT !== $token[0])
            {
                continue;
            }

            $comment = $token[1];

            if (0 === $i ++)
            {
                $doc = SimpleAnnotationParser::classComment($comment);

                if (empty($doc['package']) || !empty($doc['ignore']))
                {
                    SimpleAnnotation::message('File `' . $file .'` ignored: Implied or does not belong to a package.');
                    return false;
                }

                continue;
            }

            $comment = SimpleAnnotationParser::methodComment($comment);
            
            if (!empty($comment))
            {
                $contents[] = $comment;
            }
        }

        if (empty($contents))
        {
            return false;
        }
        
        $doc['contents'] = $contents;
        $output[] = $doc;
    }

    public function parseClass(ReflectionClass $object)
    {
        $doc = array();
        $ns = 'class_ns';

        $comments = $object->getDocComment();

        if (empty($comments))
        {
            SimpleAnnotation::message('Class `' . $object->getName() .'` ignored: No documents found.');
            return false;
        }

        $name = $object->getShortName();
        
        if (!empty($this->_options[$ns]) && 0 !== strpos($name, $this->_options[$ns]))
        {
            SimpleAnnotation::message('Class `' . $object->getName() .'` ignored: Did not meet NS requirement.');
            return false;
        }
        
        $doc['name'] = trim(
            str_replace(
                array('\\', '_'), '/', !empty($this->_options[$ns])
                ? str_replace($this->_options[$ns], '', $name)
                : $name
            ), '/');

        $doc = array_merge($doc, SimpleAnnotationParser::classComment($comments));

        if (empty($doc['package']) || isset($doc['ignore']))
        {
            SimpleAnnotation::message('Class `' . $object->getName() .'` ignored: Implied or does not belong to a package.');
            return false;
        }
        
        $methods = $object->getMethods(ReflectionMethod::IS_PUBLIC);

        $method_docs = array();

        foreach ($methods as $method)
        {
            $method = $this->parseMethod($method);
            
            if (!empty($method))
            {
                $method_docs[] = $method;
            }
        }

        if (!empty($method_docs))
        {
            $doc['methods'] = $method_docs;
        }

        return $doc;
    }

    public function parseMethod(ReflectionMethod $object)
    {
        $doc = array();
        $ns = 'method_ns';

        $comments = $object->getDocComment();

        if (empty($comments))
        {
            SimpleAnnotation::message('Method `' . $object->getName() .'` ignored: No documents found.');
            return false;
        }

        $name = $object->getShortName();
        
        if (!empty($this->_options[$ns]) && 0 !== strpos($name, $this->_options[$ns]))
        {
            SimpleAnnotation::message('Method `' . $object->getName() .'` ignored: Did not meet NS requirement.');
            return false;
        }
        
        $doc['name'] = trim(
            !empty($this->_options[$ns])
                ? str_replace($this->_options[$ns], '', $name)
                : $name,
            '_');

        return array_merge($doc, SimpleAnnotationParser::methodComment($comments));
    }
}