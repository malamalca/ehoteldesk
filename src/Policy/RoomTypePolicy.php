<?php
declare(strict_types=1);

namespace App\Policy;

/**
 * RoomType Policy Resolver
 */
class RoomTypePolicy
{
    /**
     * Authorize view action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\RoomType $entity RoomType
     * @return bool
     */
    public function canView($authUser, $entity)
    {
        return $authUser->company_id == $entity->company_id;
    }

    /**
     * Authorize edit action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\RoomType $entity RoomType
     * @return bool
     */
    public function canEdit($authUser, $entity)
    {
        return ($authUser->company_id == $entity->company_id) && $authUser->hasRole('admin');
    }

    /**
     * Authorize delete action
     *
     * @param \App\Model\Entity\User $authUser User
     * @param \App\Model\Entity\RoomType $entity RoomType
     * @return bool
     */
    public function canDelete($authUser, $entity)
    {
        return ($authUser->company_id == $entity->company_id) && $authUser->hasRole('admin');
    }
}
