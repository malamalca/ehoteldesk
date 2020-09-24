<?php
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\Utility\Inflector;

$reservationView = [
    'title_for_layout' => __('Reservation'),
    'menu' => [
        'edit' => [
            'title' => __('Edit'),
            'visible' => $this->getCurrentUser()->hasRole('admin'),
            'url' => [
                'action' => 'edit',
                $reservation->id,
            ]
        ],
        'delete' => [
            'title' => __('Delete'),
            'visible' => $this->getCurrentUser()->hasRole('admin'),
            'url' => [
                'action' => 'delete',
                $reservation->id,
            ],
            'params' => [
                'confirm' => __('Are you sure you want to delete this reservation?')
            ]
        ],
        'to_registration' => [
            'title' => __('Create Registration'),
            'visible' => $this->getCurrentUser()->hasRole('admin'),
            'url' => [
                'controller' => 'Registrations',
                'action' => 'add',
                '?' => ['reservation' =>  $reservation->id],
            ],
        ],
        'add_invoice' => !Configure::read('useInvoices') ? null : [
            'title' => __('Create Invoice'),
            'visible' => $this->getCurrentUser()->hasRole('admin'),
            'url' => [
                'action' => 'createInvoice',
                $reservation->id,
            ],
        ],
    ],
    'entity' => $reservation,
    'panels' => [
        'reservator' => [
            'lines' => [
                [
                    'label' => __('Name') . ':',
                    'text' => h($reservation->name)
                ],
                [
                    'label' => __('Address') . ':',
                    'text' => h($reservation->address)
                ],
                [
                    'label' => __('Country') . ':',
                    'text' => $reservation->country_code
                ],
            ],
        ],
        'date_span' => [
            'lines' => [
                [
                    'label' => __('Date Span') . ':',
                    'text' => $reservation->start . ' - ' . $reservation->end
                ],
            ],
        ],
        'room' => [
            'lines' => [
                [
                    'label' => __('Room') . ':',
                    'text' => h($reservation->room->toString())
                ],
                [
                    'label' => __('Persons') . ':',
                    'text' => $reservation->persons
                ]
            ],
        ],
        'descript' => [
            'lines' => [
                [
                    'label' => __('Notes') . ':',
                    'text' => $this->Lil->autop($reservation->descript)
                ],
            ],
        ],
        'invoices' => ['lines' => []]
    ]
];

if (Configure::read('useInvoices')) {
    $afterDeleteRedirect = base64_encode(Router::url(['plugin' => false, 'controller' => 'Reservations', 'action' => 'view', $reservation->id], true));
    foreach ($invoices as $i => $invoice) {
        $reservationView['panels']['invoices']['lines'][] = [
            'label' => $i == 0 ? __('Reservation Invoice') . ':' : '',
            'text' => $this->Html->link('#' . $invoice->no . ' ( ' . $this->Number->precision($invoice->total, 2) . ' € )', [
                'action' => 'previewInvoice',
                $invoice->reservation_id,
                $invoice->id,
            ]) . ' ' . $this->Html->link(__('delete'), ['plugin' => 'LilInvoices', 'controller' => 'Invoices', 'action' => 'delete', $invoice->id, 'redirect' => $afterDeleteRedirect], ['confirm' => __('Are you sure?')])
        ];
    }
}

echo $this->Lil->panels($reservationView, 'Reservations.view');
