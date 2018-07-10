<?php

namespace AppBundle\Controller;

use AppBundle\Form\UserType;
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
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            if (false === $this->get('security.authorization_checker')->isGranted('ROLE_COMPLETE_USER')) {
                return $this->redirect($this->generateUrl('settings'));
            }

            $user = $this->getUser();
            $em = $this->getDoctrine()->getManager();

            $form = $this->createForm(
                new UserType(),
                $user,
                [
                    'validation_groups' => ['settings', 'newConnectionRequest'],
                    'manager' => $this->getDoctrine()->getManager(),
                    'locale' => $request->getLocale(),
                    'add_connection_request' => true,
                    'translator' => $this->get('translator'),
                    'newly_arrived_date' => $this->get('newly_arrived_date'),
                ]
            );

            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $connectionRequest = $user->getNewConnectionRequest();
                    $em->persist($user);
                    $em->persist($connectionRequest);
                    $em->flush();

                    $this->addFlash('info', $this->get('translator')->trans('connection_request.created.flash'));

                    return $this->redirect($this->generateUrl('homepage'));
                } else {
                    $this->addFlash('error', $this->get('translator')
                        ->trans('connection_request.validation_failed.flash'));
                }
            }

            $newUser = false;
            foreach ($this->container->get('session')->getFlashBag()->get('data') as $message) {
                if ($message == 'newUser') {
                    $newUser = true;
                }
            }

            $parameters = [
                'form' => $form->createView(),
                'connectionRequest' =>  $this->get('connection_request_repository')->findOneOpenByUser($user),
                'startMunicipalities' => $this->get('municipality_repository')->findAllActiveStartMunicipalities(),
                'newUser' => $newUser,
            ];
        } else {
            $parameters = [];
        }

        return $this->render('default/index.html.twig', $parameters);
    }
}
