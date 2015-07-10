#!/usr/bin/env php
<?php
/**
 * Requires Box [http://box-project.org] for packaging.
 */
define ('LIBDIR', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR);
define ('TEMPLATEDIR', LIBDIR . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR);
require LIBDIR . 'SimpleAnnotation.php';

$opt_long = array(
    'bootstrap:',
    'output:',
    'class_ns:',
    'method_ns:',
    'template:',
    'verbose',
    'help'
);

if (file_exists('anno.json'))
{
    $options = json_decode(file_get_contents('anno.json'), true);
    
    $options = array_merge($options, $options['target']);
    unset($options['target']);
}

$options = array_merge($options, SimpleAnnotation::getOptions(getopt(null, $opt_long)));

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
    ." --template : template file that can transform the data.\n"
    ." --verbose : extra messages\n"
    ." --help : displays help\n\n");
}

if (empty($options['output']))
{
    $options['output'] = 'output';
}

$anno = new SimpleAnnotation($options);

$docs = array();

$i = 0;

do
{
    $files = SimpleAnnotation::getFiles($options[$i]);
    
    foreach ($files as $file)
    {
        $pathInfo = pathinfo($file);
        if ('md' !== strtolower($pathInfo['extension']))
        {
            $docs = array_merge($docs, $anno->filter([$file]));
            continue;
        }
        
        SimpleAnnotation::message('Processing `' . $pathInfo['basename'] . '`.');
        $docs[] = array(
            'package' => array(
                strtolower(str_replace(array('_', '-'), '/', str_replace(dirname($pathInfo['dirname']) . DIRECTORY_SEPARATOR, '', $pathInfo['dirname'])))
            ),
            'type' => 'markdown',
            'name' => str_replace(array('_', '-'), '/', str_replace(dirname($pathInfo['filename']) . DIRECTORY_SEPARATOR, '', $pathInfo['filename'])),
            'description' => file_get_contents($file)
        );
    }
} while (!empty($options[++$i]));


for ($i = 0, $count = count($docs); $i < $count; $i ++)
{
    if (empty($docs[$docs[$i]['package'][0]]))
    {
        $docs[$docs[$i]['package'][0]] = [];
    }
    
    $docs[$docs[$i]['package'][0]][] = $docs[$i];
    unset($docs[$i]);
}

if (empty($docs))
{
    SimpleAnnotation::message('Cannot process anything.', true);
}

$options['output'] = rtrim($options['output'], DIRECTORY_SEPARATOR);

if (!is_file($options['output']) && !is_dir($options['output']) && !mkdir($options['output'], 0755, true))
{
    SimpleAnnotation::message('Cannot create `' . $options['output'] . '`.', true);
}

if (!empty($options['template']))
{
    require LIBDIR . 'TemplateHelper.php';
    
    if ('slate' == $options['template'])
    {
        $includes = $options['output'] . DIRECTORY_SEPARATOR . 'includes';
        if (!is_dir($includes) && !mkdir($includes, 0755, true))
        {
            SimpleAnnotation::message('Cannot create `' . $includes . '`.', true);
        }
        
        $slateTemplate = TEMPLATEDIR . 'slate' . DIRECTORY_SEPARATOR;

        $options['slate']['includes'] = [];
        
        $headers = [];
        if (!empty($options['slate']['headers']))
        {
            $headers = $options['slate']['headers'];
            unset($options['slate']['headers']);
        }
        
        foreach ($docs as $key => $items)
        {
            $header = empty($headers[$key]) ? ''
                : "\n * * * *\n# " . $headers[$key] . "\n\n";
            
            $file = $options['slate']['includes'][] = strtolower(str_replace(['\\', '/'], '-', $key));
            $file = $includes . DIRECTORY_SEPARATOR . '_' . $file . '.md';
            file_put_contents($file, SimpleAnnotation::getFromTemplate($items, $slateTemplate . 'includes.php', $header));
        }

        $manifestFile = 'index';
        
        if (!empty($options['slate']['manifest']))
        {
            $manifestFile = $options['slate']['manifest'];
            unset($options['slate']['manifest']);
        }
        
        file_put_contents(
            $options['output'] . DIRECTORY_SEPARATOR . $manifestFile . '.md',
            SimpleAnnotation::getFromTemplate(empty($options['slate']) ? []: [$options['slate']], $slateTemplate . 'index.php')
        );
        exit();
    }
}

$options['output'] = rtrim($options['output'], DIRECTORY_SEPARATOR);

if (!is_dir($options['output']) && !mkdir($options['output'], 0755, true))
{
    SimpleAnnotation::message('Cannot create `' . $options['output'] . '`.', true);
}

$options['output'] .= DIRECTORY_SEPARATOR;

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