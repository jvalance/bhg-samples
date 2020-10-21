<?php
namespace User\Helper\Session;


use Zend\Session\Container;

/**
 * Inquiry Session Helper
 *  
 * @author Jaziel Lopez, <juan.jaziel@gamil.com>
 *        
 */
final class Inquiry
{
    /**
     * 
     * @var Inquiry
     */
    private static $instance;
    
    /**
     * 
     * @var Container
     */
    private $container;
    
    /**
     * Is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
     */
    private function __construct(){}
    
    /**
     * Prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone(){}
    
    /**
     * Prevent from being unserialized (which would create a second instance of it)
     */
    private function __wakeup(){}
    
    
    /**
     * Get an instance
     * 
     * @return \User\Helper\Session\Singleton
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            
            static::$instance = new static;
            
            static::$instance->container = new Container('sess_inquiry');
            
        }
    
       return static::$instance;
    }
    
    /**
     * Getter
     * 
     * @param unknown $key
     * @return mixed|NULL|\Zend\Session\Storage\StorageInterface
     */
    public static function get($key){
        
        
        return static::$instance->container->offsetGet($key);
    }
    
    /**
     * Setter
     * @param unknown $key
     * @param unknown $value
     */
    public static function set($key, $value){
        
        if(static::$instance->container->offsetExists($key)){
            
            static::$instance->container->offsetUnset($key);
        }
        
        static::$instance->container->offsetSet($key, $value);
        
    }
    
    /**
     * Delete
     * 
     * @param unknown $key
     */
    public static function delete($key){
        
        static::$instance->container->offsetUnset($key);
        
    }
    
    /**
     * Destroy
     */
    public static function destroy(){
        
        static::$instance->container->getManager()->destroy();
    }
}

