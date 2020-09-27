<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * EturizemLog Entity
 *
 * @property string $id
 * @property int $status
 * @property string $xml
 * @property string $message
 * @property \Cake\I18n\FrozenTime $created
 */
class EturizemLog extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'company_id' => true,
        'status' => true,
        'xml' => true,
        'message' => true,
        'created' => true,
    ];
}
