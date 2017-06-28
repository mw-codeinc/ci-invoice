<?php

namespace base\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class Encryption extends AbstractPlugin
{
    public function __invoke($value)
    {
        $options = array(
            'cost' => 11,
            'salt' => mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_RAND)
        );

        return password_hash($value, PASSWORD_BCRYPT, $options);
    }
}