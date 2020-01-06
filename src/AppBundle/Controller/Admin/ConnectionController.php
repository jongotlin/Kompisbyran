<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Connection;
use AppBundle\Form\EditConnectionType;
use AppBundle\Form\Model\SearchConnection;
use AppBundle\Form\SearchConnectionType;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("admin/connections")
 */
class ConnectionController extends Controller
{
    /**
     * @Route("/", name="admin_connections")
     */
    public function indexAction(Request $request)
    {
        $searchConnection = new SearchConnection();
        $form = $this->createForm(SearchConnectionType::class, $searchConnection);
        $form->handleRequest($request);

        $queryBuilder = $this->getConnectionRepository()
            ->getFindAllQueryBuilderForUser($searchConnection, $this->getUser());

        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);

        $pagerfanta->setMaxPerPage(10);
        $pagerfanta->setCurrentPage($request->query->getInt('page', 1));

        $parameters = [
            'pagerfanta' => $pagerfanta,
            'form' => $form->createView(),
        ];

        return $this->render('admin/connection/index.html.twig', $parameters);
    }

    /**
     * @Route("/{id}", name="admin_connection")
     */
    public function viewAction(Connection $connection, Request $request)
    {
        $form = $this->createForm(EditConnectionType::class, $connection);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($connection);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_connection', ['id' => $connection->getId()]);
        }

        $parameters = [
            'form' => $form->createView(),
        ];

        return $this->render('admin/connection/view.html.twig', $parameters);
    }

    protected function getConnectionRepository()
    {
        return $this->getDoctrine()->getManager()->getRepository('AppBundle:Connection');
    }
}
