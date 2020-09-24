<?php
declare(strict_types=1);

namespace App\Policy;

/**
 * Company Policy Resolver
 */
class CompanyPolicy
{
    /**
     * Authorize view action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\Company $entity Company
     * @return bool
     */
    public function canView($authUser, $entity)
    {
        return $authUser->hasRole('root');
    }

    /**
     * Authorize edit action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\Company $entity Company
     * @return bool
     */
    public function canEdit($authUser, $entity)
    {
        return $authUser->hasRole('root');
    }

    /**
     * Authorize delete action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\Company $entity Company
     * @return bool
     */
    public function canDelete($authUser, $entity)
    {
        return $authUser->hasRole('root');
    }
}
