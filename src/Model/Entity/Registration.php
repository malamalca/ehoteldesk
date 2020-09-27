<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Registration Entity
 *
 * @property string $id
 * @property string $company_id
 * @property string $counter_id
 * @property string $client_id
 * @property int $client_no
 * @property string $room_id
 * @property string $service_id
 * @property string $surname
 * @property string $name
 * @property string $sex
 * @property string $street
 * @property string $zip
 * @property string $city
 * @property string $country_code
 * @property \Cake\I18n\Date $dob
 * @property string $plob
 * @property string $nationality_code_code
 * @property string $kind
 * @property \Cake\I18n\Date $start
 * @property \Cake\I18n\Date $end
 * @property string $ident_kind
 * @property string $ident_no
 * @property string $ttax_kind
 * @property float $ttax_amount
 * @property string $etur_guid
 * @property \Cake\I18n\Time $etur_time
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\Client $client
 */
class Registration extends Entity
{
}
