<?php
declare(strict_types=1);

namespace App\Policy;

/**
 * RoomsTable Policy Resolver
 */
class RoomsTablePolicy
{
    /**
     * Rooms scope
     *
     * @param \App\Model\Entity\User $user User
     * @param \Cake\ORM\Query $query Query object
     * @return \Cake\ORM\Query
     */
    public function scopeIndex($user, $query)
    {
        return $query->where(['Rooms.company_id' => $user->company_id]);
    }
}
