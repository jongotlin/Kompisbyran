<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Municipality;
use AppBundle\Entity\PreMatch;
use AppBundle\Entity\PreMatchIgnore;
use AppBundle\Security\Authorization\Voter\MunicipalityVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/admin")
 */
class PreMatchController extends Controller
{
    /**
     * @Route("/pre-matches/{id}", name="admin_pre_matches", requirements={"id": "\d+"})
     * @Method("GET")
     */
    public function indexAction(Municipality $municipality)
    {
        $this->denyAccessUnlessGranted(MunicipalityVoter::ADMIN_VIEW, $municipality);

        $municipalities = $this->getUser()->getAdminMunicipalities();

        if (count($municipality->getPreMatches()) == 0) {
            $this->get('manager.pre_match')->createMatches($municipality);
        }

        $this->getDoctrine()->getManager()->refresh($municipality);

        $parameters = [
            'municipalities' => $municipalities,
            'municipality' => $municipality,
            'preMatches' => $municipality->getPreMatches(),
        ];

        return $this->render('admin/preMatch/index.html.twig', $parameters);
    }

    /**
     * @Route(
     *     "/pre-matches/{municipalityId}/{preMatchId}",
     *     name="admin_re_pre_match",
     *     requirements={"municipalityId": "\d+", "preMatchId": "\d+"},
     *     options={"expose"=true}
     * )
     * @Method("PUT")
     * @ParamConverter(
     *     "preMatch",
     *     class="AppBundle:PreMatch",
     *     options={
     *         "repository_method"="findByMunicipalityIdAndPreMatchId",
     *         "map_method_signature"=true
     *     }
     * )
     */
    public function rePreMatchAction(PreMatch $preMatch)
    {
        $this->denyAccessUnlessGranted(MunicipalityVoter::ADMIN_VIEW, $preMatch->getMunicipality());

        if ($preMatch->getFluentSpeakerConnectionRequest()) {
            $preMatchIgnore = new PreMatchIgnore();
            $preMatchIgnore->setFluentSpeaker($preMatch->getFluentSpeakerConnectionRequest()->getUser());
            $preMatchIgnore->setLearner($preMatch->getLearnerConnectionRequest()->getUser());
            $preMatch->addPreMatchIgnore($preMatchIgnore);
            $preMatch->setFluentSpeakerConnectionRequest(null);
            $this->getDoctrine()->getManager()->persist($preMatch);
            $this->getDoctrine()->getManager()->flush();
        } else {
            foreach ($preMatch->getPreMatchIgnores() as $preMatchIgnore) {
                $this->getDoctrine()->getManager()->remove($preMatchIgnore);
            }
            $this->getDoctrine()->getManager()->flush();
        }

        $this->get('manager.pre_match')->createMatchForConnectionRequest(
            $preMatch->getLearnerConnectionRequest(), $preMatch
        );

        $this->getDoctrine()->getManager()->refresh($preMatch);

        return new JsonResponse($this->get('serializer')->normalize($preMatch));
    }

    /**
     * @Route("/pre-match/{id}.json", name="admin_pre_match_json", requirements={"id": "\d+"}, options={"expose"=true})
     * @Method("GET")
     */
    public function jsonAction(Municipality $municipality)
    {
        $this->denyAccessUnlessGranted(MunicipalityVoter::ADMIN_VIEW, $municipality);

        return new JsonResponse($this->get('serializer')->normalize($municipality->getPreMatches()));
    }
}
