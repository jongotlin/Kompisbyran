<?php

namespace AppBundle\Manager;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use AppBundle\Entity\UserRepository;
use AppBundle\Entity\User;
use AppBundle\Entity\ConnectionRequest;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use AppBundle\Manager\CategoryManager;

/**
 * @Service("user_manager")
 */
class UserManager implements ManagerInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var CategoryManager
     */
    private $categoryManager;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @InjectParams({
     *     "userRepository" = @Inject("user_repository"),
     *     "categoryManager" = @Inject("category_manager"),
     *     "router" = @Inject("router"),
     *     "translator" = @Inject("translator"),
     *     "requestStack" = @Inject("request_stack")
     * })
     */
    public function __construct(UserRepository $userRepository, CategoryManager $categoryManager, RouterInterface $router, TranslatorInterface $translator, RequestStack $requestStack)
    {
        $this->userRepository   = $userRepository;
        $this->categoryManager  = $categoryManager;
        $this->router           = $router;
        $this->translator       = $translator;
        $this->requestStack     = $requestStack;
    }

    /**
     * @return User
     */
    public function createNew()
    {
        return new User();
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function save($entity)
    {
        return $this->userRepository->save($entity);
    }

    /**
     * @param $id
     * @return null|object
     */
    public function getFind($id)
    {
        return $this->userRepository->find($id);
    }

    /**
     * @return array
     */
    public function getFindAll()
    {
        return $this->userRepository->findAll();
    }

    /**
     * @param $entity
     */
    public function remove($entity)
    {
        $this->userRepository->remove($entity);
    }

    /**
     * @param User $user
     * @param ConnectionRequest $userRequest
     * @param int $page
     * @param array $criterias
     * @return array
     */
    public function getFindMatch(User $user, ConnectionRequest $userRequest, $page = 1, array $criterias)
    {
        $this->unsetEmptyCriterias($criterias);

        $results    = $this->userRepository->findMatchArray($user, $userRequest, $criterias);
        $adapter    = new ArrayAdapter($results);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(5);
        $pagerfanta->setCurrentPage($page);

        return [
            'success'   => true,
            'results'   => $this->getMatchResultsdByPager($pagerfanta, $user),
            'next'      => ($pagerfanta->hasNextPage()? $pagerfanta->getNextPage(): false)
        ];
    }

    /**
     * @param Pagerfanta $pagerfanta
     * @param User $user
     * @return array
     */
    private function getMatchResultsdByPager(Pagerfanta $pagerfanta, User $user)
    {
        $datas  = [];
        $users  = $pagerfanta->getCurrentPageResults();

        foreach ($users as $auser) {
            $currentUser    = $this->getFind($auser['id']);
            $datas[]        = [
                'user_id'           => $auser['id'],
                'score'             => $auser['score'],
                'interests'         => $this->getCategoriesExactMatchByUser($user, $currentUser),
                'user_info'         => $currentUser->getFullName(),
                'edit_profile_link' => $this->router->generate('admin_ajax_edit', ['id' => $auser['id']]),
                'mark_pending_link' => $this->router->generate('admin_ajax_connection_request_mark_pending_or_unpending', ['id' => $auser['connection_request_id']]),
                'mark_pending_label'=> ($auser['pending']? 'Remove Pending': 'Make Pending'),
                'about'             => $currentUser->getAbout(),
                'matches'           => $this->getExactMatchByUser($user, $currentUser),
                'ele'               => 'ele'.$auser['id'],
                'gender'            => ($currentUser->getGender() == $user->getGender()? 1: 0),
                'age_diff'          => $currentUser->getAge()-$user->getAge()
            ];
        }

        return $datas;
    }

    /**
     * @param User $user
     * @param User $currentUser
     * @return array
     */
    private function getExactMatchByUser(User $user, User $currentUser)
    {
        $ageDiff    = $currentUser->getAge()-$user->getAge();
        $matches    = [];

        if ($this->isAgeDiffWithinRange($ageDiff)) {
            $matches[] = $this->wrapSpanString($currentUser->getAge().' '.$this->translator->trans('years'));
        } else {
            $matches[] = $currentUser->getAge().' '.$this->translator->trans('years');
        }

        $matches[]  =  $currentUser->getCountryName();

        if ($this->isUserMunicipalityMatch($user, $currentUser)) {
            $matches[] = $this->wrapSpanString($currentUser->getMunicipality()->getName());
        } else {
            $matches[] = $currentUser->getMunicipality()->getName();
        }

        if ($this->isUserHasChildrenMatch($user, $currentUser)) {
            $matches[] = $this->wrapSpanString(($currentUser->hasChildren()? $this->translator->trans('kids'): $this->translator->trans('no kids')));
        } else {
            $matches[] = ($currentUser->hasChildren()? $this->translator->trans('kids'): $this->translator->trans('no kids'));
        }

        return $matches;
    }
    /**
     * @param User $user
     * @return string
     */
    public function getCategoryNameStringByUser(User $user)
    {
        $categoryNames  = array_values($user->getCategoryNames());
        if (count($categoryNames) > 1) {
            $lastCategory   = array_pop($categoryNames);
            $categories = implode(', ', $categoryNames) .' '.  $this->translator->trans('and') .' '. $lastCategory;
        } else {
            $categories = implode(', ', $categoryNames);
        }
        return $categories;
    }
    /**
     * @param User $user
     * @param User $currentUser
     * @return string
     */
    private function getCategoriesExactMatchByUser(User $user, User $currentUser)
    {
        $categories             = [];
        $locale                 = $this->requestStack->getCurrentRequest()->getLocale();
        $currentUserCategories  = $this->categoryManager->getFindByIdsAndLocale(array_keys($currentUser->getCategoryNames()), $locale);
        $userCategories         = $user->getCategoryNames();
        foreach($currentUserCategories as $currentUserCategory) {
            if (isset($userCategories[$currentUserCategory->getId()])) {
                $categories[]   = $this->wrapSpanString($currentUserCategory->getName());
            } else {
                $categories[]   = $currentUserCategory->getName();
            }
        }
        if (count($categories) > 1) {
            $lastCategory   = array_pop($categories);
            $categories     = implode(', ', $categories) .' '.  $this->translator->trans('and') .' '. $lastCategory;
        } else {
            $categories     = implode(', ', $categories);
        }
        return $categories;
    }
    /**
     * @param $ageDiff
     * @return bool
     */
    private function isAgeDiffWithinRange($ageDiff)
    {
        return $ageDiff > -5 && $ageDiff < 5;
    }
    /**
     * @param User $user
     * @param User $currentUser
     * @return bool
     */
    private function isUserMunicipalityMatch(User $user, User $currentUser)
    {
        return $user->getMunicipality()->getId() == $currentUser->getMunicipality()->getId();
    }
    /**
     * @param User $user
     * @param User $currentUser
     * @return bool
     */
    private function isUserHasChildrenMatch(User $user, User $currentUser)
    {
        return $user->hasChildren() == true && $currentUser->hasChildren() == true;
    }
    /**
     * @param array $criterias
     */
    private function unsetEmptyCriterias(array &$criterias)
    {
        unset($criterias['_token']);

        foreach ($criterias as $key => $criteria) {
            if (strlen(trim($criteria)) == 0) {
                unset($criterias[$key]);
            }
        }
    }
    /**
     * @param $str
     * @return string
     */
    private function wrapSpanString($str)
    {
        return '<span class="matches">'.$str.'</span>';
    }
}
