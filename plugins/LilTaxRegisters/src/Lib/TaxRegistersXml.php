<?php

namespace LilTaxRegisters\Lib;

use Cake\I18n\Time;
use Cake\Utility\Text;

class TaxRegistersXml
{
    public static function envelope($xmlDataArray = null)
    {
        $envelope = ['soapenv:Envelope' => [
            'xmlns:soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'xmlns:fu' => 'http://www.fu.gov.si/',
            'xmlns:xsi' => 'http://www.w3.org/2000/09/xmldsig#',
            'soapenv:Body' => []
        ]];

        if ($xmlDataArray) {
            $envelope['soapenv:Envelope']['soapenv:Body'] = $xmlDataArray;
        }

        return $envelope;
    }

    /**
     * Generate InvoiceRequest array
     *
     * @param object $invoice Invoice entity.
     * @param object $invoicesTaxconfirmation InvoicesTaxconfirmation entity.
     * @return array
     */
    public static function invoice($invoice, $invoicesTaxconfirmation)
    {
        $xmlArray = [
           'fu:InvoiceRequest' => [
               '@Id' => 'data',
               'fu:Header' => [
                    'fu:MessageID' => Text::uuid(),
                    'fu:DateTime' => (new Time())->format('c')
                ],
                'fu:Invoice' => [
                    'fu:TaxNumber' => $invoicesTaxconfirmation->issuer_taxno,
                    'fu:IssueDateTime' => $invoice->created->format('c'),
                    // način dodeljevanja številke računa
                    // C-centralno za poslovno enoto, B-po napravi
                    'fu:NumberingStructure' => 'C',
                    'fu:InvoiceIdentifier' => [
                        'fu:BusinessPremiseID' => $invoicesTaxconfirmation->bp_no,
                        'fu:ElectronicDeviceID' => $invoicesTaxconfirmation->device_no,
                        'fu:InvoiceNumber' => $invoice->counter
                    ],
                    'fu:CustomerVATNumber' => $invoice->buyer->taxno,
                    'fu:InvoiceAmount' => sprintf('%0.2f', $invoice->total),
                    'fu:PaymentAmount' => sprintf('%0.2f', $invoice->total),
                    'fu:TaxesPerSeller' => [],
                    'fu:OperatorTaxNumber' => $invoicesTaxconfirmation->operator_taxno,
                    'fu:ProtectedID' => $invoicesTaxconfirmation->zoi
                ]
            ]
        ];

        $exemptVAT = 0; $nonTaxable = 0;
        $taxSpec = [];
        foreach ($invoice->invoices_items as $item) {
            if (empty($item->vat_id)) {
                $exemptVAT += $item->net_total;
            } else {
                if ($item->vat_percent == 0) {
                    $nonTaxable += $item->net_total;
                } else {
                    if (!isset($taxSpec[$item->vat_id])) {
                        $taxSpec[$item->vat_id] = ['base' => 0, 'amount' => 0, 'percent' => 0];
                    }
                    $taxSpec[$item->vat_id]['base'] += $item->net_total;
                    $taxSpec[$item->vat_id]['amount'] += $item->tax_total;
                    $taxSpec[$item->vat_id]['percent'] = $item->vat_percent;
                }
            }
        }

        if ($exemptVAT > 0) {
            $xmlArray['fu:InvoiceRequest']['fu:Invoice']['fu:TaxesPerSeller']['ExemptVATTaxableAmount'] = sprintf('%0.2f', $exemptVAT);
        }

        if ($nonTaxable > 0) {
            $xmlArray['fu:InvoiceRequest']['fu:Invoice']['fu:TaxesPerSeller']['NontaxableAmount'] = sprintf('%0.2f', $nonTaxable);
        }

        foreach ($taxSpec as $tax) {
            $xmlArray['fu:InvoiceRequest']['fu:Invoice']['fu:TaxesPerSeller']['fu:VAT'][] = [
                'fu:TaxRate' => sprintf('%0.2f', $tax['percent']),
                'fu:TaxableAmount' => sprintf('%0.2f', $tax['base']),
                'fu:TaxAmount' => sprintf('%0.2f', $tax['amount']),
            ];
        }

        if (empty($invoice->buyer->taxno)) {
            unset($xmlArray['fu:InvoiceRequest']['fu:Invoice']['fu:CustomerVATNumber']);
        }

        if (empty($invoicesTaxconfirmation->operator_taxno)) {
            unset($xmlArray['fu:InvoiceRequest']['fu:Invoice']['fu:OperatorTaxNumber']);
        }

        return $xmlArray;
    }

    /**
     * Generate BusinessPremiseRequest array
     *
     * @param object $businessPremise BP entity.
     * @param bool $closingTag Include BP closing tag.
     * @return array
     */
    public static function businessPremise($businessPremise, $closingTag = false)
    {
        $xmlArray = [
           'fu:BusinessPremiseRequest' => [
               '@Id' => 'data',
               'fu:Header' => [
                    'fu:MessageID' => Text::uuid(),
                    'fu:DateTime' => (new Time())->format('c')
                ],
                'fu:BusinessPremise' => [
                    'fu:TaxNumber' => $businessPremise->issuer_taxno,
                    'fu:BusinessPremiseID' => $businessPremise->no,
                    'fu:BPIdentifier' => [
                        'fu:RealEstateBP' => [
                            'fu:PropertyID' => [
                                'fu:CadastralNumber' => $businessPremise->casadral_number,
                                'fu:BuildingNumber' => $businessPremise->building_number,
                                'fu:BuildingSectionNumber' => $businessPremise->building_section_number,
                            ],
                            'fu:Address' => [
                                'fu:Street' => $businessPremise->street,
                                'fu:HouseNumber' => $businessPremise->house_number,
                                'fu:HouseNumberAdditional' => $businessPremise->house_number_additional,
                                'fu:Community' => $businessPremise->community,
                                'fu:City' => $businessPremise->city,
                                'fu:PostalCode' => $businessPremise->postal_code,
                            ],
                        ],
                        'fu:PremiseType' => $businessPremise->mo_type,
                    ],

                    'fu:ValidityDate' => $businessPremise->validity_date->toDateString(),
                    'fu:ClosingTag' => 'Z',
                    'fu:SoftwareSupplier' => [
                        'fu:TaxNumber' => $businessPremise->sw_taxno,
                    ],
                    //<fu:SpecialNotes>Primer prijave poslovnega prostora</fu:SpecialNotes>
                ]
           ]
        ];

        if ($businessPremise->kind == 'RL') {
            unset($xmlArray['fu:BusinessPremiseRequest']['fu:BusinessPremise']['fu:BPIdentifier']['fu:PremiseType']);
        } else {
            unset($xmlArray['fu:BusinessPremiseRequest']['fu:BusinessPremise']['fu:BPIdentifier']['fu:RealEstateBP']);
        }

        if ($closingTag === false) {
            unset($xmlArray['fu:BusinessPremiseRequest']['fu:BusinessPremise']['fu:ClosingTag']);
        }

        return $xmlArray;
    }
}
