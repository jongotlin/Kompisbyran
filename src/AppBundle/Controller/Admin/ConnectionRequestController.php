<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Manager\ConnectionRequestManager;
use AppBundle\Manager\CityManager;
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
     * @var CityManager
     */
    private $cityManager;

    /**
     * @InjectParams({
     *     "connectionRequestManager"   = @Inject("connection_request_manager"),
     *     "cityManager"                = @Inject("city_manager")
     * })
     */
    public function __construct(ConnectionRequestManager $connectionRequestManager, CityManager $cityManager)
    {
        $this->connectionRequestManager = $connectionRequestManager;
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
        /** @var User $user */
        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')
            ->find($request->request->getInt('userId'));
        $city = $this->getDoctrine()->getManager()->getRepository('AppBundle:City')
            ->find($request->request->getInt('cityId'));

        $connectionRequest = new ConnectionRequest();
        $connectionRequest->setUser($user);
        $connectionRequest->setWantToLearn($request->request->getBoolean('wantToLearn'));
        $connectionRequest->setComment($request->request->get('comment'));
        $connectionRequest->setCity($city);
        $connectionRequest->setSortOrder($request->request->getInt('sortOrder'));
        $connectionRequest->setMusicFriend($request->request->getBoolean('musicFriend'));

        $this->getDoctrine()->getEntityManager()->persist($connectionRequest);
        $this->getDoctrine()->getEntityManager()->flush();

        return new Response();
    }

    /**
     * @Route("/ajax-by-city/{id}", name="ajax_by_city", options={"expose"=true})
     * @Method({"GET"})
     */
    public function ajaxByCityAction(Request $request)
    {
        $city   = $this->cityManager->getFind($request->get('id'));

        if ($city instanceof City) {
            $results = $this->connectionRequestManager->getFindPaginatedByCityResults($city, $request->get('page', 1));
        } else {
            $results = [
                'success'   => false,
                'message'   => 'City not found!'
            ];
        }

        return new JsonResponse($results);
    }
}
