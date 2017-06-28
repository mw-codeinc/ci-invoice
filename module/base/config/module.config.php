<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
return array (
		'router' => array (
				'routes' => array (
						'home' => array (
								'type' => 'Zend\Mvc\Router\Http\Literal',
								'options' => array (
										'route' => '/',
										'defaults' => array (
												'controller' => 'base\Controller\Index',
												'action' => 'index'
										)
								)
						),
						// The following is a route to simplify getting started creating
						// new controllers and actions without needing to create a new
						// module. Simply drop new controllers in, and you can access them
						// using the path /base/:controller/:action
						'base' => array (
								'type' => 'Literal',
								'options' => array (
										'route' => '/',
										'defaults' => array (
												'__NAMESPACE__' => 'base\Controller',
												'controller' => 'Index',
												'action' => 'index' 
										) 
								),
								'may_terminate' => true,
								'child_routes' => array (
										'default' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '[:controller[/:action]]',
														'constraints' => array (
																'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
																'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
														),
														'defaults' => array () 
												) 
										)
								) 
						),
                        'login' => array(
                            'type' => 'Literal',
                            'options' => array(
                                'route' => '/login',
                                'defaults' => array(
                                    '__NAMESPACE__' => 'base\Controller',
                                    'controller' => 'Auth',
                                    'action' => 'login'
                                )
                            )
                        ),
                        'logout' => array(
                            'type' => 'Literal',
                            'options' => array(
                                'route' => '/logout',
                                'defaults' => array(
                                    '__NAMESPACE__' => 'base\Controller',
                                    'controller' => 'Auth',
                                    'action' => 'logout'
                                )
                            )
                        ),
                        'password-recovery' => array(
                            'type' => 'Literal',
                            'options' => array(
                                'route' => '/password-recovery',
                                'defaults' => array(
                                    '__NAMESPACE__' => 'base\Controller',
                                    'controller' => 'Auth',
                                    'action' => 'password-recovery'
                                )
                            )
                        ),
                        'password-reset' => array(
                            'type' => 'Segment',
                            'options' => array(
                                'route' => '/password-reset/[:email[/:token]]',
                                'constraints' => array(
                                    'email' => '[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+',
                                    'token' => '[a-zA-Z0-9_-]*'
                                ),
                                'defaults' => array(
                                    '__NAMESPACE__' => 'base\Controller',
                                    'controller' => 'Auth',
                                    'action' => 'password-reset'
                                )
                            )
                        ),
                        'registration' => array(
                            'type' => 'Literal',
                            'options' => array(
                                'route' => '/account/registration',
                                'defaults' => array(
                                    '__NAMESPACE__' => 'base\Controller',
                                    'controller' => 'Auth',
                                    'action' => 'registration'
                                )
                            )
                        ),
                        'activation' => array(
                            'type' => 'Segment',
                            'options' => array(
                                'route' => '/account/activation/[:email[/:token]]',
                                'constraints' => array(
                                    'email' => '[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+',
                                    'token' => '[a-zA-Z0-9_-]*'
                                ),
                                'defaults' => array(
                                    '__NAMESPACE__' => 'base\Controller',
                                    'controller' => 'Auth',
                                    'action' => 'activation'
                                )
                            )
                        ),
                        'resend-activation' => array(
                            'type' => 'Literal',
                            'options' => array(
                                'route' => '/account/resend-activation',
                                'defaults' => array(
                                    '__NAMESPACE__' => 'base\Controller',
                                    'controller' => 'Auth',
                                    'action' => 'resend-activation'
                                )
                            )
                        )
				) 
		),
		'service_manager' => array (
				'abstract_factories' => array (
						'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
						'Zend\Log\LoggerAbstractServiceFactory' 
				),
				'aliases' => array (
						'translator' => 'MvcTranslator' 
				) 
		),
		'translator' => array (
				'locale' => 'pl_PL',
				'translation_file_patterns' => array (
						array (
								'type' => 'gettext',
								'base_dir' => __DIR__ . '/../language',
								'pattern' => '%s.mo' 
						) 
				) 
		),
		'controllers' => array (
				'invokables' => array (
						'base\Controller\Index' 	=> 'base\Controller\IndexController',
                        'base\Controller\Auth' 	    => 'base\Controller\AuthController',
						'base\Controller\Invoice'   => 'base\Controller\InvoiceController',
                        'base\Controller\Service'   => 'base\Controller\ServiceController',
                        'base\Controller\Buyer'     => 'base\Controller\BuyerController'
				) 
		),
		'view_manager' => array (
				'display_not_found_reason' => true,
				'display_exceptions' => true,
				'doctype' => 'HTML5',
				'not_found_template' => 'base/error/404',
				'exception_template' => 'base/error/index',
				'template_map' => array (
						'base/layout' => __DIR__ . '/../view/layout/layout.phtml',
						'base/error/404' => __DIR__ . '/../view/error/404.phtml',
						'base/error/index' => __DIR__ . '/../view/error/index.phtml',
				),
				'template_path_stack' => array (
						__DIR__ . '/../view' 
				),
				'strategies' => array (
						'ViewJsonStrategy' 
				) 
		),
        'module_layouts' => array(
            'base' => 'base/layout',
        ),
        'module_error_layouts' => array(
            'base' => array(
                'not_found_template' => 'base/error/404',
                'exception_template' => 'base/error/index'
            )
        ),
        'view_helpers' => array(
            'invokables' => array(
                'phone_number' => 'base\View\Helper\PhoneNumberHelper'
            )
        ),
        'controller_plugins' => array(
            'invokables' => array(
                'forceHttp' => 'base\Controller\Plugin\ForceHttp',
                'forceHttps' => 'base\Controller\Plugin\ForceHttps',
                'getAuthService' => 'base\Controller\Plugin\AuthService',
                'getEntityManager' => 'base\Controller\Plugin\EntityManager',
                'convertAmountToWordsPl' => 'base\Controller\Plugin\ConvertAmountToWordsPl',
                'getExchangeRate' => 'base\Controller\Plugin\ParseExchangeRate',
                'getInvoiceNumber' => 'base\Controller\Plugin\CalculateInvoiceNumber',
                'encrypt' => 'base\Controller\Plugin\Encryption'
            )
        ),
		// Placeholder for console routes
		'console' => array (
				'router' => array (
						'routes' => array () 
				) 
		),
		'doctrine' => array (
				'authentication' => array (
						'orm_default' => array (
								'object_manager' => 'Doctrine\ORM\EntityManager',
								'identity_class' => 'base\Entity\Account',
								'identity_property' => 'email',
								'credential_property' => 'password',
								'credential_callable' => function (base\Entity\Account $account, $password) {
                                    if (password_verify($password, $account->getPassword()) && $account->isActive()) {
                                        return true;
                                    } else {
                                        return false;
                                    }
								}
						)
				),
				'driver' => array (
						'base_entities' => array (
								'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
								'cache' => 'array',
								'paths' => array (
										__DIR__ . '/../src/base/Entity'
								) 
						),
						'orm_default' => array (
								'drivers' => array (
										'base\Entity' => 'base_entities'
								) 
						) 
				) 
		)
);
