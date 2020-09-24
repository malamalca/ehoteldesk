<?php
declare(strict_types=1);

namespace App\Policy;

/**
 * ServiceTypesTable Policy Resolver
 */
class ServiceTypesTablePolicy
{
    /**
     * ServiceTypes scope
     *
     * @param \App\Model\Entity\User $user User
     * @param \Cake\ORM\Query $query Query object
     * @return \Cake\ORM\Query
     */
    public function scopeIndex($user, $query)
    {
        return $query->where(['ServiceTypes.company_id' => $user->company_id]);
    }
}
