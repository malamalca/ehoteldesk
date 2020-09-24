<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Registration Entity
 *
 * @property string $id
 * @property string $company_id
 * @property string $client_id
 * @property string $id_kind
 * @property string $id_no
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\Client $client
 */
class Registration extends Entity
{

}
