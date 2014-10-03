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

* @see
* @package
* @version
* @param
* @return

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
 *  string    name   dfsdjfshfdkjsdf
 *      sdfsdfisdfsdhfjks sdfsdfjshdkfj sdfsjdfhskdfj
 *      sdfsdfisdfsdhfjks sdfsdfjshdkfj sdfsjdfhskdfj
 *      sdfsdfisdfsdhfjks sdfsdfjshdkfj sdfsjdfhskdfj
 *  string   name.api   dfsdjfshfdkjsdf
 *      sdfsdfisdfsdhfjks sdfsdfjshdkfj sdfsjdfhskdfj
 *      sdfsdfisdfsdhfjks sdfsdfjshdkfj sdfsjdfhskdfj
 *      sdfsdfisdfsdhfjks sdfsdfjshdkfj sdfsjdfhskdfj
 *  string   name.api0   dfsdjfshfdkjsdf
 *      sdfsdfisdfsdhfjks sdfsdfjshdkfj sdfsjdfhskdfj
 *      sdfsdfisdfsdhfjks sdfsdfjshdkfj sdfsjdfhskdfj
 *      sdfsdfisdfsdhfjks sdfsdfjshdkfj sdfsjdfhskdfj
 *  string   name.api1   dfsdjfshfdkjsdf
 *      sdfsdfisdfsdhfjks sdfsdfjshdkfj sdfsjdfhskdfj
 *  string name.api1.deeper {1,10} dfsdjfshfdkjsdf
 *  int      image       abu abu abuubububububub
 *      fgdfgdjfglkdfjgdlfkgd
 *      dfgdjfgdklfjgdkg
 */
</pre>

rm -rf output && php anno.phar --class_ns=Namespace testFiles
