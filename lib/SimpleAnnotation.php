<?php

require __DIR__ . DIRECTORY_SEPARATOR . 'SimpleAnnotationParser.php';

class SimpleAnnotation {

    protected $_options = array();

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

        $this->_options = $options;
    }

    public static function message ($message, $is_error = false)
    {
        echo true === $is_error ? '[ERROR] ': '[MESSAGE] ';

        echo $message . "\n";

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
        $output = array();

        foreach ($files as $file)
        {
            SimpleAnnotation::message('Evaluating `' . $file .'`...');
            $declared_classes = get_declared_classes();
            include $file;
            $classes = array_diff(get_declared_classes(), $declared_classes);

            if (!empty($classes))
            {
                foreach ($classes as $class)
                {
                    $this->parseClass(new ReflectionClass($class), $output);
                }
            }
            else
            {
                $this->parseSimple($file, $output);
            }
        }

        return $output;
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

            $contents[] = SimpleAnnotationParser::methodComment($comment);
        }

        $doc['contents'] = $contents;

        $output[] = $doc;
    }

    public function parseClass(ReflectionClass $object, array & $output = array())
    {
        $doc = array();
        $ns = 'class_ns';

        $comments = $object->getDocComment();

        if (empty($comments))
        {
            SimpleAnnotation::message('Class `' . $object->getName() .'` ignored: No documents found.');
            return false;
        }

        $doc['name'] = trim(
            str_replace(
                array('\\', '_'), '/', !empty($this->_options[$ns])
                ? str_replace($this->_options[$ns], '', $object->getShortName())
                : $object->getShortName()
            ), '/');

        $doc = array_merge($doc, SimpleAnnotationParser::classComment($comments));

        if (empty($doc['package']) || !empty($doc['ignore']))
        {
            SimpleAnnotation::message('Class `' . $object->getName() .'` ignored: Implied or does not belong to a package.');
            return false;
        }

        $methods = $object->getMethods(ReflectionMethod::IS_PUBLIC);

        $method_docs = array();

        foreach ($methods as $method)
        {
            $this->parseMethod($method, $method_docs);
        }

        if (!empty($method_docs))
        {
            $doc['methods'] = $method_docs;
        }

        return $output[] = $doc;
    }

    public function parseMethod(ReflectionMethod $object, array & $output = array())
    {
        $doc = array();
        $ns = 'method_ns';

        $comments = $object->getDocComment();

        if (empty($comments))
        {
            SimpleAnnotation::message('Method `' . $object->getName() .'` ignored: No documents found.');
            return false;
        }

        $doc['name'] = trim(
            !empty($this->_options[$ns])
                ? str_replace($this->_options[$ns], '', $object->getShortName())
                : $object->getShortName(),
            '_');

        $output = array_merge($doc, SimpleAnnotationParser::methodComment($comments));
    }
}