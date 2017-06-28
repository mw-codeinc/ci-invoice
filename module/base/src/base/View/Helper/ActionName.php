<?php

namespace base\View\Helper;

use Zend\View\Helper\AbstractHelper;

class ActionName extends AbstractHelper
{

protected $routeMatch;

    public function __construct($routeMatch)
    {
        $this->routeMatch = $routeMatch;
    }

    public function __invoke()
    {
        if ($this->routeMatch) {
            $action = $this->routeMatch->getParam('action', 'index');
            return $action;
        }
    }
}
