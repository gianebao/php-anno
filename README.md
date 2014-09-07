php-anno
========

PHP DocBlock Annotation Document generator

Supported Annotations:

@see
@package
@version
@param
@return

API Special Format:

To generate an API Response

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


rm -rf output && php anno.phar --class_ns=Namespace testFiles