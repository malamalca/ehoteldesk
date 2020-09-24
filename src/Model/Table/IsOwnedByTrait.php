<?php
namespace App\Model\Table;

trait IsOwnedByTrait
{
    /**
     * Returns default counter
     *
     * @param uuid $entityId Entity id.
     * @param uuid $ownerId Owner Id.
     * @return mixed
     */
    public function isOwnedBy($entityId, $ownerId)
    {
        $conditions = ['id' => $entityId, 'company_id' => $ownerId];
        $ret = $this->exists($conditions);

        return $ret;
    }
}
