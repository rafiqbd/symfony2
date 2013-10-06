<?php

namespace Acme\DemoBundle\Provider;

use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Acme\DemoBundle\Entity\User;
use Acme\DemoBundle\Provider\OAuthUser;

class Provider extends OAuthUserProvider
{
    protected $session, $doctrine, $admins;
    public function __construct($session, $doctrine, $admins) {
        $this->session = $session;
        $this->doctrine = $doctrine;
        $this->admins = $admins;
    }

    public function loadUserByUsername($username)
    {
        return new OAuthUser($username, $this->isUserAdmin($username)); //look at the class below
    }

    private function isUserAdmin($nickname)
    {
        return in_array($nickname, $this->admins);
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        //data from facebook response
        $facebook_id = $response->getUsername();
        $nickname = $response->getNickname();
        $realname = $response->getRealName();
        $email    = $response->getEmail();
        $avatar   = $response->getProfilePicture();
var_dump($facebook_id);
        exit;
        //set data in session
        $this->session->set('nickname', $nickname);
        $this->session->set('realname', $realname);
        $this->session->set('email', $email);
        $this->session->set('avatar', $avatar);

        //get user by fid
        $qb = $this->doctrine->getManager()->createQueryBuilder();
        $qb ->select('u.id')
            ->from('AcmeDemoBundle:User', 'u')
            ->where('u.fid = :fid')
            ->setParameter('fid', $facebook_id)
            ->setMaxResults(1);
        $result = $qb->getQuery()->getResult();

        //add to database if doesn't exists
        if ( !count($result) ) {
            $User = new User();
            $User->setCreatedAt(new \DateTime());
            $User->setNickname($nickname);
            $User->setRealname($realname);
            $User->setEmail($email);
            $User->setAvatar($avatar);
            $User->setFID($facebook_id);

            $em = $this->doctrine->getManager();
            $em->persist($User);
            $id = $em->flush();
        } else {
            $id = $result[0]['id'];
        }

        //set id
        $this->session->set('id', $id);

        //@TODO: hmm : is admin
        if ($this->isUserAdmin($nickname)) {
            $this->session->set('is_admin', true);
        }

        //parent:: returned value
        return $this->loadUserByUsername($response->getNickname());
    }

    public function supportsClass($class)
    {
        return $class === 'Acme\\DemoBundle\\Provider\\OAuthUser';
    }
}