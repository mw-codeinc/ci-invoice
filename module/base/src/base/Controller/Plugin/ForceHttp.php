<?php

namespace base\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Uri\Http as HttpUri;

class ForceHttp extends AbstractPlugin
{
    public function __invoke()
    {
        $request = $this->getController()->getRequest();

        if ('http' === $request->getUri()->getScheme()) {
            return;
        }

        // Not secure, create full url
        $plugin = $this->getController()->url();
        $url    = $plugin->fromRoute(null, array(), array(
            'force_canonical' => true,
        ), true);

        $url = new HttpUri($url);
        $url->setScheme('http');

        return $this->getController()->redirect()->toUrl($url);
    }
}