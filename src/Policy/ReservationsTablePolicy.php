<?php
declare(strict_types=1);

namespace App\Policy;

/**
 * ReservationsTable Policy Resolver
 */
class ReservationsTablePolicy
{
    /**
     * Reservations scope
     *
     * @param \App\Model\Entity\User $user User
     * @param \Cake\ORM\Query $query Query object
     * @return \Cake\ORM\Query
     */
    public function scopeIndex($user, $query)
    {
        return $query->where(['Reservations.company_id' => $user->company_id]);
    }
}
