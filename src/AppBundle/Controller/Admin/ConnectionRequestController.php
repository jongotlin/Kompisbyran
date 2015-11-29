<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\ConnectionRequest;
use AppBundle\Form\EditConnectionRequestType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("admin/connectionrequests")
 */
class ConnectionRequestController extends Controller
{
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
        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')
            ->find($request->request->getInt('userId'));
        $city = $this->getDoctrine()->getManager()->getRepository('AppBundle:City')
            ->find($request->request->getInt('cityId'));

        $connectionRequest = new ConnectionRequest();
        $connectionRequest->setUser($user);
        $connectionRequest->setWantToLearn($request->request->getInt('wantToLearn'));
        $connectionRequest->setComment($request->request->get('comment'));
        $connectionRequest->setCity($city);

        $this->getDoctrine()->getEntityManager()->persist($connectionRequest);
        $this->getDoctrine()->getEntityManager()->flush();

        return new Response();
    }


}
