<?php
/**
 * Zend_View_Helper
 * 
 * @author Jaziel Lopez
 * @version patch
 */
namespace Zend_View_Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * EnvironmentHelper  helper
 *
 * @uses viewHelper env
 */
class EnvironmentHelper extends AbstractHelper 
{

    
    public function __invoke()
    {
        // 
        
        return 'environment';
    }
}
