<?php

namespace Akyos\CanopeeSDK\Service;

use App\Entity\UserAccessRight;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class RepositoryService
{
    public function __construct(
        private RequestStack $requestStack,
    ){
    }

    public function currentCustomer(QueryBuilder $qb, string $alias, ?UserAccessRight $userAccessRight = null): Comparison
    {
        if(!$userAccessRight) {
            $userAccessRights = $this->requestStack->getSession()->get('userAccessRights');
        }

        if($userAccessRights) {
            $qb->setParameter('customer', $userAccessRights->getCustomer());
        } else {
            $qb->setParameter('customer', null);
        }

        return $qb->expr()->eq($alias.'.customer', ':customer');
    }

    public function notDelete(QueryBuilder $qb, string $alias, ?string $deletedState): Comparison|QueryBuilder
    {
        if (null !== $deletedState) {
            $qb->setParameter('deleted', $deletedState);

            return $qb->expr()->eq($alias.'.deletedState', ':deleted');
        }

        return $qb;
    }
}
