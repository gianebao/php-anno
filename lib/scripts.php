<?php

function message ($message, $is_error = false)
{
    if ($is_error)
    {
        echo '[ERROR] ';
    }

    echo $message . "\n";

    if ($is_error)
    {
        exit(1);
    }
}

function get_options(array $options)
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

function load_files($path)
{
    if (is_dir($path))
    {
        var_dump(realpath($path));
        $directory = new RecursiveDirectoryIterator(realpath($path));
        $iterator = new RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

        return array_keys(iterator_to_array($regex));

    }
    elseif (is_file($path))
    {
        return array(realpath($path));
    }

    message('File or Directory does not exist.', true);
}

function parse()
{

}