<?php
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\Routing\Router;

$roomForm = [
    'title_for_layout' => $room->id ? __('Edit Room') : __('Add Room'),
    'menu' => [
        'delete' => [
            'title' => __('Delete'),
            'visible' => !$room->isNew() && $this->getCurrentUser()->hasRole('admin'),
            'url' => [
                'action' => 'delete',
                $room->id
            ],
            'params' => ['confirm' => __('Are You Sure?')]
        ]
    ],
    'form' => [
        'defaultHelper' => $this->Form,
        'pre' => '<div class="form">' . PHP_EOL,
        'post' => '</div>',
        'lines' => [
            'form_start' => [
                'method' => 'create',
                'parameters' => [$room]
            ],
            'id' => [
                'method' => 'hidden',
                'parameters' => ['id']
            ],
            'company_id' => [
                'method' => 'hidden',
                'parameters' => ['company_id']
            ],
            'referer' => [
                'method' => 'hidden',
                'parameters' => ['referer', [
                    'default' => $this->getRequest()->referer()
                ]]
            ],

            'fs_basics_start' => '<fieldset>',
            'lg_basics' => sprintf('<legend>%s</legend>', __('Basics')),
            'no' => [
                'method' => 'control',
                'parameters' => ['no', [
                    'type' => 'text',
                    'label' => __('No.') . ':',
                ]]
            ],
            'title' => [
                'method' => 'control',
                'parameters' => ['title', [
                    'type' => 'text',
                    'label' => __('Title') . ':',
                ]]
            ],
            'type' => [
                'method' => 'control',
                'parameters' => ['room_type_id', [
                    'type' => 'select',
                    'options' => $roomTypes,
                    'label' => [
                        'text' => __('Room Type') . ':',
                        'class' => 'active',
                    ],
                    'class' => 'browser-default'
                ]]
            ],
            'beds' => [
                'method' => 'control',
                'parameters' => ['beds', [
                    'type' => 'number',
                    'label' => __('No. of Beds') . ':',
                ]]
            ],
            'fs_basics_end' => '</fieldset>',

            'fs_invoice_start' => '<fieldset>',
            'lg_invoice' => sprintf('<legend>%s</legend>', __('Invoice Data')),
            'price' => [
                'method' => 'control',
                'parameters' => ['priceperday', [
                    'type' => 'number',
                    'label' => __('Price Per Day.') . ':',
                ]]
            ],
            'vat' => [
                'method' => 'control',
                'parameters' => ['vat_id', [
                    'type' => 'select',
                    'label' => [
                        'text' => __('VAT') . ':',
                        'class' => 'active',
                    ],
                    'options' => isset($vatLevels) ? $vatLevels : [],
                    'class' => 'browser-default'
                ]]
            ],
            'fs_invoice_end' => '</fieldset>',

            'submit' => [
                'method' => 'submit',
                'parameters' => [
                    'label' => __('Save')
                ]
            ],
            'form_end' => [
                'method' => 'end',
                'parameters' => []
            ],
        ]
    ]
];

echo $this->Lil->form($roomForm, 'Rooms.edit');
