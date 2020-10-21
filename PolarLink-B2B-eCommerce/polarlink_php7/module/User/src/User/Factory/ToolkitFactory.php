<?php

namespace User\ServiceFactory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use  IbmiToolkit\ToolkitApi\ToolkitService;

class ToolkitFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dbAdapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
        $dbAdapter->getDriver()
            ->getConnection()
            ->connect();
        $dbConn = $dbAdapter->getDriver()
            ->getConnection()
            ->getResource();
        //require_once("ToolkitService.php");
        // pass database connection into toolkit instantiation
        $tk = \ToolkitService::getInstance($dbConn, DB2_I5_NAMING_ON); // cheating here by hard-coding naming mode
        $tk->setOptions(array(
            'stateless' => true
        ));
        return $tk;
    }
}