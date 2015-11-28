<?php

namespace AppBundle\Controller\Admin;

use AppBundle\DomainEvents;
use AppBundle\Entity\City;
use AppBundle\Entity\Connection;
use AppBundle\Event\ConnectionCreatedEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("admin")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/{id}", name="admin_start", defaults={"id": null})
     */
    public function indexAction(Request $request, City $city = null)
    {
        $manager = $this->getDoctrine()->getManager();

        if ($request->isMethod('POST')) {
            $learnerConnectionRequest = $this->getConnectionRequestRepository()->find($request->request->getInt('learner'));
            $fluentSpeakerConnectionRequest = $this->getConnectionRequestRepository()->find($request->request->getInt('fluentSpeaker'));

            if ($this->getConnectionRepository()->findForUsers(
                $learnerConnectionRequest->getUser(), $fluentSpeakerConnectionRequest->getUser()
            )) {
                $this->addFlash('error', sprintf(
                    'Personerna %s och %s har redan kopplats ihop tidigare',
                    $learnerConnectionRequest->getUser()->getName(),
                    $fluentSpeakerConnectionRequest->getUser()->getName()
                ));
            } else {
                $connection = new Connection($this->getUser());
                $connection->setLearner($learnerConnectionRequest->getUser());
                $connection->setFluentSpeaker($fluentSpeakerConnectionRequest->getUser());
                $connection->setCity($learnerConnectionRequest->getCity());
                $connection->setFluentSpeakerComment($fluentSpeakerConnectionRequest->getComment());
                $connection->setLearnerComment($learnerConnectionRequest->getComment());

                $manager->persist($connection);
                $manager->remove($learnerConnectionRequest);
                $manager->remove($fluentSpeakerConnectionRequest);
                $manager->flush();

                $this->get('event_dispatcher')->dispatch(
                    DomainEvents::CONNECTION_CREATED,
                    new ConnectionCreatedEvent($connection)
                );

                $this->addFlash('info', sprintf(
                    'En koppling skapades melland %s och %s',
                    $learnerConnectionRequest->getUser()->getName(),
                    $fluentSpeakerConnectionRequest->getUser()->getName()
                ));
            }

            return $this->redirect($this->generateUrl('admin_start'));
        }

        if (!$city) {
            $city = $this->getCityRepository()->findAll()[0];
        }
        $learners = $this->getConnectionRequestRepository()->findBy(['wantToLearn' => true, 'city' => $city]);
        $fluentSpeakers = $this->getConnectionRequestRepository()->findBy(['wantToLearn' => false, 'city' => $city]);
        $cities = $this->getCityRepository()->findAll();

        $parameters = [
            'learners' => $learners,
            'fluentSpeakers' => $fluentSpeakers,
            'cities' => $cities,
            'city' => $city,
        ];

        return $this->render('admin/default/index.html.twig', $parameters);
    }

    protected function getConnectionRequestRepository()
    {
        return $this->getDoctrine()->getManager()->getRepository('AppBundle:ConnectionRequest');
    }

    protected function getCityRepository()
    {
        return $this->getDoctrine()->getManager()->getRepository('AppBundle:City');
    }

    /**
     * @return \AppBundle\Entity\ConnectionRepository
     */
    protected function getConnectionRepository()
    {
        return $this->getDoctrine()->getManager()->getRepository('AppBundle:Connection');
    }
}
