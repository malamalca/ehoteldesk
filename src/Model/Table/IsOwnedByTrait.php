<?php
declare(strict_types=1);

namespace App\Model\Table;

trait IsOwnedByTrait
{
    /**
     * Returns default counter
     *
     * @param \App\Model\Table\uuid $entityId Entity id.
     * @param \App\Model\Table\uuid $ownerId Owner Id.
     * @return mixed
     */
    public function isOwnedBy($entityId, $ownerId)
    {
        $conditions = ['id' => $entityId, 'company_id' => $ownerId];
        $ret = $this->exists($conditions);

        return $ret;
    }
}
