<?php

namespace Acme\DemoBundle\Provider;

use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser as HWIOAuthUser;

class OAuthUser extends HWIOAuthUser{
    private $isAdmin = false;
    public function __construct($username, $isAdmin = false)
    {
        parent::__construct($username);
        $this->isAdmin = $isAdmin;
    }

    public function getRoles()
    {
        $roles = array('ROLE_USER', 'ROLE_OAUTH_USER');

        if ($this->isAdmin) {
            array_push($roles, 'ROLE_SUPER_ADMIN');
        }

        return $roles;
    }
}