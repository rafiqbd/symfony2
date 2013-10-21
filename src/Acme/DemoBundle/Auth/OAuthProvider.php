<?php
namespace Acme\DemoBundle\Auth;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Acme\DemoBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Doctrine\ORM\EntityManager;

class OAuthProvider extends OAuthUserProvider
{
    protected $session, $doctrine, $admins,$service_container;
    public function __construct($session, $doctrine, $service_container)
    {
        $this->session = $session;
        $this->doctrine = $doctrine;
        $this->container = $service_container;
    }
    public function loadUserByUsername($username)
    {
        $qb = $this->doctrine->getManager()->createQueryBuilder();
        $qb->select('u')
            ->from('AcmeDemoBundle:User', 'u')
            ->where('u.facebookId = :fbId')
            ->setParameter('fbId', $username)
            ->setMaxResults(1);
        $result = $qb->getQuery()->getResult();
        if (count($result)) {
            return $result[0];
        } else {
            return new User();
        }
    }
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        //Data from Facebook response
        $facebookId = $response->getUsername(); /* An ID like:67089347589 */
        $email = $response->getEmail();
        $nickname = $response->getNickname();
        $realname = $response->getRealName();
        $avatar = $response->getProfilePicture();

        var_dump($response);

        exit;

        //set data in session
        $this->session->set('email', $email);
        $this->session->set('nickname', $nickname);
        $this->session->set('realname', $realname);
        $this->session->set('avatar', $avatar);
        //Check if this Google user already exists in our app DB
        $qb = $this->doctrine->getManager()->createQueryBuilder();
        $qb->select('u')
            ->from('AcmeDemoBundle:User', 'u')
            ->where('u.facebookId = :fbId')
            ->setParameter('fbId', $facebookId)
            ->setMaxResults(1);
        $result = $qb->getQuery()->getResult();

        return $this->loadUserByUsername($response->getUsername());
    }
    public function supportsClass($class)
    {
        return $class === 'Acme\\DemoBundle\\Entity\\User';
    }
}
