<?php

namespace AppBundle\Controller\Admin2;

use AppBundle\Enum\FriendTypes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Manager\UserManager;
use AppBundle\Manager\ConnectionRequestManager;
use AppBundle\Manager\ConnectionManager;
use Symfony\Component\Form\FormFactoryInterface;
use AppBundle\Entity\User;
use AppBundle\Entity\ConnectionRequest;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use AppBundle\Mailer\UserMailer;

/**
 * @Route("admin2/matches")
 */
class MatchController extends Controller
{
    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var ConnectionManager
     */
    private $connectionManager;

    /**
     * @var ConnectionRequestManager
     */
    private $connectionRequestManager;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var UserMailer
     */
    private $userMailer;

    /**
     * @InjectParams({
     *     "formFactory" = @Inject("form.factory"),
     *     "templating" = @Inject("templating"),
     *     "userMailer" = @Inject("app.user_mailer")
     * })
     * @param UserManager $userManager
     * @param ConnectionManager $connectionManager
     * @param ConnectionRequestManager $connectionRequestManager
     */
    public function __construct(UserManager $userManager, ConnectionManager $connectionManager, ConnectionRequestManager $connectionRequestManager, FormFactoryInterface $formFactory, EngineInterface $templating, UserMailer $userMailer)
    {
        $this->userManager              = $userManager;
        $this->connectionManager        = $connectionManager;
        $this->connectionRequestManager = $connectionRequestManager;
        $this->formFactory              = $formFactory;
        $this->templating               = $templating;
        $this->userMailer               = $userMailer;
    }

    /**
     * @Route("/find/{id}", name="admin_match_find", options={"expose"=true})
     * @Method({"GET"})
     */
    public function findAction(Request $request)
    {
        $user           = $this->userManager->getFind($request->get('id'));
        $userRequest    = $this->connectionRequestManager->getFindOneUnpendingByUserId($request->get('id'));

        $this->denyAccessUnlessGranted('view', $userRequest);

        if (!$user instanceof User || !$userRequest instanceof ConnectionRequest) {
            throw $this->createNotFoundException();
        }

        $form = $this->formFactory->create('match_filter', null, [
            'type' => $userRequest->getType(),
            'city_id' => $userRequest->getCity()->getId(),
        ]);

        $matchForm  = $this->formFactory->create('match', null, [
            'user' => $user
        ]);

        return $this->render('admin2/match/find.html.twig', [
            'user'                  => $user,
            'connectionRequest'     => $userRequest,
            'form'                  => $form->createView(),
            'matchForm'             => $matchForm->createView()
        ]);
    }

    /**
     * @Route("/ajax/results/{id}", name="admin_match_results", options={"expose"=true})
     * @Method({"POST"})
     */
    public function ajaxResultsAction(Request $request, User $user)
    {
        $criterias      = $request->request->all();
        $userRequest    = $this->connectionRequestManager->getFindOneUnpendingByUserId($user->getId());
        $results        = $this->userManager->getFindMatch($user, $userRequest, $request->get('page', 1), $criterias['match_filter']);

        return new JsonResponse($results);
    }

    /**
     * @Route("/approve", name="admin_match_approve")
     * @Method({"POST"})
     */
    public function approveAction(Request $request)
    {
        $match      = $request->get('match');
        $user       = $this->userManager->getFind($match['user_id']);
        $matchUser  = $this->userManager->getFind($match['match_user_id']);

        if ($user instanceof User && $matchUser instanceof User) {
            if ($this->connectionManager->getIsUserConnectionExists($user, $matchUser)) {

                $this->addFlash('error', sprintf(
                    'Personerna %s och %s har redan kopplats ihop tidigare',
                    $user->getName(),
                    $matchUser->getName()
                ));

            } else {

                $userRequest        = $this->connectionRequestManager->getFindOneByUser($user);
                $matchUserRequest   = $this->connectionRequestManager->getFindOneByUser($matchUser);

                if ($userRequest instanceof ConnectionRequest && $matchUserRequest instanceof ConnectionRequest) {

                    $connection = $this->connectionManager->saveByConnectionRequest($userRequest, $matchUserRequest, $this->getUser());
                    $this->connectionRequestManager->remove($userRequest);
                    $this->connectionRequestManager->remove($matchUserRequest);

                    $this->userMailer->sendMatchEmailMessage($user, $matchUser, $match['email_to_user'], $connection->getCity()->getSenderEmail());
                    $this->userMailer->sendMatchEmailMessage($matchUser, $user, $match['email_to_match_user'], $connection->getCity()->getSenderEmail());

                    $this->addFlash('info', sprintf(
                        'En koppling skapades melland %s och %s',
                        $userRequest->getUser()->getName(),
                        $matchUserRequest->getUser()->getName()
                    ));
                }
            }

            return $this->redirect($this->generateUrl('admin_start'));
        }

        throw $this->createNotFoundException();
    }

    /**
     * @Route("/ajax/email-message/{id}/{match_user_id}", name="admin_ajax_email_message", options={"expose"=true})
     * @Method({"GET"})
     */
    public function ajaxEmailMessageAction(Request $request, User $user)
    {
        $matchUser          = $this->userManager->getFind($request->get('match_user_id'));
        $userRequest        = $this->connectionRequestManager->getFindOneByUser($user);
        $matchUserRequest   = $this->connectionRequestManager->getFindOneByUser($matchUser);

        return new JsonResponse([
            'success'               => true,
            'user_message'          => $this->templating->render('email/match_email.html.twig', [
                'user'      => $user,
                'matchUser' => $matchUser,
                'request'   => $matchUserRequest]),
            'match_user_message'    => $this->templating->render('email/match_email.html.twig', [
                'user'      => $matchUser,
                'matchUser' => $user,
                'request'   => $userRequest])
        ]);
    }
}
