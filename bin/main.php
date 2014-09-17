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
    'method_ns:',
    'verbose',
    'help'
);

$options = SimpleAnnotation::getOptions(getopt(null, $opt_long));


if (isset($options['help']))
{
    exit(
    "PHP DocBlock Annotation Document generator. Allows Multiline and docs written in MD\n"
    ."\n"
    ."Options:\n"
    ."\n"
    ." --bootstrap : boostrap script for the target\n"
    ." --output : target output directory\n"
    ." --class_ns : namespace of classes that will be covered.\n"
    ." --method_ns : namespace of methods that will be covered.\n"
    ." --verbose : extra messages\n"
    ." --help : displays help\n\n");
}

if (empty($options['output']))
{
    $options['output'] = 'output';
}

$options['output'] = rtrim($options['output'], DIRECTORY_SEPARATOR);

if (!is_dir($options['output']) && !mkdir($options['output'], 0755, true))
{
    SimpleAnnotation::message('Cannot create `' . $options['output'] . '`.', true);
}

$options['output'] .= DIRECTORY_SEPARATOR;

$anno = new SimpleAnnotation($options);

$docs = $anno->filter(SimpleAnnotation::getFiles($options[0]));

if (empty($docs))
{
    SimpleAnnotation::message('Cannot process anything.', true);
}

$packages = array();

$data_folder = $options['output'] . 'data';
if (!is_dir($data_folder) && !mkdir($data_folder, 0755, true))
{
    SimpleAnnotation::message('Cannot create `' . $data_folder . '`.', true);
}

$data_folder .= DIRECTORY_SEPARATOR;

do
{
    $doc = array_shift($docs);
    $json_doc = json_encode($doc);
    
    $filename = md5($json_doc) . '.json';
    $file = $data_folder . $filename;
    file_put_contents($file, $json_doc);
    $packages[] = array('package' => $doc['package'], 'href' => $filename);

} while(!empty($docs));

file_put_contents($options['output'] . 'manifest.json', json_encode($packages));