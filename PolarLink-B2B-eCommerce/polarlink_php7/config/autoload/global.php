<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

 /* return array(
     'db' => array(
         'driver'         => 'Pdo',
         'dsn'            => 'mysql:dbname=zf2test;host=localhost',
         'driver_options' => array(
             PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
         ),
     ),
     'service_manager' => array(
         'factories' => array(
             'Zend\Db\Adapter\Adapter'
                     => 'Zend\Db\Adapter\AdapterServiceFactory',
         ),
     ),
 ); */

return array(
		'db' => array(
				'driver' => 'IbmDb2',
				'database' => '*LOCAL',
				'username' => 'OCTALSW',
				'password' => 'priyank5',

// 				'driver_options' => array(
// 						'i5_naming' => DB2_I5_NAMING_ON,
// 					//	'i5_libl' => 'PLINKDEV LXTSTF LXTSTUSRF QGPL ZENDSVR6'
// 						'i5_libl' => 'PLINKTST LXTSTF LXTSTUSRF QGPL ZENDSVR6'
// 				),
				'platform_options' => array('quote_identifiers' => false)
		),
		'service_manager' => array(
				'factories' => array(
						'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
				),
		),
		'emailAddresses' => array(
			'from' => 'polarlink@polarbev.com'
		),
		'allowed_order_attachmets_max_size' => array(
				'size' => '10'
		),

        // @see http://www.file-extensions.org/extensions/common-file-extension-list
		'allowed_file_attachments_white_list' =>
            array('pdf','docx','doc', 'dot', 'xls','xlsx','ppt','pptx','png','gif','jpg','jpeg','tiff',
                'psd','xml','txt','csv','odf','ods','odt','ott','pub','rtf','vsd', 'vsdx', 'wpd', 'wps', 'wri',
                'log', 'mdb', 'one'
            ),
);
