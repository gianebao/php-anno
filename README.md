php-anno
========

PHP DocBlock Annotation Document generator. Allows Multiline and docs written in MD

Options:

* --bootstrap : boostrap script for the target
* --output : target output directory
* --class_ns : namespace of classes that will be covered.
* --method_ns : namespace of methods that will be covered.
* --verbose : extra messages
* --help : displays help

Supported Annotations:

* @name
* @package
* @param
* @return
* @see
* @version

API Special Format:

To generate an API Request Param

@param   datatype      name       {min}      fsdfsdfsfsdfsd
@param   datatype      name       {min, max} fsdfsdfsfsdfsd

set min to 0 to indicate it as `optional`

To generate an API Response
<pre>
/**
 * This is a demo for APIResponse object alias
 *
 * @param string banana {1,19} sdfs fgkjdfghdg jdhfghdkgjs fgdjfhgkdgh
 *
 * @return API Responds:
 *  string    param1      this is the definition
 *      which can be multiline if you write
 *      it like this. You can also use it to create:
 *      `Markdowns`
 *  string   param1.api   this means this is a subgroup of *param1*
 *  string   param1.api0  this is a subgroup of api at index 0
 *  string   param1.api1  this is a subgroup of api at index 1
 *  string   param.api1.deeper this is an even deeper subgroup
 *  int      image        and any variable
 */
</pre>

rm -rf output && php anno.phar --class_ns=Namespace testFiles
