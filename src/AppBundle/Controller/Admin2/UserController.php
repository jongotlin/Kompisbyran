<?php

namespace AppBundle\Controller\Admin2;

use AppBundle\Entity\User;
use AppBundle\Form\AdminUserType;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Manager\UserManager;
use Symfony\Component\Form\FormFactoryInterface;
use AppBundle\Manager\ConnectionRequestManager;

/**
 * @Route("admin2/users")
 */
class UserController extends Controller
{
    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var ConnectionRequestManager
     */
    private $connectionRequestManager;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @InjectParams({
     *     "formFactory" = @Inject("form.factory")
     * })
     * @param UserManager $userManager
     * @param ConnectionRequestManager $connectionRequestManager
     */
    public function __construct(UserManager $userManager, ConnectionRequestManager $connectionRequestManager, FormFactoryInterface $formFactory)
    {
        $this->userManager              = $userManager;
        $this->connectionRequestManager = $connectionRequestManager;
        $this->formFactory              = $formFactory;
    }

    /**
     * @Route("/edit/{id}", name="admin_ajax_edit", options={"expose"=true})
     * @Method({"GET", "POST"})
     */
    public function ajaxEditAction(Request $request, User $user)
    {
        $form   = $this->formFactory->create('admin_user', $user, [
            'manager'   => $this->getDoctrine()->getManager(),
            'locale'    => $request->getLocale()
        ]);

        $form->handleRequest($request);

        if ($request->isMethod(Request::METHOD_POST)) {
            if ($form->isValid()) {
                $this->userManager->save($user);

                $userRequest = $this->connectionRequestManager->getFindOneUnpendingByUserId($user->getId());

                return new JsonResponse([
                    'success'   => true,
                    'user'      => [
                        'fullName'                      => $user->getFullName(),
                        'email'                         => $user->getEmail(),
                        'age'                           => $user->getAge(),
                        'type'                          => $this->connectionRequestManager->getWantToLearnTypeName($userRequest),
                        'countryName'                   => $user->getCountryName(),
                        'area'                          => $user->getMunicipality()->getName(),
                        'hasChildren'                   => ($user->getFullName()? 'Yes': 'No'),
                        'musicFriendType'               => $userRequest->getMusicFriendType(),
                        'about'                         => $user->getAbout(),
                        'firstConnectionRequestComment' => $user->getFirstConnectionRequestComment(),
                        'internalComment'               => $user->getInternalComment(),
                        'interests'                     => $this->userManager->getCategoryNameStringByUser($user)
                    ]
                ]);
            } else {
                return new JsonResponse(['success' => false]);
            }
        }

        return $this->render('admin2/user/form.html.twig', [
            'form'  => $form->createView(),
            'user'  => $user
        ]);
    }
}
