<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ConnectionRequest;
use AppBundle\Form\ConnectionRequestType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $activeRequest = false;

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            if (false === $this->get('security.authorization_checker')->isGranted('ROLE_COMPLETE_USER')) {
                return $this->redirect($this->generateUrl('settings'));
            }

            $user           = $this->getUser();
            $em             = $this->getDoctrine()->getManager();

            /** @var \AppBundle\Entity\User $user */
            $connectionRequest = new ConnectionRequest();
            $connectionRequest->setUser($user);
            $connectionRequest->setWantToLearn($user->getWantToLearn());
            $connectionRequest->setType($user->getType());
            $form = $this->createForm(
                new ConnectionRequestType(),
                $connectionRequest,
                ['validation_groups' => ['newConnectionRequest']]
            );

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $activeRequest  = $em->getRepository('AppBundle:ConnectionRequest')->hasActiveRequest($user);

                if (!$activeRequest) {
                    $em->persist($connectionRequest);
                    $em->flush();

                    $this->get('app.user_mailer')->sendRegistrationWelcomeEmailMessage($user);

                    return $this->redirect($this->generateUrl('homepage'));
                }
            }

            $parameters = [
                'form'          => $form->createView(),
                'activeRequest' => $activeRequest
            ];
        } else {
            $parameters = [
                'activeRequest' => $activeRequest
            ];
        }

        return $this->render('default/index.html.twig', $parameters);
    }
}
