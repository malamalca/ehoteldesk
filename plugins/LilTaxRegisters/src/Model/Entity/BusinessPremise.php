<?php
namespace LilTaxRegisters\Model\Entity;

use Cake\ORM\Entity;

/**
 * BusinessPremise Entity
 *
 * @property string $id
 * @property string $owner_id
 * @property string $no
 * @property string $title
 * @property string $kind
 * @property string $casadral_number
 * @property string $building_number
 * @property string $building_section_number
 * @property string $street
 * @property string $house_number
 * @property string $house_number_additional
 * @property int $community
 * @property int $city
 * @property string $postal_code
 * @property string $mo_type
 * @property \Cake\I18n\Time $validity_date
 * @property bool $closed
 * @property string $sw_taxno
 * @property string $sw_title
 * @property string $notes
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \LilTaxRegisters\Model\Entity\Owner $owner
 */
class BusinessPremise extends Entity
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
        'id' => false
    ];
}
