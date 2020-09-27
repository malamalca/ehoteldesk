<?php
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\Utility\Inflector;

$moTypes = Configure::read('LilTaxRegisters.moveableTypes');

$viewPanels = [
    'title_for_layout' => $businessPremise->title,
    'menu' => [
        'edit' => [
            'title' => __d('lil_tax_registers', 'Edit'),
            'visible' => $this->getCurrentUser()->hasRole('admin'),
            'url' => [
                'action' => 'edit',
                $businessPremise->id,
            ]
        ],
        'delete' => [
            'title' => __d('lil_tax_registers', 'Delete'),
            'visible' => $this->getCurrentUser()->hasRole('admin'),
            'url' => [
                'action' => 'delete',
                $businessPremise->id,
            ],
            'params' => [
                'confirm' => __d('lil_tax_registers', 'Are you sure you want to delete this premise?')
            ]
        ],
        'register' => [
            'title' => __d('lil_tax_registers', 'Register'),
            'visible' => $this->getCurrentUser()->hasRole('admin') && !$businessPremise->active,
            'url' => [
                'action' => 'register',
                $businessPremise->id,
            ],
        ],
        'unregister' => [
            'title' => __d('lil_tax_registers', 'Ungister/close'),
            'visible' => $this->getCurrentUser()->hasRole('admin') && $businessPremise->active,
            'url' => [
                'action' => 'unregister',
                $businessPremise->id,
            ],
        ],
    ],
    'entity' => $businessPremise,
    'panels' => [
        'basics' => [
            'lines' => [
                'no ' => [
                    'label' => __d('lil_tax_registers', 'No.') . ':',
                    'text' => h($businessPremise->no)
                ],
                'issuer_taxno ' => [
                    'label' => __d('lil_tax_registers', 'Issuer Taxno.') . ':',
                    'text' => h($businessPremise->issuer_taxno)
                ],
                'validity_date ' => [
                    'label' => __d('lil_tax_registers', 'Validity Date') . ':',
                    'text' => h($businessPremise->validity_date)
                ],
            ],
        ],
        'kind' => [
            'lines' => [
                'kind' => [
                    'label' => __d('lil_tax_registers', 'Kind') . ':',
                    'text' => $businessPremise->kind == 'RL' ? __d('lil_tax_registers', 'Real Estate') : __d('lil_tax_registers', 'Moveable')
                ],
                'status' => $businessPremise->closed === false ? null : [
                    'label' => __d('lil_tax_registers', 'Status') . ':',
                    'text' => __d('lil_tax_registers', 'CLOSED')
                ],
            ],
        ],
        'mo_data' => $businessPremise->kind == 'RL' ? null : [
            'lines' => [
                'mo_type' => [
                    'label' => __d('lil_tax_registers', 'Moveable Kind') . ':',
                    'text' => $businessPremise->mo_type . ' - ' . h($moTypes[$businessPremise->mo_type])
                ],
            ],
        ],
        'rl_data' => $businessPremise->kind != 'RL' ? null : [
            'lines' => [
                'casadral_number' => [
                    'label' => __d('lil_tax_registers', 'Casadral Number') . ':',
                    'text' => $businessPremise->casadral_number
                ],
                'building_number' => [
                    'label' => __d('lil_tax_registers', 'Casadral Number') . ':',
                    'text' => $businessPremise->building_number
                ],
                'building_section_number' => [
                    'label' => __d('lil_tax_registers', 'Casadral Number') . ':',
                    'text' => $businessPremise->building_section_number
                ],
            ],
        ],
        'rl_address' => $businessPremise->kind != 'RL' ? null : [
            'lines' => [
                'street' => [
                    'label' => __d('lil_tax_registers', 'Street') . ':',
                    'text' => h($businessPremise->street)
                ],
                'house_number' => [
                    'label' => __d('lil_tax_registers', 'House No.') . ':',
                    'text' => h($businessPremise->house_number)
                ],
                'house_number_additional' => [
                    'label' => __d('lil_tax_registers', 'Add. House No.') . ':',
                    'text' => h($businessPremise->house_number_additional)
                ],
                'community' => [
                    'label' => __d('lil_tax_registers', 'Town') . ':',
                    'text' => h($businessPremise->community)
                ],
                'city' => [
                    'label' => __d('lil_tax_registers', 'Post Office') . ':',
                    'text' => h($businessPremise->city)
                ],
                'postal_code' => [
                    'label' => __d('lil_tax_registers', 'Postcode') . ':',
                    'text' => h($businessPremise->postal_code)
                ],
            ],
        ],
        'invoices' => ['lines' => []]
    ]
];

echo $this->Lil->panels($viewPanels, 'LilTaxRegisters.BusinessPremises.view');
