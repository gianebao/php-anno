#!/usr/bin/env php
<?php
/**
 * Requires Box [http://box-project.org] for packaging.
 */

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'scripts.php';

$opt_long = array(
    'bootstrap:'
);

$options = get_options(getopt(null, $opt_long));

if (!empty($options['bootstrap']))
{
    if (!is_file($options['bootstrap']))
    {
        message('boostrap must be a valid PHP executable.', true);
    }

    include $options['bootstrap'];
}



var_dump(load_files($options[0]));
/*
$class = new ReflectionClass('TestX');

var_dump($class->getDocComment());

$methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

foreach ($methods as $method)
{
    var_dump($method->getDocComment());
}
*/