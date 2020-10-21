<?php
$incl_path = '.:/usr/local/zendsvr6/var/libraries/Zend_Framework_2/default/library:' . ini_get('include_path');
ini_set('include_path', $incl_path);

// require_once autoloader.php to register autoloaders
require_once 'Zend/Loader/StandardAutoloader.php';

$loader = new Zend\Loader\StandardAutoloader(
    array(
        // Automatically register all ZF classes
        'autoregister_zf' => true
    )
);
	
$loader->register();

?>