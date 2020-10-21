<?php
namespace User\Helper;

/**
 *
 * Business Address Helper
 *
 * @author Jaziel Lopez <juan.jaziel@gmail.com>
 *
 */
class Address
{
    
    /**
     * Format business address 
     * 
     * Beware $address keys should match key names as in Customer Ship To
     * 
     * @see:
     * User\Helper\Customer (class)
     * getDefaultShipToAddress (method)
     * 
     * @param array $address
     * @return string
     */
    public static function format($address = array()){
        
        if(empty($address)) {
            
            return 'No Default Ship-To Selected';
        }
        
        $formattedAddress = '';
        
        $parseAddresss = [
            
            'shipId' => $address['ST_NUM'],
            
            'customerId' => $address['ST_CUST'],
            
            'name' => preg_replace('/\s{2,}/', ' ', $address['ST_NAME']),
            
            'atn' => trim($address['ST_ATTN']),
            
            'address1' => trim($address['ST_ADR1']),
            
            'address2' => trim($address['ST_ADR2']),
            
            'address3' => trim($address['ST_ADR3']),
            
            'zip' => trim($address['ST_ZIP']),
            
            'state' => trim($address['ST_STATE'])
            
        ];
        
        $streetAddress = [];
        
        array_push($streetAddress, $parseAddresss['address1']);
        
        if(!empty($parseAddresss['address2'])){
            
            array_push($streetAddress, $parseAddresss['address2']);
        }
        
        if(!empty($parseAddresss['customerId']) && !empty($parseAddresss['customerId'])):
        
            $formattedAddress .= join('/', 
                
                [
                    $parseAddresss['customerId'], 
                    
                    $parseAddresss['shipId']
                    
                ]
            );
            
            $formattedAddress .= PHP_EOL;
            
       endif;
        
        
        if(!empty($parseAddresss['name'])):
        
            $formattedAddress .= join(',', 
                
                [
                    $parseAddresss['name']
                    
                ]
            );
            
            $formattedAddress .= PHP_EOL;

        endif;
         
        if(!empty($parseAddresss['atn'])):
             
            $formattedAddress .= 'Attn: ' . join(',',
                
                [
                    $parseAddresss['atn']
                    
                ]
            );
        
            $formattedAddress .= PHP_EOL;
            
        endif;
         
        $formattedAddress .= join(', ', $streetAddress);
        
        $formattedAddress .= PHP_EOL;
         
        $formattedAddress .= join(', ', 
            
            [
                
                $parseAddresss['address3'], 
                
                $parseAddresss['state'], 
                
                $parseAddresss['zip']
                
            ]
        );
        
        $formattedAddress .= PHP_EOL;
        
        return $formattedAddress;
    }
}

