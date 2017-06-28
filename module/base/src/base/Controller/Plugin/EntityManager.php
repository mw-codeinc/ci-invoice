<?php

namespace base\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class EntityManager extends AbstractPlugin
{
    public function __invoke()
    {
        return $this->getController()->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    }
}