<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ConnectionRequest;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="settings")
     */
    public function settingsAction(Request $request)
    {
        /** @var \AppBundle\Entity\User $user */
        $user = $this->getUser();
        $form = $this->createForm(
            new UserType(),
            $user,
            [
                'validation_groups' => ['settings'],
                'manager' => $this->getDoctrine()->getManager(),
                'locale' => $request->getLocale(),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if (false === $this->get('security.authorization_checker')->isGranted('ROLE_COMPLETE_USER')) {
                $user->addRole('ROLE_COMPLETE_USER');
                $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
                $this->getSecurityContext()->setToken($token);
                $this->addFlash(
                    'info',
                    'Nu har vi registrerat dina uppgifter, och kommer att höra av oss så fort vi har hittat en ny
                    matchning.'
                );
                $connectionRequest = new ConnectionRequest();
                $connectionRequest->setUser($user);
                $connectionRequest->setCity($form->get('city')->getData());
                $connectionRequest->setWantToLearn($user->getWantToLearn());
                $em->persist($connectionRequest);
            }
            $em->persist($user);
            $em->flush();

            return $this->redirect($this->generateUrl('homepage'));
        }

        $parameters = [
            'form' => $form->createView(),
        ];

        return $this->render('user/settings.html.twig', $parameters);
    }

    /**
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    protected function getSecurityContext()
    {
        return $this->get('security.context');
    }
}
