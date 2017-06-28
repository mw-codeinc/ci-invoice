<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace base;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;

class Module {
	
	public function onBootstrap(MvcEvent $e) {
		$eventManager = $e->getApplication ()->getEventManager ();
		$serviceManager = $e->getApplication()->getServiceManager();
		$viewModel = $e->getApplication()->getMvcEvent()->getViewModel();
		$moduleRouteListener = new ModuleRouteListener ();
		$moduleRouteListener->attach ( $eventManager );
		$app = $e->getApplication ();
		$evt = $app->getEventManager ();
		
		$evt->attach ( MvcEvent::EVENT_DISPATCH_ERROR, array (
				$this,
				'onDispatchError' 
		), 100 );
		
		$app->getServiceManager()->get('viewhelpermanager')->setFactory('getControllerName', function($sm) use ($e) {
			$viewHelper = new View\Helper\ControllerName($e->getRouteMatch());
			return $viewHelper;
		});
		
		$app->getServiceManager()->get('viewhelpermanager')->setFactory('getActionName', function($sm) use ($e) {
			$viewHelper = new View\Helper\ActionName($e->getRouteMatch());
			return $viewHelper;
		});

		$authService = $serviceManager->get('Zend\Authentication\AuthenticationService');
		$viewModel->userHasIdentity = $authService->hasIdentity();
		$viewModel->userIdentity = $authService->getIdentity();
	}
	
	public function getConfig() {
		return include __DIR__ . '/config/module.config.php';
	}
	
	public function getServiceConfig() {
		return array (
				'factories' => array (
						'Zend\Authentication\AuthenticationService' => function ($serviceManager) {
							return $serviceManager->get ('doctrine.authenticationservice.orm_default');
						}
				) 
		);
	}
	
	public function onDispatchError(MvcEvent $e) {
		$vm = $e->getViewModel ();
		$vm->setTemplate ( 'layout/404' );
	}
	
	public function getAutoloaderConfig() {
		return array (
				'Zend\Loader\StandardAutoloader' => array (
						'namespaces' => array (
								__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__ 
						) 
				) 
		);
	}
}
