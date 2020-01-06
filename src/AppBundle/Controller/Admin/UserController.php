<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\User;
use AppBundle\Enum\FriendTypes;
use AppBundle\Form\AdminUserType;
use AppBundle\Form\ConnectionRequestType;
use AppBundle\Form\MunicipalityAdministratorType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("admin/users")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="admin_users")
     */
    public function indexAction()
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $users = $this->getUserRepository()->findAllWithCategoryJoinAssoc();
        $categories = $this->getCategoryRepository()->findAll();

        $parameters = [
            'users' => $users,
            'categories' => $categories,
        ];

        return $this->render('admin/user/index.html.twig', $parameters);
    }

    /**
     * @Route("/{id}", name="admin_user", requirements={"id": "\d+"})
     * @Method({"POST", "GET"})
     */
    public function viewAction(Request $request, User $user)
    {
        $form = $this->createForm(
            AdminUserType::class,
            $user,
            [
                'manager' => $this->getDoctrine()->getManager(),
                'locale' => $request->getLocale(),
                'translator' => $this->get('translator'),
                'newly_arrived_date' => $this->get('newly_arrived_date'),
                'authorization_checker' => $this->get('security.authorization_checker'),
            ]
        );

        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_start'));
        }


        $connectionRequestForm = null;
        if ($user->hasOpenConnectionRequest()) {
            $connectionRequest = $user->getOpenConnectionRequest();
            $connectionRequestForm = $this->createForm(ConnectionRequestType::class, $connectionRequest);
            $connectionRequestForm->handleRequest($request);

            if ($connectionRequestForm->isSubmitted() && $connectionRequestForm->isValid()) {
                $em->persist($connectionRequest);
                $em->flush();

                return $this->redirect($this->generateUrl('admin_start'));
            }
            $connectionRequestForm = $connectionRequestForm->createView();
        }

        $parameters = [
            'form' => $form->createView(),
            'user' => $user,
            'connectionRequestForm' => $connectionRequestForm,
            'startMunicipalities' => $this->get('municipality_repository')->findAllActiveStartMunicipalities(),
            'matchFamilyMunicipalities' => $this->get('municipality_repository')->findAllMatchFamilyMunicipalities(),
        ];

        return $this->render('admin/user/view.html.twig', $parameters);
    }

    /**
     * @Route("/{id}", name="admin_user_delete", requirements={"id": "\d+"})
     * @Method({"DELETE"})
     */
    public function deleteAction(User $user)
    {
        $this->get('user_manager')->softDelete($user);

        return new Response();
    }

    /**
     * @Route("/municipality-administrators", name="admin_municipality_administrators")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function municipalityAdministrators(Request $request)
    {
        $user = new User();
        $user->setType(FriendTypes::START);
        $form = $this->createForm(MunicipalityAdministratorType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('info', 'Användaren skapades');

            return $this->redirect($this->generateUrl('admin_municipality_administrators'));
        }


        $municipalityAdministrators = $this->getUserRepository()->findAllMunicipalityAdministrators();


        $parameters = [
            'municipalityAdministrators' => $municipalityAdministrators,
            'form' => $form->createView(),
        ];

        return $this->render('admin/user/municipalityAdministrators.html.twig', $parameters);
    }

    protected function getUserRepository()
    {
        return $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
    }

    protected function getCategoryRepository()
    {
        return $this->getDoctrine()->getManager()->getRepository('AppBundle:Category');
    }
}
