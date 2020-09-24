<?php
declare(strict_types=1);

namespace App\Policy;

/**
 * CountersTable Policy Resolver
 */
class CountersTablePolicy
{
    /**
     * Counters scope
     *
     * @param \App\Model\Entity\User $user User
     * @param \Cake\ORM\Query $query Query object
     * @return \Cake\ORM\Query
     */
    public function scopeIndex($user, $query)
    {
        return $query->where(['Counters.company_id' => $user->company_id]);
    }
}
