<?php
return array(
     'controllers' => array(
         'invokables' => array(
             
             'User\Controller\Item' => 'User\Controller\ItemController',
             
             'User\Controller\User' => 'User\Controller\UserController',
             
             'User\Controller\Customer' => 'User\Controller\CustomerController'
         ),
     ),
     // The following section is new and should be added to your file
     'router' => array(
         'routes' => array(
              // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            
             'customer' => array(
                 
                 'type'    => 'literal',
                 
                 'options' => array(
                     
                     'route'    => '/customer[/]',
                     
                     'defaults' => array(
                         
                         'controller' => 'User\Controller\Customer',
                         
                         'action'     => 'index',
                     ),
                ),
                 
                 'may_terminate' => true,
                  
                 'child_routes' => array(
                     
                     'shipToEmails' => array(
                          
                         'type' => 'literal',
                          
                         'options' => array(
                              
                             'route' => 'customer/shipToEmails',
                              
                             'defaults' => array(
                 
                                 'action' => 'shipToEmails',
                             ),
                         ),
                         
                         'may_terminate' => true
                     ),
                 ),
            ),
             'item' => array(
                 
                 'type' => 'Literal',
                 
                 'options' => array(
                     
                     'route' => '/item',
                     
                     'defaults' =>  array(
                         
                         'controller' => 'User\Controller\Item',
                         
                         'action' => 'index'
                     ),
                 ),
                 
                 'may_terminate' => true,
                 
                 'child_routes' => array(
                     
                     'inquiry' => array(
                         
                         'type' => 'segment',
                         
                         'options' => array(
                             
                             'route' => '/inquiry',
                             
                             'defaults' => array(
                                 
                                 'action' => 'inquiry',
                             ),
                         ),
                         
                         'may_terminate' => true
                     ),
                     
                     'updateShipTo' => array(
                          
                         'type' => 'segment',
                          
                         'options' => array(
                              
                             'route' => '/ship-to',
                              
                             'defaults' => array(
                                  
                                 'action' => 'updateShipTo',
                             ),
                         ),
                          
                         'may_terminate' => true
                     ),
                     
                     'itemInquirySearch' => array(
                          
                         'type' => 'segment',
                          
                         'options' => array(
                              
                             'route' => '/search',
                              
                             'defaults' => array(
                                  
                                 'action' => 'itemInquirySearch',
                             ),
                         ),
                          
                         'may_terminate' => true
                     ),
                     
                     'itemInquiryFilterResults' => array(
                     
                         'type' => 'segment',
                     
                         'options' => array(
                     
                             'route' => '/search/filter',
                     
                             'defaults' => array(
                     
                                 'action' => 'itemInquiryFilterResults',
                             ),
                         ),
                     
                         'may_terminate' => true
                     ),
                     
                     'itemInquiryResetFilterResults' => array(
                          
                         'type' => 'segment',
                          
                         'options' => array(
                              
                             'route' => '/search/filter/reset',
                              
                             'defaults' => array(
                                  
                                 'action' => 'itemInquiryResetFilterResults',
                             ),
                         ),
                          
                         'may_terminate' => true
                     ),
                 )
             ),
            'user' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/user',
                    'defaults' => array(
                        '__NAMESPACE__' => 'User\Controller',
                        'controller'    => 'User',
                        'action'        => 'login',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                'controller' => 'User\Controller\User',
                         'action'     => 'login',
                            ),
                        ),
                    ),
                	'index' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/index',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'index',
                						),
                				),
                				'may_terminate' => true,
                		),
                		'logout' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/logout',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'logout',
                						),
                				),
                				'may_terminate' => true,
                		),
                    
                    'placeOrder' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/place-order',
                            'defaults' => array(
                                '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'user',
                                'action' => 'placeOrder',
                            ),
                        ),
                        'may_terminate' => true
                    ),
                    
                    'currentOrder' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/current-order[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'user',
                                'action' => 'currentOrder',
                            ),
                        ),
                        'may_terminate' => true
                    ),
                    'itemSearch' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/item-search',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'user',
                                'action' => 'itemSearch',
                            ),
                        ),
                        'may_terminate' => true,
                    ),
                		'itemSearchAjax' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/item-search-ajax',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'itemSearchAjax',
                						),
                				),
                				'may_terminate' => true,
                		),
                		'orderHistory' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/order-history',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'orderHistory',
                						),
                				),
                				'may_terminate' => true,
                		),
                        'exportOrderHistory' => array(
                            
                            'type' => 'segment',
                            'options' => array(
                                'route' => '/export-order-history',
                                'constraints' => array(
                                    'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    'id' => '[0-9]+',
                                ),
                                'defaults' => array(
                                    '__NAMESPACE__' => 'User\Controller',
                                    'controller' => 'user',
                                    'action' => 'exportOrderHistory',
                                ),
                            ),
                            'may_terminate' => false,
                        ),
                		'orderHistoryView' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/order-history-view/:orderNum[/]:orderNumToDisplay[/]',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'orderNum' => '[a-zA-Z0-9_-]+',
                								'orderNumToDisplay' => '[a-zA-Z0-9_-]+'
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'orderHistoryView',
                								'orderNum' => "",
                								'orderNumToDisplay' => ""
                						),
                				),
                				'may_terminate' => true,
                		),
                		'setMessage' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/set-message',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'setMessage',
                						),
                				),
                				'may_terminate' => true,
                		),
                		'orderCancel' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/order-cancel',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'orderCancel',
                						),
                				),
                				'may_terminate' => true,
                		),
                		'updateOrder' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/update-order',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'updateOrder',
                						),
                				),
                				'may_terminate' => true,
                		),
                		'updateOrderInline' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/update-order-inline',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'updateOrderInline',
                						),
                				),
                				'may_terminate' => true,
                		),
                		'deleteItem' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/delete-item',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'deleteItem',
                						),
                				),
                				'may_terminate' => true,
                		),
                    'orderHeader' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/order-header',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'user',
                                'action' => 'orderHeader',
                            ),
                        ),
                        'may_terminate' => true,
                    ),
                    'orderShipping' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/order-shipping',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'user',
                                'action' => 'orderShipping',
                            ),
                        ),
                        'may_terminate' => true,
                    ),
                    'substitutes' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/substitutes',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'user',
                                'action' => 'substitutes',
                            ),
                        ),
                        'may_terminate' => true,
                    ),
                		'itemSubstituteAjax' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/item-substitute-ajax',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'itemSubstituteAjax',
                						),
                				),
                				'may_terminate' => true,
                		),
                		'updateSubstitutes' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/update-substitutes',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'updateSubstitutes',
                						),
                				),
                				'may_terminate' => true,
                		),
                    'reviewOrder' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/review-order[/:tab]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+',
                            	'tab' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'user',
                                'action' => 'reviewOrder',
                            ),
                        ),
                        'may_terminate' => true,
                    ),
                		
                		
                		/*order confirmation added 2872016*/
                		'orderConfirmation' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/order-confirmation[/:tab]',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                								'tab' => '[a-zA-Z][a-zA-Z0-9_-]*',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'orderConfirmation',
                						),
                				),
                				'may_terminate' => true,
                		),
                		
                		/*END*/
                		
                		
                		
                    'confirm' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/confirm',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'user',
                                'action' => 'confirm',
                            ),
                        ),
                        'may_terminate' => true,
                    ),
                    'getUserDetail' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/getUserDetail',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'user',
                                'action' => 'getUserDetail',
                            ),
                        ),
                        'may_terminate' => true,
                    ),
                		'csrIndex' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/csr-index',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'csrIndex',
                						),
                				),
                				'may_terminate' => true,
                		),
                		'csrSelectCustomer' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/csr-select-customer',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'csrSelectCustomer',
                						),
                				),
                				'may_terminate' => true,
                		),
                		'csrAnnouncementSearch' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/csr-announcement-search',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'csrAnnouncementSearch',
                						),
                				),
                				'may_terminate' => true,
                		),
                		'csrAnnouncementDetail' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/csr-announcement-detail[/:type][/:id]',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'type' => '[a-zA-Z0-9_-]+',
                								'id' => '[a-zA-Z0-9_-]+'
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'csrAnnouncementDetail',
                								'type' => 'add',
                								'id' => ''
                						),
                				),
                				'may_terminate' => true,
                		),
                		'csrCustomerList' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/csr-customer-list',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[a-zA-Z0-9_-]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'csrCustomerList'
                						),
                				),
                				'may_terminate' => true,
                		),
                		'csrCustomerDetail' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/csr-customer-detail[/:type][/:id]',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'type' => '[a-zA-Z0-9_-]+',
                								'id' => '[a-zA-Z0-9_-]+'
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'csrCustomerDetail',
                								'type' => 'add',
                								'id' => ''
                						),
                				),
                				'may_terminate' => true,
                		),
                		'csrCustomerAdd' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/csr-customer-add',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[a-zA-Z0-9_-]+'
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'csrCustomerAdd',
                								'id' => ''
                						),
                				),
                				'may_terminate' => true,
                		),
                		'csrCustomerEdit' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/csr-customer-edit[/:id]',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[a-zA-Z0-9_-]+'
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'csrCustomerEdit',
                								'id' => ''
                						),
                				),
                				'may_terminate' => true,
                		),
                		'csrCustomerView' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/csr-customer-view[/:id]',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[a-zA-Z0-9_-]+'
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'csrCustomerView',
                								'id' => ''
                						),
                				),
                				'may_terminate' => true,
                		),
                		'csrCustomerDelete' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/csr-customer-delete[/:id]',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[a-zA-Z0-9_-]+'
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'csrCustomerDelete',
                								'id' => ''
                						),
                				),
                				'may_terminate' => true,
                		),
                		'csrUserList' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/csr-user-list',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[a-zA-Z0-9_-]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'csrUserList'
                						),
                				),
                				'may_terminate' => true,
                		),
                		'csrUserAdd' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/csr-user-add',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[a-zA-Z0-9_-]+'
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'csrUserAdd',
                								'id' => ''
                						),
                				),
                				'may_terminate' => true,
                		),
                		
                		
                		/* added 7/5/2016 */
                		
                		'OrderAttachment' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/order-attachment[/:id]',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[a-zA-Z0-9_-]+',
                								'tab' => '[a-zA-Z][a-zA-Z0-9_-]*',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'orderAttachment',
                								'id' => ''
                						),
                				),
                				'may_terminate' => true,
                		),
                		
                		
                		
                		
                		'orderAttachmentFileDownload' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/order-attachment-file-download',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'fileName' => '[a-zA-Z0-9_-]+',
                								'tab' => '[a-zA-Z][a-zA-Z0-9_-]*',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'orderAttachmentFileDownload',
                								'id' => ''
                						),
                				),
                				'may_terminate' => true,
                		),
                		
                		'deleteOrderAttachmentFile' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/delete-order-attachment-file',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'deleteOrderAttachmentFile',
                						),
                				),
                				'may_terminate' => true,
                		),
                		
                		
                		'updateOrderAttachmentDescription' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/update-order-attachment-description[/:id]',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'updateOrderAttachmentDescription',
                						),
                				),
                				'may_terminate' => true,
                		),
                		
                		
                		
                		
                		
                		
                		'dummyPricing' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/dummy-pricing[/:id]',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'dummyPricing',
                						),
                				),
                				'may_terminate' => true,
                		),
                		
                		
                		
                		
                		
                		/* END */
                		
                		'csrUserView' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/csr-user-view[/:id]',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[ A-Za-z0-9_%-]+'
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'csrUserView',
                								'id' => ''
                						),
                				),
                				'may_terminate' => true,
                		),
                		'csrUserEdit' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/csr-user-edit[/:id]',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[ A-Za-z0-9_%-]+'
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'csrUserEdit',
                								'id' => ''
                						),
                				),
                				'may_terminate' => true,
                		),
                		'csrUserDelete' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/csr-user-delete[/:id]',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[ A-Za-z0-9_%-]+'
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'csrUserDelete',
                								'id' => ''
                						),
                				),
                				'may_terminate' => true,
                		),
                		'csrUserDetail' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/csr-user-detail',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'type' => '[a-zA-Z]+',
                								'userId' => '[a-zA-Z0-9_-]+'
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'csrUserDetail',
                								'type' => 'add',
                								'userId' => '',
                						),
                				),
                				'may_terminate' => true,
                		),
                		'printPdf' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/print-pdf[/:id]',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[a-zA-Z0-9_-]+'
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'printPdf',
                								'id' => ''
                						),
                				),
                				'may_terminate' => true,
                		),
                		'sendMail' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/send-mail',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[a-zA-Z0-9_-]+'
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'sendMail'
                						),
                				),
                				'may_terminate' => true,
                		),
                		'getAnnouncementsAjax' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/get-announcement-ajax',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'customerId' => '[a-zA-Z0-9_-]+',
                								'shipTo' => '[a-zA-Z0-9_-]+'
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'getAnnouncementsAjax',
                								'customerId' => '',
                								'shipTo' => ''
                						),
                				),
                				'may_terminate' => true,
                		),
                		
                		'checkCustGroupValidation' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/check-cust-group-validation',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								//'customerId' => '[a-zA-Z0-9_-]+',
                								'csr_cust_group' => '[a-zA-Z0-9_-]+'
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'checkCustGroupValidation',
                								//'customerId' => '',
                								'csr_cust_group' => ''
                						),
                				),
                				'may_terminate' => true,
                		),
                		
                		
                		'getDefaultShipTo' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/get-default-ship-to',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								//'customerId' => '[a-zA-Z0-9_-]+',
                								'csr_cust_group' => '[a-zA-Z0-9_-]+'
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'getDefaultShipTo',
                								//'customerId' => '',
                								'csr_cust_group' => ''
                						),
                				),
                				'may_terminate' => true,
                		),
                		
                		
                		
                		
                		'ajaxSearchShipTo' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/ajax-search-ship-to',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'ajaxSearchShipTo',
                						),
                				),
                				'may_terminate' => true,
                		),
                		'csrCustomerLoadData' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/csr-customer-load-data',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'csrCustomerLoadData',
                						),
                				),
                				'may_terminate' => true,
                		),
                		'csrUserLoadData' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/csr-user-load-data',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'csrUserLoadData',
                						),
                				),
                				'may_terminate' => true,
                		),
                		'adminCustomerEdit' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/admin-customer-edit[/:id]',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[a-zA-Z0-9_-]+'
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'adminCustomerEdit',
                								'id' => ''
                						),
                				),
                				'may_terminate' => true,
                		),
                		'adminCustomerView' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/admin-customer-view[/:id]',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[a-zA-Z0-9_-]+'
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'adminCustomerView',
                								'id' => ''
                						),
                				),
                				'may_terminate' => true,
                		),
                		'adminCustomerLoadData' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/admin-customer-load-data',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'adminCustomerLoadData',
                						),
                				),
                				'may_terminate' => true,
                		),
                		'checkCustomerUser' => array(
                				'type' => 'segment',
                				'options' => array(
                						'route' => '/check-customer-user',
                						'constraints' => array(
                								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                								'id' => '[0-9]+',
                						),
                						'defaults' => array(
                								'__NAMESPACE__' => 'User\Controller',
                								'controller' => 'user',
                								'action' => 'checkCustomerUser',
                						),
                				),
                				'may_terminate' => true,
                		),
                    'checkAjaxCsrReviewOrder' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/check-csr-review-order',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                                
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'user',
                                'action' => 'checkAjaxCsrReviewOrder'
                            ),
                        ),
                        'may_terminate' => true,
                    ),
                ),
            ),
         /*    'user' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/user[/:action][/:id]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                         'id'     => '[0-9]+',
                     ),
                     'defaults' => array(
                         'controller' => 'User\Controller\User',
                         'action'     => 'login',
                     ),
                 ),
             ), */
         ),
     ),

     'view_manager' => array(
         'template_path_stack' => array(
             'user' => __DIR__ . '/../view',
         ),
     		'strategies' => array(
     				'ViewJsonStrategy',
     		),
//      		'view_helpers' => array(
//      				'invokables'=> array(
//      						'formmulticheckbox' => 'User\View\Helper\FormMultiCheckbox'
//      		)
//      		),
     ),
    
    'servicemanager' => array(
    'factories' => array(
        'navigation' => function($sm) {
            $navigation = new \Zend\Navigation\Service\DefaultNavigationFactory;
            $navigation = $navigation->createService($sm);

            return $navigation;
        }
    ),
),
 );