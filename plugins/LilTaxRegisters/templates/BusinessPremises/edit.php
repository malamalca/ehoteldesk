<?php
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\Routing\Router;

$editForm = [
    'title_for_layout' => $businessPremise->isNew() ? __d('lil_tax_registers', 'Add Business Premise') : __d('lil_tax_registers', 'Edit Business Premise'),
    'form' => [
        'defaultHelper' => $this->Form,
        'pre' => '<div class="form">',
        'post' => '</div>',
        'lines' => [
            'form_start' => [
                'method' => 'create',
                'parameters' => [$businessPremise]
            ],
            'id' => [
                'method' => 'hidden',
                'parameters' => ['id']
            ],
            'company_id' => [
                'method' => 'hidden',
                'parameters' => ['owner_id']
            ],
            'sw_taxno' => [
                'method' => 'hidden',
                'parameters' => ['sw_taxno', ['value' => Configure::read('LilTaxRegisters.vendorTaxNo')]]
            ],
            'issuer_taxno' => [
                'method' => 'hidden',
                'parameters' => ['issuer_taxno', ['value' => $currentUser['company']['tax_no']]]
            ],
            'referer' => [
                'method' => 'hidden',
                'parameters' => ['referer', [
                    'default' => $this->request->referer()
                ]]
            ],

            'fs_basics_start' => '<fieldset>',
            'lg_basics' => sprintf('<legend>%s</legend>', __d('lil_tax_registers', 'Basics')),
            'title' => [
                'method' => 'control',
                'parameters' => ['title', [
                    'type' => 'text',
                    'label' => __d('lil_tax_registers', 'Title') . ':',
                ]]
            ],
            'no' => [
                'method' => 'control',
                'parameters' => ['no', [
                    'type' => 'text',
                    'label' => __d('lil_tax_registers', 'No.') . ':',
                ]]
            ],
            'tax_no' => [
                'method' => 'control',
                'parameters' => ['issuer_taxno', [
                    'type' => 'text',
                    'label' => __d('lil_tax_registers', 'Tax No.') . ':',
                ]]
            ],
            'validity_date' => [
                'method' => 'control',
                'parameters' => ['validity_date', [
                    'type' => 'lil-date',
                    'label' => __d('lil_tax_registers', 'Validity Date') . ':',
                ]]
            ],
            'kind' => [
                'method' => 'control',
                'parameters' => ['kind', [
                    'type' => 'select',
                    'options' => [
                        'RL' => __d('lil_tax_registers', 'RealEstate'),
                        'MO' => __d('lil_tax_registers', 'Moveable')
                    ],
                    'label' => [
                        'text' => __d('lil_tax_registers', 'Premise Kind') . ':',
                        'class' => 'active'
                    ],
                    'class' => 'browser-default',
                ]]
            ],
            'fs_basics_end' => '</fieldset>',

            'fs_mo_start' => '<fieldset>',
            'lg_mo' => sprintf('<legend>%s</legend>', __d('lil_tax_registers', 'Moveable Business Premise')),
            'mo_type' => [
                'method' => 'control',
                'parameters' => ['mo_type', [
                    'type' => 'select',
                    'options' => Configure::read('LilTaxRegisters.moveableTypes'),
                    'label' => [
                        'text' => __d('lil_tax_registers', 'Moveable Kind') . ':',
                        'class' => 'active',
                    ],
                    'class' => 'browser-default',
                ]]
            ],
            'fs_mo_end' => '</fieldset>',


            'fs_rl_start' => '<fieldset>',
            'lg_rl' => sprintf('<legend>%s</legend>', __d('lil_tax_registers', 'RealEstate Premise Data')),
            'casadral_number' => [
                'method' => 'control',
                'parameters' => ['casadral_number', [
                    'type' => 'text',
                    'label' => __d('lil_tax_registers', 'Casadral Community No.') . ':',
                ]]
            ],
            'building_number' => [
                'method' => 'control',
                'parameters' => ['building_number', [
                    'type' => 'text',
                    'label' => __d('lil_tax_registers', 'Building No.') . ':',
                ]]
            ],
            'building_section_number' => [
                'method' => 'control',
                'parameters' => ['building_section_number', [
                    'type' => 'text',
                    'label' => __d('lil_tax_registers', 'Building Sect. No.') . ':',
                ]]
            ],
            'street' => [
                'method' => 'control',
                'parameters' => ['street', [
                    'type' => 'text',
                    'label' => __d('lil_tax_registers', 'Street') . ':',
                ]]
            ],
            'house_number' => [
                'method' => 'control',
                'parameters' => ['house_number', [
                    'type' => 'text',
                    'label' => __d('lil_tax_registers', 'House Number') . ':',
                ]]
            ],
            'house_number_additional' => [
                'method' => 'control',
                'parameters' => ['house_number_additional', [
                    'type' => 'text',
                    'label' => __d('lil_tax_registers', 'House Number Additional') . ':',
                ]]
            ],
            'community' => [
                'method' => 'control',
                'parameters' => ['community', [
                    'type' => 'text',
                    'label' => __d('lil_tax_registers', 'Town') . ':',
                ]]
            ],
            'city' => [
                'method' => 'control',
                'parameters' => ['city', [
                    'type' => 'text',
                    'label' => __d('lil_tax_registers', 'Post Office') . ':',
                ]]
            ],
            'postal_code' => [
                'method' => 'control',
                'parameters' => ['postal_code', [
                    'type' => 'text',
                    'label' => __d('lil_tax_registers', 'Postcode') . ':',
                ]]
            ],
            'fs_rl_end' => '</fieldset>',

            'submit' => [
                'method' => 'submit',
                'parameters' => [
                    'label' => __d('lil_tax_registers', 'Save')
                ]
            ],
            'form_end' => [
                'method' => 'end',
                'parameters' => []
            ],
        ]
    ]
];

echo $this->Lil->form($editForm, 'LilTaxRegisters.BusinessPremises.edit');
