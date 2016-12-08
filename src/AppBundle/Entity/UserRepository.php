<?php
namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\User;
use AppBundle\Entity\ConnectionRequest;

/**
 * Class UserRepository
 * @package AppBundle\Entity
 */
class UserRepository extends EntityRepository
{
    /**
     * @param User $user
     * @return User
     */
    public function save(User $user)
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
        return $user;
    }

    /**
     * @param User $user
     */
    public function remove(User $user)
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @return array
     */
    public function findAllWithCategoryJoinAssoc()
    {
        $sql = "
            SELECT u.id, u.email, u.first_name, u.last_name, u.want_to_learn, u.gender, u.age, u.from_country,
                GROUP_CONCAT(DISTINCT uc.category_id) as category_ids,
                IF (
                    IFNULL(MAX(c1.created_at), '2000-01-01') > IFNULL(MAX(c2.created_at), '2000-01-01'),
                    MAX(c1.created_at),
                    MAX(c2.created_at)
                    ) as connection_created_at
            FROM fos_user u
            LEFT JOIN users_categories uc on u.id = uc.user_id
            LEFT JOIN connection c1 on u.id = c1.fluent_speaker_id
            LEFT JOIN connection c2 on u.id = c2.learner_id
            WHERE u.roles != 'a:0:{}'
            AND u.enabled = 1
            GROUP BY u.id
            ORDER BY u.id";
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param User $user
     * @param ConnectionRequest $userRequest
     * @param array $criterias
     * @return array
     */
    public function findMatchArray(User $user, ConnectionRequest $userRequest, array $criterias)
    {
        $userParams = [
            'user_municipality'     => $user->getMunicipality()->getId(),
            'user_gender'           => $user->getGender(),
            'user_age'              => $user->getAge(),
            'user_children'         => $user->hasChildren(),
            'want_to_learn'         => $user->getWantToLearn(),
            'user'                  => $user->getId(),
            'user_categories'       => array_values($user->getCategoryIds()),
            'user_music_categories' => array_values($user->getMusicCategoryIds())
        ];

        $where  = $this->prepareMatchCriterias($criterias);
        $rsm    = new \Doctrine\ORM\Query\ResultSetMapping();
        $sql    = "SELECT *, (COALESCE(SUM(cat_score),0) + SUM(age_score) + SUM(area_score) + SUM(children_score) + SUM(gender_score) + COALESCE(SUM(music_cat_score),0)) AS score
              FROM
              (
                  SELECT u.id, cr.created_at AS connection_request_created_at, cr.pending, cr.id AS connection_request_id, (CASE WHEN(u.municipality_id=:user_municipality) THEN 2 ELSE 0 END) AS area_score, (CASE WHEN(u.has_children=true AND true=:user_children) THEN 2 ELSE 0 END) AS children_score, (CASE WHEN(u.gender=:user_gender) THEN 1 ELSE 0 END) AS gender_score, (CASE WHEN((u.age-:user_age) BETWEEN -5 AND 5) THEN 2 ELSE 0 END) AS age_score, ((SELECT COUNT(users_categories.category_id) FROM users_categories WHERE users_categories.user_id = u.id AND users_categories.category_id IN (:user_categories) GROUP BY users_categories.user_id)*3) cat_score, ((SELECT COUNT(users_music_categories.category_id) FROM users_music_categories WHERE users_music_categories.user_id = u.id AND users_music_categories.category_id IN (:user_music_categories) GROUP BY users_music_categories.user_id)*3) AS music_cat_score
                  FROM fos_user u
                  JOIN connection_request cr
                  ON cr.user_id = u.id
                  LEFT JOIN users_categories c
                  ON c.user_id = u.id
                  LEFT JOIN users_music_categories mc
                  ON mc.user_id = u.id
                  WHERE u.id != :user
                  AND u.want_to_learn != :want_to_learn
                  AND cr.pending = false
                  AND cr.disqualified = false
                  and u.enabled = true
                  AND $where
                  GROUP BY u.id
              ) temp
              GROUP BY temp.id
              ORDER BY score DESC
        ";

        $query = $this->_em->createNativeQuery($sql, $rsm);

        $rsm->addScalarResult('id'                      , 'id');
        $rsm->addScalarResult('area_score'              , 'area_score');
        $rsm->addScalarResult('children_score'          , 'children_score');
        $rsm->addScalarResult('gender_score'            , 'gender_score');
        $rsm->addScalarResult('age_score'               , 'age_score');
        $rsm->addScalarResult('cat_score'               , 'cat_score');
        $rsm->addScalarResult('music_cat_score'         , 'music_cat_score');
        $rsm->addScalarResult('score'                   , 'score');
        $rsm->addScalarResult('pending'                 , 'pending');
        $rsm->addScalarResult('connection_request_id'   , 'connection_request_id');
        $rsm->addScalarResult('connection_request_created_at' , 'connection_request_created_at');

        $this->setSearchParamaters(array_merge($criterias, $userParams), $query);

        return $query->getArrayResult();
    }

    /**
     * @param array $params
     * @param $query
     */
    private function setSearchParamaters(array $params, $query)
    {
        foreach($params as $key => $value) {
            if ($key == 'q') {
                $query->setParameter($key, "%$value%");

                continue;
            }

            $query->setParameter($key, $value);
        }
    }

    /**
     * @param array $criterias
     * @return string
     */
    private function prepareMatchCriterias(array $criterias)
    {
        $where  = ['u.enabled = true'];
        $fields = array_keys($criterias);
        foreach($fields as $field) {
            if ($field === 'ageFrom' || $field === 'ageTo' || $field === 'category_id' || $field === 'city_id' || $field === 'music_friend' || $field === 'q') {
                continue;
            }
            $where[] = 'u.'.$field .' = :'.$field;
        }
        if (isset($criterias['ageFrom']) && isset($criterias['ageTo'])) {
            $where[] = 'u.age BETWEEN :ageFrom AND :ageTo';
        }
        if (isset($criterias['category_id'])) {
            $where[] = '(c.category_id = :category_id OR mc.category_id = :category_id)';
        }
        if (isset($criterias['city_id'])) {
            $where[] = 'cr.city_id = :city_id';
        }
        if (isset($criterias['type'])) {
            $where[] = 'cr.type = :type';
        }
        if (isset($criterias['q'])) {
            $where[] = '(cr.comment LIKE :q OR u.about LIKE :q)';
        }

        return implode(' AND ', $where);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findAllAdmin()
    {
        return $this
            ->createQueryBuilder('u')
            ->where('u.roles LIKE :role_admin')
            ->orWhere('u.roles LIKE :role_super_admin')
            ->setParameter('role_admin', "%ROLE_ADMIN%")
            ->setParameter('role_super_admin', "%ROLE_SUPER_ADMIN%")
            ->getQuery()
            ->getResult()
        ;
    }
}
