<?php
namespace AppBundle\Tests\Phpunit\Extension;

use AppBundle\Entity\User;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

trait AuthenticationExtensionTrait
{
    /**
     * @var User
     */
    protected $authenticatedUser;

    /**
     * @param User $user
     * @param $role
     */
    protected function authenticateUser(User $user, $role)
    {
        if ($role) {
            if (is_array($role)) {
                foreach ($role as $loopedRole) {
                    $user->addRole($loopedRole);
                }
            } else {
                $user->addRole($role);
            }
        }

        $token = new UsernamePasswordToken(
            $user,
            'dummy',
            $providerKey = 'main'
        );
        $this->authenticateToken($token);
    }


    /**
     * @param TokenInterface $token
     */
    protected function authenticateToken(TokenInterface $token)
    {
        self::$client->getCookieJar()->set(new Cookie('MOCKSESSID', session_id()));
        $container = self::$client->getContainer();
        $dispatcher = $container->get('event_dispatcher');
        $session = $container->get('session');
        $listener = function () use ($dispatcher, $session, $token, &$listener) {
            $dispatcher->removeListener(KernelEvents::REQUEST, $listener);
            $session->set('_security_main', serialize($token));
        };
        $dispatcher->addListener(KernelEvents::REQUEST, $listener, 191);
        $this->authenticatedUser = $token->getUser();
    }
}
