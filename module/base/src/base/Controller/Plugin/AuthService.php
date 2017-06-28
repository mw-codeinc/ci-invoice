<?php

namespace base\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class AuthService extends AbstractPlugin
{
    public function __invoke()
    {
        return $this->getController()->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
    }
}