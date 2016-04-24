<?php

namespace AppBundle\Controller\Admin2;

use AppBundle\Manager\ConnectionRequestManager;
use AppBundle\Manager\CityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\City;

use AppBundle\DomainEvents;
use AppBundle\Entity\Connection;
use AppBundle\Entity\ConnectionRequest;
use AppBundle\Event\ConnectionCreatedEvent;

/**
 * @Route("admin2")
 */
class DefaultController extends Controller
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
     * @Route("/manual", name="admin_manual")
     * @Method("GET")
     * @Template("admin2/default/manual.html.twig")
     */
    public function manualAction(Request $request)
    {
        return [
            'pendingRequests' => $this->connectionRequestManager->getFindAllPending()
        ];
    }

    /**
     * @Route("/inspection", name="admin_inspection")
     * @Method("GET")
     * @Template("admin2/default/inspection.html.twig")
     */
    public function inspectionAction(Request $request)
    {
        return [
            'uninspectedRequests' => $this->connectionRequestManager->getFindAllUninspected()
        ];
    }

    /**
     * @Route("", name="admin_start2")
     * @Method("GET")
     * @Template("admin2/default/index.html.twig")
     */
    public function indexAction(Request $request, City $city = null)
    {
        $cities = $this->cityManager->getFindAll();

        if (!$city instanceof City) {
            $city   = $cities[0];
        }

        return [
            'newUsers'          => $this->connectionRequestManager->getFindNewWithinCity($city),
            'establishedUsers'  => $this->connectionRequestManager->getFindEstablishedWithinCity($city),
            'cities'            => $cities,
            'city'              => $city,
            'currentCityId'     => $request->getSession()->get('selected_city', $city->getId())
        ];
    }
}
