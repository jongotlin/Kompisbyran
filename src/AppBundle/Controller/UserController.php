<?php

namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="settings")
     * @Method({"GET", "POST"})
     */
    public function settingsAction(Request $request)
    {
        /** @var \AppBundle\Entity\User $user */
        $user = $this->getUser();

        $validationGroups = ['settings'];
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_COMPLETE_USER')) {
            $validationGroups[] = 'registration';
        }

        $form = $this->createForm(
            new UserType(),
            $user,
            [
                'validation_groups' => $validationGroups,
                'manager' => $this->getDoctrine()->getManager(),
                'locale' => $request->getLocale(),
                'translator' => $this->get('translator'),
                'newly_arrived_date' => $this->get('newly_arrived_date'),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Ett eller flera fält är felaktigt ifyllda.');
        } elseif ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $sendEmail = false;
            if (false === $this->get('security.authorization_checker')->isGranted('ROLE_COMPLETE_USER')) {
                $user->addRole('ROLE_COMPLETE_USER');
                $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
                $this->getSecurityContext()->setToken($token);
                if (!$user->hasRole('ROLE_MUNICIPALITY')) {
                    $this->addFlash(
                        'info',
                        'Nu har vi registrerat dina uppgifter, och kommer att höra av oss så fort vi har hittat en ny
                         matchning.'
                    );

                    $sendEmail = true;
                }
                $this->addFlash('data', 'newUser');
            }
            if (is_null($user->isCanPlayInstrument())) {
                $user->setCanPlayInstrument(false);
            }
            if (is_null($user->isCanSing())) {
                $user->setCanSing(false);
            }
            if (is_null($user->isProfessionalMusician())) {
                $user->setProfessionalMusician(false);
            }
            if (is_null($user->isNewlyArrived())) {
                $user->setNewlyArrived(false);
            }

            $em->persist($user);
            if ($form->has('newConnectionRequest')) {
                $connectionRequest = $user->getNewConnectionRequest();
                $em->persist($connectionRequest);
            }
            $em->flush();

            if ($sendEmail) {
                $this->get('app.user_mailer')->sendRegistrationWelcomeEmailMessage($user);
            }

            return $this->redirect($this->generateUrl('homepage'));
        }

        $parameters = [
            'form' => $form->createView(),
            'startMunicipalities' => $this->get('municipality_repository')->findAllActiveStartMunicipalities(),
        ];

        return $this->render('user/settings.html.twig', $parameters);
    }

    /**
     * @Route("/", name="delete", options={"expose"=true})
     * @Method("DELETE")
     */
    public function deleteAction()
    {
        $this->get('user_manager')->softDelete($this->getUser());

        $this->get('security.token_storage')->setToken(null);
        $this->get('request')->getSession()->invalidate();

        return new Response();
    }

        /**
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    protected function getSecurityContext()
    {
        return $this->get('security.context');
    }
}
