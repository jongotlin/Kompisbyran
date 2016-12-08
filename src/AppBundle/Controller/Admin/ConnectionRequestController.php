<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Manager\ConnectionRequestManager;
use AppBundle\Manager\CityManager;
use AppBundle\Manager\UserManager;
use AppBundle\Entity\ConnectionRequest;
use AppBundle\Entity\User;
use AppBundle\Entity\City;
use AppBundle\Form\EditConnectionRequestType;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("admin/connectionrequests")
 */
class ConnectionRequestController extends Controller
{
    /**
     * @var ConnectionRequestManager
     */
    private $connectionRequestManager;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var CityManager
     */
    private $cityManager;

    /**
     * @InjectParams({
     *     "connectionRequestManager"   = @Inject("connection_request_manager"),
     *     "userManager"                = @Inject("user_manager"),
     *     "cityManager"                = @Inject("city_manager")
     * })
     */
    public function __construct(ConnectionRequestManager $connectionRequestManager, UserManager $userManager, CityManager $cityManager)
    {
        $this->connectionRequestManager = $connectionRequestManager;
        $this->userManager              = $userManager;
        $this->cityManager              = $cityManager;
    }

    /**
     * @Route("/{id}", name="admin_connectionrequest")
     * @Method({"GET", "POST"})
     */
    public function viewAction(Request $request, ConnectionRequest $connectionRequest)
    {
        $form = $this->createForm(new EditConnectionRequestType(), $connectionRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($connectionRequest);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_start'));
        }

        $parameters = [
            'connectionRequest' => $connectionRequest,
            'form' => $form->createView(),
        ];

        return $this->render('admin/connectionRequest/view.html.twig', $parameters);
    }

    /**
     * @Route("/{id}", name="admin_delete_connectionrequest")
     * @Method("DELETE")
     */
    public function deleteAction(ConnectionRequest $connectionRequest)
    {
        $this->getDoctrine()->getEntityManager()->remove($connectionRequest);
        $this->getDoctrine()->getEntityManager()->flush();

        return new Response();
    }

    /**
     * @Route("/", name="admin_create_connectionrequest")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $user           = $this->userManager->getFind($request->request->getInt('userId'));
        $activeRequest  = $this->connectionRequestManager->userHasActiveRequest($user);

        if ($activeRequest) {
            return new JsonResponse([
                'success' => false
            ]);
        }

        $city               = $this->cityManager->getFind($request->request->getInt('cityId'));
        $connectionRequest  = $this->connectionRequestManager->createNew();

        $connectionRequest->setUser         ( $user                                         );
        $connectionRequest->setWantToLearn  ( $request->request->getBoolean('wantToLearn')  );
        $connectionRequest->setComment      ( $request->request->get('comment')             );
        $connectionRequest->setCity         ( $city                                         );
        $connectionRequest->setSortOrder    ( $request->request->getInt('sortOrder')        );
        $connectionRequest->setType($request->request->getType('type'));
        try {
            $connectionRequest->setCreatedAt(new \DateTime($request->request->get('date')));
        } catch (\Exception $e) {}

        $this->connectionRequestManager->save($connectionRequest);

        return new JsonResponse([
            'success' => true
        ]);


    }
}
