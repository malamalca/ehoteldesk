<?php
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\Utility\Inflector;

$registrationView = [
    'title_for_layout' => $registration->kind == 'P' ? __('Registration') : __('Cancellation'),
    'menu' => [
        'edit' => [
            'title' => __('Edit'),
            'visible' => $this->getCurrentUser()->hasRole('admin') && empty($registration->etur_guid),
            'url' => [
                'action' => 'edit',
                $registration->id,
            ]
        ],
        'delete' => [
            'title' => __('Delete'),
            'visible' => $this->getCurrentUser()->hasRole('admin') && empty($registration->etur_guid),
            'url' => [
                'action' => 'delete',
                $registration->id,
            ],
            'params' => [
                'confirm' => __('Are you sure you want to delete this registration?')
            ]
        ],
    ],
    'entity' => $registration,
    'panels' => [
        'client' => [
            'lines' => [
                [
                    'label' => __('Client') . ':',
                    'text' => h($registration->name . ' ' . $registration->surname)
                ],
                ['label' => __('Street') . ':', 'text' => h($registration->street)],
                ['label' => __('ZIP and City') . ':', 'text' => h($registration->zip) . ' ' . h($registration->city)],
                ['label' => __('Country') . ':', 'text' => h($registration->country_code)],
            ],
        ],
        'dob-plob' => [
            'lines' => [
                [
                    'label' => __('Birth Date') . ':',
                    'text' => $registration->dob
                ],
                [
                    'label' => __('Birth Place') . ':',
                    'text' => h($registration->plob)
                ],
            ],
        ],
        'identification' => [
            'lines' => [
                [
                    'label' => __('Identification') . ':',
                    'text' => Configure::read('identKinds')[$registration->ident_kind]
                ],
                [
                    'label' => __('No.') . ':',
                    'text' => h($registration->ident_no)
                ],
            ],
        ],
        'date_span' => [
            'lines' => [
                [
                    'label' => __('Date Span') . ':',
                    'text' => $registration->start . ' - ' . $registration->end
                ],
            ],
        ],
        'room' => [
            'lines' => [
                [
                    'label' => __('Room') . ':',
                    'text' => h($registration->room->toString())
                ],
                [
                    'label' => __('Service') . ':',
                    'text' => isset($registration->service_type) ? h($registration->service_type->title) : ''
                ]
            ],
        ],
        'tourist-tax' => [
            'lines' => [
                [
                    'label' => __('Tourist Tax') . ':',
                    'text' => Configure::read('touristTaxKinds')[$registration->ttax_kind]
                ],
                [
                    'label' => __('Amount') . ':',
                    'text' => $this->Number->currency($registration->ttax_amount)
                ],
            ],
        ],
        'eturizem' => empty($registration->etur_guid) ?
        [
            'lines' => [
                [
                    'label' => __('ETurizem Status') . ':',
                    'text' => __('NOT Sent')
                ],
            ]
        ] :
        [
            'lines' => [
                [
                    'label' => __('ETurizem Status') . ':',
                    'text' => __('Sent')
                ],
                [
                    'label' => __('GUID') . ':',
                    'text' => h($registration->etur_guid)
                ],
                [
                    'label' => __('Time') . ':',
                    'text' => $registration->etur_time
                ],
            ]
        ],
        'invoices' => ['lines' => []]
    ]
];

echo $this->Lil->panels($registrationView, 'Registrations.view');
