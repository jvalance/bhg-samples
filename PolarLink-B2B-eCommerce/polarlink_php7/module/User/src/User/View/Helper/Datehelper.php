<?php namespace User\View\Helper;
use Zend\View\Helper\AbstractHelper;
 
class Datehelper extends AbstractHelper
{
    public function __invoke($dateString, $format)
    {
        if (! is_string($dateString)){
            return ;
        }
 
       if(!empty($dateString) && !empty($format)){
       	$fn = new \DateTime();
				$time = $fn::createFromFormat('Y-m-d-H.i.s.u', $dateString)->format($format);
				 return  $time; 
       }
 
        return ;
    }
} ?>