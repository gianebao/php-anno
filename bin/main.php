#!/usr/bin/env php
<?php
/**
 * Requires Box [http://box-project.org] for packaging.
 */

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'SimpleAnnotation.php';

$opt_long = array(
    'bootstrap:',
    'output:',
    'class_ns:',
    'method_ns:'
);

$options = SimpleAnnotation::getOptions(getopt(null, $opt_long));

$anno = new SimpleAnnotation($options);



$anno->filter(SimpleAnnotation::getFiles($options[0]));