<?php
declare(strict_types=1);

namespace LilInvoices\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

/**
 * InvoicesClient Entity.
 *
 * @property string $id
 * @property string|null $invoice_id
 * @property string|null $contact_id
 * @property string|null $kind
 * @property string|null $title
 * @property string|null $street
 * @property string|null $city
 * @property string|null $zip
 * @property string|null $country
 * @property string|null $country_code
 * @property string|null $iban
 * @property string|null $bic
 * @property string|null $bank
 * @property string|null $tax_no
 * @property string|null $mat_no
 * @property string|null $person
 * @property string|null $phone
 * @property string|null $fax
 * @property string|null $email
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 */
class InvoicesClient extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];

    /**
     * Patch InvoicesClient with data from specified user
     *
     * @param \App\Model\Entity\User $user Source data entity.
     * @return void
     */
    public function patchWithAuth($user)
    {
        $this->contact_id = $user->company_id;

        if (empty($user->company)) {
            /** @var \App\Model\Entity\Companies $company */
            $company = TableRegistry::getTableLocator()->get('Companies')->get($user->company_id);
        } else {
            /** @var \LilCrm\Model\Entity\Contact $company */
            $company = $user->company;
        }
        $this->title = $company->name;
        $this->mat_no = $company->mat_no;
        $this->tax_no = $company->tax_no;

        $this->street = $company->street;
        $this->city = $company->city;
        $this->zip = $company->zip;
        $this->country = $company->country;
        $this->country_code = $company->country_code;

        $this->iban = $company->iban;
        $this->bic = $company->bic;
        $this->bank = $company->bank;

        $this->person = $user->name;
    }
}
