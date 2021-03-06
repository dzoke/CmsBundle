<?php

namespace Opifer\CmsBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Opifer\ContentBundle\Model\Content;
use Opifer\ContentBundle\Model\ContentRepository as BaseContentRepository;
use Opifer\CrudBundle\Pagination\Paginator;

/**
 * ContentRepository
 *
 * Because content items are used in different kinds of usecases, please specify
 * the scope of the function inside the function name.
 * For example:
 *
 * findByIds()         => returns all content
 * findPublicByIds()   => returns only public content
 * findPrivateByIds()  => returns only private content
 */
class ContentRepository extends BaseContentRepository
{

    /**
     * Search content by term
     *
     * @param string $term
     *
     * @return ArrayCollection
     */
    public function search($term)
    {
        $qb = $this->_em->createQueryBuilder();

        return $this->createValuedQueryBuilder('c')
            ->leftJoin('c.directory', 'd')
            ->where($qb->expr()->orX(
                $qb->expr()->like('c.title', ':term'),
                $qb->expr()->like('c.description', ':term'),
                $qb->expr()->like('v.value', ':term')
            ))
            ->andWhere('c.active = :active')
            ->andWhere('c.indexable = :indexable')
            ->andWhere('c.nestedIn IS NULL')
            ->andWhere('d.searchable = :searchable OR c.directory IS NULL')
            ->setParameter('term', '%'.$term.'%')
            ->setParameter('active', 1)
            ->setParameter('indexable', 1)
            ->setParameter('searchable', 1)
            ->getQuery()
            ->getResult();
    }

    /**
     * Search content by term including nested
     *
     * @param  string $term
     *
     * @return ArrayCollection
     */
    public function searchNested($term)
    {
        $qb = $this->_em->createQueryBuilder();

        return $this->createValuedQueryBuilder('c')
            ->leftJoin('c.directory', 'd')
            ->where($qb->expr()->orX(
                $qb->expr()->like('c.title', ':term'),
                $qb->expr()->like('c.description', ':term'),
                $qb->expr()->like('v.value', ':term'),
                $qb->expr()->like('v.value', ':term_entities')
            ))
            ->andWhere('c.active = :active')
            ->andWhere('c.searchable = :searchable OR c.nestedIn IS NOT NULL')
            ->andWhere('d.searchable = :searchable OR c.directory IS NULL')
            ->setParameter('term', '%'.$term.'%')
            ->setParameter('term_entities', '%'.htmlentities($term).'%')
            ->setParameter('active', 1)
            ->setParameter('searchable', 1)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get a querybuilder by request
     *
     * @param Request $request
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findPaginatedByRequest(Request $request)
    {
        $qb = $this->createValuedQueryBuilder('c');
        $qb->andWhere('c.nestedIn IS NULL');

        if ($request->get('site_id')) {
            $qb->andWhere("c.site = :site")->setParameter('site', $request->get('site_id'));
        }

        if ($request->get('q')) {
            $qb->andWhere("c.title LIKE :query")->setParameter('query', '%'.$request->get('q').'%');
        } else {
            if ($request->get('directory_id')) {
                $qb->andWhere("c.directory = :directory")->setParameter('directory', $request->get('directory_id'));
            } else {
                $qb->andWhere("c.directory is NULL");
            }
        }

        $qb->orderBy('c.slug');

        $page = ($request->get('p')) ? $request->get('p') : 1;
        $limit = ($request->get('limit')) ? $request->get('limit') : 25;

        return new Paginator($qb, $limit, $page);
    }

    /**
     * Find related content
     *
     * @param Content $content
     * @param integer $limit
     *
     * @return ArrayCollection
     */
    public function findRelated(Content $content, $limit = 10)
    {
        $city = $content->getValueSet()->getValueFor('address')->getAddress()->getCity();

        $query = $this->createQueryBuilder('c')
            ->innerJoin('c.valueSet', 'vs')
            ->innerJoin('vs.values', 'v')
            ->innerJoin('v.attribute', 'a')
            ->innerJoin('v.address', 'addr')
            ->where('addr.city = :city')
            ->andWhere('c.active = 1')
            ->andWhere('c.nestedIn IS NULL')
            ->andWhere('c.id <> :id')
            ->setParameter('id', $content->getId())
            ->setParameter('city', $city)
            ->setMaxResults($limit)
            ->getQuery()
        ;

        return $query->getResult();
    }

    /**
     * Find the last updated content items
     *
     * @param int $limit
     *
     * @return ArrayCollection
     */
    public function findLastUpdated($limit = 5)
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.nestedIn IS NULL')
            ->orderBy('c.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
        ;

        return $query->getResult();
    }

    /**
     * Find all active and addressable content items
     *
     * @return ArrayCollection
     */
    public function findActiveAddressable()
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.directory', 'directory')
            ->where('c.nestedIn IS NULL')
            ->Andwhere('c.active = :active')
            ->setParameter('active', '1')
            ->orderBy('directory.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
