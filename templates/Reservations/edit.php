<?php
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\Routing\Router;


$pageTitle = __('Add Reservation #{0}', $reservation->no);
if (!$reservation->isNew()) {
    $pageTitle = __('Edit Reservation #{0}', $reservation->no);
}

$reservationForm = [
    'title_for_layout' => $pageTitle,
    'menu' => [
        'delete' => $reservation->isNew() ? null : [
            'title' => __('Delete'),
            'visible' => $this->getCurrentUser()->hasRole('admin'),
            'url' => [
                'action' => 'delete',
                $reservation->id
            ],
            'params' => [
                'confirm' => __('Are you sure you want to delete this reservation?')
            ]
        ],
        'create-registration' => $reservation->isNew() ? null : [
            'title' => __('Create Registration'),
            'visible' => $this->getCurrentUser()->hasRole('admin'),
            'url' => [
                'controller' => 'Registrations',
                'action' => 'add',
                '?' => ['reservation' => $reservation->id]
            ]
        ],
    ],
    'form' => [
        'defaultHelper' => $this->Form,
        'pre' => '<div class="form" id="ReservationsEditForm">' . PHP_EOL,
        'post' => '</div>',
        'lines' => [
            'form_start' => [
                'method' => 'create',
                'parameters' => [$reservation]
            ],
            'id' => [
                'method' => 'hidden',
                'parameters' => ['id']
            ],
            'company_id' => [
                'method' => 'hidden',
                'parameters' => ['company_id']
            ],
            'client_id' => [
                'method' => 'hidden',
                'parameters' => ['client_id', ['id' => 'client-id']]
            ],
            'client_no' => [
                'method' => 'hidden',
                'parameters' => ['client_no', ['id' => 'client-no']]
            ],
            'referer' => [
                'method' => 'hidden',
                'parameters' => ['referer', [
                    'default' => $this->getRequest()->referer()
                ]]
            ],

            'fs_no_start' => '<div id="ReservationsEditNo">',
            'no' => [
                'method' => 'control',
                'parameters' => ['no', [
                    'type' => 'hidden',
                ]]
            ],
            'fs_no_end' => '</div>',

            'fs_reservator_start' => '<fieldset>',
            'lg_reservator' => sprintf('<legend>%s</legend>', __('Reservator')),

            'name' => [
                'method' => 'control',
                'parameters' => ['name', [
                    'type' => 'text',
                    'label' => __('Surname and Name') . ':'
                ]]
            ],
            'address' => [
                'method' => 'control',
                'parameters' => ['address', [
                    'type' => 'text',
                    'label' => __('Address') . ':'
                ]]
            ],
            'country_code' => [
                'method' => 'control',
                'parameters' => ['country_code', [
                    'type' => 'select',
                    'label' => [
                        'text' => __('Country'),
                        'class' => 'active',
                    ],
                    'options' => Configure::read('LilCrm.countries'),
                    'default' => Configure::read('LilCrm.defaultCountry'),
                    'class' => 'browser-default'
                ]]
            ],
            'fs_reservator_end' => '</fieldset>',

            'fs_dates_start' => '<fieldset id="ReservationDates">',
            'lg_dates' => sprintf('<legend>%s</legend>', __('Reservation Dates')),
            'start' => [
                'method' => 'control',
                'parameters' => ['start', [
                    'type' => 'date',
                    'label' => __('Start') . ':',
                    'error' => [
                        'format' => __('Invalid Date Format.'),
                        'overlapDates' => __('Invalid or Overlapping Date'),
                        'overlapRegistrations' => __('Overlapping with Registrations'),
                    ],
                    'default' => $this->getRequest()->getQuery('start') ?: new Time()
                ]]
            ],
            'end' => [
                'method' => 'control',
                'parameters' => ['end', [
                    'type' => 'date',
                    'label' => __('End') . ':',
                    'error' => [
                        'format' => __('Invalid Date Format.'),
                        'overlapDates' => __('Invalid or Overlapping Date'),
                    ],
                    'default' => $this->getRequest()->getQuery('end') ?: new Time()
                ]]
            ],
            'fs_dates_end' => '</fieldset>',

            'fs_room_start' => '<fieldset id="ReservationRoom">',
            'lg_room' => sprintf('<legend>%s</legend>', __('Room')),
            'room' => [
                'method' => 'control',
                'parameters' => ['room_id', [
                    'type' => 'select',
                    'label' => [
                        'text' => __('Room') . ':',
                        'class' => 'active',
                    ],
                    'options' => $rooms,
                    'class' => 'browser-default',
                ]]
            ],

            'persons' => [
                'method' => 'control',
                'parameters' => ['persons', [
                    'type' => 'number',
                    'label' => __('No. Persons') . ':',
                ]]
            ],

            'descript' => [
                'method' => 'control',
                'parameters' => ['descript', [
                    'type' => 'textarea',
                    'rows' => 2,
                    'label' => [
                        'text' => __('Notes') . ':',
                        'class' => 'active',
                    ],
                    'default' => $this->getRequest()->getQuery('descript')
                ]]
            ],

            'fs_room_end' => '</fieldset>',

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

echo $this->Lil->form($reservationForm, 'Reservations.edit');
?>
<script type="text/javascript">
    $(document).ready(function () {
    });
</script>
