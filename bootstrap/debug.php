<?php

// @fixed

function zf_dump($var, $label = null, $echo = true) 
{
//    debug_print_backtrace();
//    exit;
    
	$sapi = PHP_SAPI;
	// $escaper = new Escaper();


    // format the label
    $label = ($label===null) ? '' : rtrim($label) . ' ';
    // var_dump the variable into a buffer and keep the output
    ob_start();
    var_dump($var);
    $output = ob_get_clean();
    // neaten the newlines and indents
    $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
    if ($sapi == 'cli') {
        $output = PHP_EOL . $label
                . PHP_EOL . $output
                . PHP_EOL;
    } else {
        // if (null !== $escaper) {
        //     $output = $escaper->escapeHtml($output);
        // } elseif (!extension_loaded('xdebug')) {
        //     $output = $escaper->escapeHtml($output);
        // }
        $output = '<pre>'
                . $label
                . $output
                . '</pre>';
    }
    if ($echo) {
        echo $output;
    }
    return $output;

}

// prevent large object
// @todo recursive
function zf_dump_types($value, $label = null)
{
    $type = gettype($value);
    
    switch ($type) {
        case 'object':
            $value = get_class($value);
            break;
        case 'array':
            $array = array();
            foreach ($value as $k => $v) {
                $type = gettype($v);
                if ($type == 'object') $v = get_class($v);
                if ($type == 'array') $v = $type;
                $array[$k] = $v;
            }
            $value = $array;
            break;
    }
   
    zf_dump($value, $label);
}

function zf_dump_class($object, $label = null)
{
    if (!is_object($object)) return;
    
    $class = get_class($object);
    
    zf_dump($class, $label);
}

function zf_dump_methods($object, $label = null)
{
    if (!is_object($object)) return;
    
    $methods = get_class_methods($object);
    sort($methods);
    
    zf_dump($methods, $label);
}

function zf_dump_object_path($obj, $label = null)
{
    $r = new \ReflectionClass($obj);
    zf_dump($r->getFilename(), $label);
}

function zf_dump_path($obj, $label = null)
{
    zf_dump_object_path($obj, $label);
}

function zf_dump_object($obj, $label = null)
{
    $r = new \ReflectionClass($obj);
    zf_dump('class: ' . get_class($obj) . ', path: ' . $r->getFilename(), $label);
}

function zf_backtrace()
{
    echo '<pre>';
    debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    echo '</pre>';
}