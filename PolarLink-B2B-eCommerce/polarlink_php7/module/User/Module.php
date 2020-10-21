<?php

namespace User;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
// use Zend\ModuleManager\Feature\FormElementProviderInterface;

use User\Model\PlinkUser;
use User\Model\PlinkUserTable;
use User\Model\PlinkCustomer;
use User\Model\PlinkCustomerTable;
use User\Model\PlinkAnnouncements;
use User\Model\PlinkAnnouncementsTable;

use User\Model\OrderAttachment;
use User\Model\OrderAttachmentTable;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface {

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                	 'Lib' => __DIR__ . '/../../vendor/Lib'
                ),
            ),
        );
    }

             public function getViewHelperConfig() {
             	return array(
             			'invokables' => array(
             					'formmulticheckbox' => 'User\Form\View\Helper\FormMultiCheckbox',
             			),
             			'factories' => array(
             					'date_helper' => function($sm) {
             						$helper = new View\Helper\Datehelper ;
             						return $helper;
             					}
             			)
             			
             	);
             }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }
    
    
    // Add this method:
    public function getServiceConfig() {
        return array(
            'factories' => array(
                
            	'User\Model\PlinkUserTable' => function($sm) {
                    $tableGateway = $sm->get('PlinkUserTableGateway');
                    $table = new PlinkUserTable($tableGateway);
                    return $table;
                },
                'PlinkUserTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new PlinkUser());
                    return new TableGateway('PLINK_USER', $dbAdapter, null, $resultSetPrototype);
                },
                'User/Model/PlinkUser' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $user = new PlinkUser();
                    $user->setDbAdapter($dbAdapter);
                    return $user;
                },
                
                
                
                'User\Model\PlinkCustomerTable' => function($sm) {
                    $tableGateway = $sm->get('PlinkCustomerTableGateway');
                    $table = new PlinkCustomerTable($tableGateway);
                    return $table;
                },
                'PlinkCustomerTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new PlinkCustomer());
                    return new TableGateway('PLINK_CUSTOMER', $dbAdapter, null, $resultSetPrototype);
                },
                'User/Model/PlinkCustomer' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $user = new PlinkCustomer();
                    $user->setDbAdapter($dbAdapter);
                    return $user;
                },
                
                /*added712016*/
                
                'User\Model\OrderAttachmentTable' => function($sm) {
                	$tableGateway = $sm->get('OrderAttachmentTableGateway');
                	$table = new OrderAttachmentTable($tableGateway);
                	return $table;
                },
                'OrderAttachmentTableGateway' => function ($sm) {
                	$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                	$resultSetPrototype = new ResultSet();
                	$resultSetPrototype->setArrayObjectPrototype(new OrderAttachment());
                	return new TableGateway('PLINK_ATTACHMENT', $dbAdapter, null, $resultSetPrototype);
                },
                'User/Model/OrderAttachment' => function ($sm) {
                	$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                	$user1 = new OrderAttachment();
                	$user1->setDbAdapter($dbAdapter);
                	return $user1;
                },
                
                /*End*/
                
                
                
                
                
                'User\Model\PlinkAnnouncementsTable' => function($sm) {
                	$tableGateway = $sm->get('PlinkAnnouncementsTableGateway');
                	$table = new PlinkAnnouncementsTable($tableGateway);
                	return $table;
                },
                'PlinkAnnouncementsTableGateway' => function ($sm) {
                	$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                	$resultSetPrototype = new ResultSet();
                	$resultSetPrototype->setArrayObjectPrototype(new PlinkAnnouncements());
                	return new TableGateway('PLINK_ANNOUNCEMENTS', $dbAdapter, null, $resultSetPrototype);
                },
                'User/Model/PlinkAnnouncements' => function ($sm) {
                	$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                	$user = new PlinkAnnouncements();
                	$user->setDbAdapter($dbAdapter);
                	return $user;
                },
                'User\Model\MyAuthStorage' => function ($sm) {
                    return new \User\Model\MyAuthStorage('polarlinks');
                },
                'AuthService' => function ($sm) {
                    // My assumption, you've alredy set dbAdapter
                    // and has users table with columns : user_name and pass_word
                    // that password hashed with md5
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $dbTableAuthAdapter = new DbTableAuthAdapter($dbAdapter, 'PLINK_USER', 'PLU_USER_ID', 'PLU_PASSWORD');

                    $authService = new AuthenticationService ();
                    $authService->setAdapter($dbTableAuthAdapter);
                    $authService->setStorage($sm->get('User\Model\MyAuthStorage'));

                    return $authService;
                }
            ),
        );
    }

}