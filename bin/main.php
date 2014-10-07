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
    'include_readme',
    'help'
);

$options = SimpleAnnotation::getOptions(getopt(null, $opt_long));

if (empty($options) && file_exists('anno.json'))
{
    $options = json_decode(file_get_contents('anno.json'), true);
    
    $options = array_merge($options, $options['target']);
    unset($options['target']);
}

if (isset($options['help']) || empty($options))
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

$docs = array();

$i = 0;

do
{
    $docs = array_merge($docs, $anno->filter(SimpleAnnotation::getFiles($options[$i])));
    
    $readme = $options[$i] . DIRECTORY_SEPARATOR . 'readme.md';
    
    if (isset($options['include_readme']) && is_dir($options[$i]) && file_exists($readme) && '' != trim(file_get_contents($readme)))
    {
        SimpleAnnotation::message('Processing `' . $readme . '`.');
        $docs[] = array(
            'package' => array(
                strtolower(str_replace(array('_', '-'), '/', str_replace(dirname($options[$i]) . DIRECTORY_SEPARATOR, '', $options[$i])))
            ),
            'name' => str_replace(dirname($options[$i]) . DIRECTORY_SEPARATOR, '', $options[$i]),
            'description' => file_get_contents($readme)
        );
    }
    
} while (!empty($options[++$i]));

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
    $packages[] = array('package' => $doc['package'], 'name' => $doc['name'], 'href' => $filename);
} while(!empty($docs));

file_put_contents($options['output'] . 'manifest.json', json_encode($packages));