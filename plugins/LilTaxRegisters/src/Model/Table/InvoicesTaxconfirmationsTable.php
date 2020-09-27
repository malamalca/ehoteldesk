<?php
namespace LilTaxRegisters\Model\Table;

use App\Lib\FiscalSign;
use App\Lib\FiscalSoap;
use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Xml;
use Cake\Validation\Validator;
use LilTaxRegisters\Lib\TaxRegistersXml;

/**
 * InvoicesTaxconfirmations Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Invoices
 *
 * @method \LilTaxRegisters\Model\Entity\InvoicesTaxconfirmation get($primaryKey, $options = [])
 * @method \LilTaxRegisters\Model\Entity\InvoicesTaxconfirmation newEntity($data = null, array $options = [])
 * @method \LilTaxRegisters\Model\Entity\InvoicesTaxconfirmation[] newEntities(array $data, array $options = [])
 * @method \LilTaxRegisters\Model\Entity\InvoicesTaxconfirmation|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \LilTaxRegisters\Model\Entity\InvoicesTaxconfirmation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \LilTaxRegisters\Model\Entity\InvoicesTaxconfirmation[] patchEntities($entities, array $data, array $options = [])
 * @method \LilTaxRegisters\Model\Entity\InvoicesTaxconfirmation findOrCreate($search, callable $callback = null)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class InvoicesTaxconfirmationsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('invoices_taxconfirmations');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Invoices', [
            'foreignKey' => 'invoice_id',
            'className' => 'LilTaxRegisters.Invoices'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('id')
            ->allowEmptyString('id', 'create');

        $validator
            ->allowEmptyString('zoi');

        $validator
            ->allowEmptyString('eor');

        $validator
            ->allowEmptyString('last_request');

        $validator
            ->allowEmptyString('last_response');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['invoice_id'], 'Invoices'));

        return $rules;
    }

    /**
     * Create invoice XML request, sign request and send it to tax auth.
     *
     * @param string $invoiceId Invoice id.
     * @param string $authCompany Current user's company data.
     * @return bool|object Returns false on failure or InvoicesTaxconfirmation Entity on success
     */
    public function signAndSend($invoiceId, $authCompany, $p12, $p12Password)
    {
        $Invoices = TableRegistry::get('LilInvoices.Invoices');
        $InvoicesTaxconfirmations = TableRegistry::get('LilTaxRegisters.InvoicesTaxconfirmations');
        $BusinessPremises = TableRegistry::get('LilTaxRegisters.BusinessPremises');

        $invoice = $Invoices->get($invoiceId, ['contain' => ['InvoicesCounters',
            'Buyers', 'InvoicesItems']]);

        // try to get existing TaxConfirmation record
        $invoicesTaxconfirmation = $InvoicesTaxconfirmations->find()
            ->select()
            ->where(['invoice_id' => $invoiceId])
            ->first();

        if (!empty($invoicesTaxconfirmation->eor)) {
            return $invoicesTaxconfirmation;
        }

        if (!$invoicesTaxconfirmation) {
            $UsersTable = TableRegistry::get('Lil.Users');
            $user = $UsersTable->get($invoice->user_id);

            $businessPremise = $BusinessPremises->get($invoice->invoices_counter->business_premise_id);

            // create new TaxConfirmation record
            $invoicesTaxconfirmation = $InvoicesTaxconfirmations->newEmptyEntity();
            $invoicesTaxconfirmation->invoice_id = $invoice->id;
            $invoicesTaxconfirmation->bp_no = $businessPremise->no;
            $invoicesTaxconfirmation->device_no = $invoice->invoices_counter->device_no;
            $invoicesTaxconfirmation->issuer_taxno = $authCompany['tax_no'];
            $invoicesTaxconfirmation->operator_taxno = $user->tax_no;
        }

        if (empty($invoicesTaxconfirmation->zoi)) {
            $invoicesTaxconfirmation->zoi = $InvoicesTaxconfirmations->generateZOI($invoicesTaxconfirmation, $invoice, $p12, $p12Password);
            $invoicesTaxconfirmation->error_code = 1;

            $invoicesTaxconfirmation->qr =
                str_pad($this->baseConvert($invoicesTaxconfirmation->zoi, 16, 10), 39, '0', STR_PAD_LEFT) .
                $invoicesTaxconfirmation->issuer_taxno .
                $invoice->created->format('YmdHis');

            // control char
            $sum = 0;
            $len = strlen($invoicesTaxconfirmation->qr);
            for ($i = 0; $i < $len; $i++) {
                $sum += $invoicesTaxconfirmation->qr[$i];
            }
            $invoicesTaxconfirmation->qr .= $sum % 10;
        }

        if ($InvoicesTaxconfirmations->save($invoicesTaxconfirmation)) {
            $invArray = TaxRegistersXml::invoice($invoice, $invoicesTaxconfirmation);
            $envelope = TaxRegistersXml::envelope($invArray);

            $XmlObject = Xml::fromArray($envelope, ['format' => 'tags', 'return' => 'domdocument', 'pretty' => true]);
            $xmlRequest = $XmlObject->saveXML();
            $invoicesTaxconfirmation->last_request = $xmlRequest;

            // sign xml
            $s = new FiscalSign();
            $s->setP12($p12);
            $s->setPassword($p12Password);

            if ($signed = $s->sign($xmlRequest, 'fu:InvoiceRequest')) {
                $invoicesTaxconfirmation->last_request = $signed;

                $s = new FiscalSoap();
                $s->setP12($p12);
                $s->setPassword($p12Password);
                $s->setCert(Configure::read('LilTaxRegisters.security.cert'));

                try {
                    $response = $s->sendInvoiceRaw($signed);
                } catch (Exception $e) {
                    Log::error(sprintf('Error sending soap request: %s', $e->getMessage()), 'taxrSoap');
                    $invoicesTaxconfirmation->error_code = CONFIRMATION_ERROR_REQUEST;
                }

                if ($response) {
                    $invoicesTaxconfirmation->last_response = $response;

                    if ($s->hasError($response) === false) {
                        $invoicesTaxconfirmation->eor = $s->elementValue($response, 'UniqueInvoiceID');
                    } else {
                        $invoicesTaxconfirmation->error_code = CONFIRMATION_ERROR_XML;
                        Log::error(sprintf('Error message from server: %s ', $s->elementValue($response, 'fu:ErrorMessage')), 'taxrSoap');
                    }
                } else {
                    $invoicesTaxconfirmation->error_code = CONFIRMATION_ERROR_SOAP;
                }
            } else {
                $invoicesTaxconfirmation->error_code = CONFIRMATION_ERROR_SIGN;
            }

            return $InvoicesTaxconfirmations->save($invoicesTaxconfirmation);
        }

        return false;
    }

    /**
     * Convert base for large numbers
     *
     * @param string $numstring Input number.
     * @param int $frombase Input number's base.
     * @param int $tobase Result's base.
     * @return string
     */
    private function baseConvert($numstring, $frombase, $tobase)
    {
        $chars = "0123456789abcdefghijklmnopqrstuvwxyz";
        $tostring = substr($chars, 0, $tobase);

        $length = strlen($numstring);
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $number[$i] = strpos($chars, $numstring[$i]);
        }
        do {
            $divide = 0;
            $newlen = 0;
            for ($i = 0; $i < $length; $i++) {
                $divide = $divide * $frombase + $number[$i];
                if ($divide >= $tobase) {
                    $number[$newlen++] = (int)($divide / $tobase);
                    $divide = $divide % $tobase;
                } elseif ($newlen > 0) {
                    $number[$newlen++] = 0;
                }
            }
            $length = $newlen;
            $result = $tostring[$divide] . $result;
        } while ($newlen != 0);

        return $result;
    }

    /**
     * Calculate and sign ZOI number
     *
     * @param object $invoicesTaxconfirmation Entity.
     * @param object $invoice Invoice entity.
     * @return string
     */
    private function generateZOI($invoicesTaxconfirmation, $invoice, $p12, $p12Password)
    {
        $enc = $invoicesTaxconfirmation->issuer_taxno;
        $enc .= $invoice->created->format('d.m.Y H:i:s');
        $enc .= $invoice->no;
        $enc .= $invoicesTaxconfirmation->bp_no;
        $enc .= $invoicesTaxconfirmation->device_no;
        $enc .= $invoice->total;

        $s = new FiscalSign();
        $s->setP12($p12);
        $s->setPassword($p12Password);

        $ret = $s->zoi($enc);

        return $ret;
    }
}
