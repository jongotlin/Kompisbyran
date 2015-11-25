<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\User;
use AppBundle\Form\AdminUserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("admin/users")
 */
class UserController extends Controller
{
    /**
     * @Route("/{id}", name="admin_user", defaults={"id": null})
     */
    public function viewAction(Request $request, User $user)
    {
        $form = $this->createForm(
            new AdminUserType(),
            $user,
            [
                'manager' => $this->getDoctrine()->getManager(),
                'locale' => $request->getLocale(),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_start'));
        }

        $parameters = [
            'form' => $form->createView(),
        ];

        return $this->render('admin/user/view.html.twig', $parameters);
    }
}
