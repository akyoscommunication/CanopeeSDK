<?php

namespace Akyos\CanopeeSDK\Service;

use Akyos\CanopeeModuleSDK\Service\ProviderService;
use Akyos\CanopeeModuleSDK\Class\Post;
use App\Entity\User;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final readonly class RepositoryService
{
    public function __construct(
        private Security $security,
    ){
    }

    public function currentCustomer(QueryBuilder $qb, string $alias, ?User $user = null): Comparison
    {
        /** @var User $user */
        $user ??= $this->security->getUser();
        $qb->setParameter('customer', $user->getCustomer());

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
