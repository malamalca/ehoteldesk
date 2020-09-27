<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Reservation Entity
 *
 * @property string $id
 * @property string $company_id
 * @property string $counter_id
 * @property string $room_id
 * @property string $client_id
 * @property string $no
 * @property \Cake\I18n\Date $start
 * @property \Cake\I18n\Date $end
 * @property int $persons
 * @property string $name
 * @property string $address
 * @property string $country_code
 * @property string $descript
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\Counter $counter
 * @property \App\Model\Entity\Room $room
 */
class Reservation extends Entity
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
        '*' => true,
        'id' => false,
    ];
}
