<?php
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\Routing\Router;

$registrationsForm = [
    'title_for_layout' => $registration->isNew() ? __('Add Registration') : __('Edit Registration'),
    'menu' => [
        'delete' => $registration->isNew() ? null : [
            'title' => __('Delete'),
            'visible' => $this->getCurrentUser()->hasRole('admin'),
            'url' => [
                'action' => 'delete',
                $registration->id
            ],
            'params' => [
                'confirm' => __('Are you sure you want to delete this registration?')
            ]
        ],
        'copy' => [
            'title' => __('Copy'),
            'visible' => !$registration->isNew(),
            'url' => [
                'action' => 'add',
                '?' => ['copy' => $registration->id]
            ],
        ]
    ],
    'form' => [
        'defaultHelper' => $this->Form,
        'pre' => '<div class="form">' . PHP_EOL,
        'post' => '</div>',
        'lines' => [
            'form_start' => [
                'method' => 'create',
                'parameters' => [$registration, ['id' => 'RegistrationsEditForm']]
            ],
            'id' => $registration->isNew() ? null : [
                'method' => 'hidden',
                'parameters' => ['id']
            ],
            'company_id' => [
                'method' => 'hidden',
                'parameters' => ['company_id']
            ],
            'counter_id' => [
                'method' => 'hidden',
                'parameters' => ['counter_id']
            ],
            'reservation_id' => [
                'method' => 'hidden',
                'parameters' => ['reservation_id', ['default' => $this->getRequest()->getQuery('reservation')]]
            ],
            'referer' => [
                'method' => 'hidden',
                'parameters' => ['referer', [
                    'default' => $this->getRequest()->referer()
                ]]
            ],

            'fs_reservator_start' => '<fieldset>',
            'lg_reservator' => sprintf('<legend>%s</legend>', __('Client')),

            'div_client_start' => '<div class="input-field">',
            'client' => [
                'method' => 'text',
                'parameters' => ['client', [
                    'type' => 'text',
                    'id' => 'client',
                    'autocomplete' => 'off',
                    'label' => false,
                    'placeholder' => __('Please Enter Client'),
                    'value' => trim($registration->surname . ' ' . $registration->name)
                ]]
            ],
            //'client-search' => '<button id="client-btn-search" class="ui-button ui-widget ui-state-default ui-corner-all" style="display: none"><i class="fa fa-search"></i></button>',
            'client-selected' => '<a id="btn-client-selected" class="btn btn-small disabled"><i class="material-icons">person</i></a>',
            'client-edit' => '<a id="btn-client-edit" class="btn btn-small"><i class="material-icons">edit</i></a>',
            'client_id-error' => [
                'method' => 'error',
                'parameters' => ['client_id', __('Please pick a client from client\'s list.')]
            ],
            'div_client_end' => '</div>',

            'client_id' => ['method' => 'control', 'parameters' => ['client_id', ['type' => 'hidden', 'id' => 'client-id']]],
            'client_id_unlock' => ['method' => 'unlockField', 'parameters' => ['client_id']],

            'client_no' => ['method' => 'control', 'parameters' => ['client_no', ['type' => 'hidden', 'id' => 'client-no']]],
            'client_no_unlock' => ['method' => 'unlockField', 'parameters' => ['client_no']],

            'name' => ['method' => 'control', 'parameters' => ['name', ['type' => 'hidden', 'id' => 'client-name']]],
            'name_unlock' => ['method' => 'unlockField', 'parameters' => ['name']],

            'surname' => ['method' => 'control', 'parameters' => ['surname', ['type' => 'hidden', 'id' => 'client-surname']]],
            'surname_unlock' => ['method' => 'unlockField', 'parameters' => ['surname']],

            //

            'label_address' => ['method' => 'label', 'parameters' => [__('Address') . ':']],
            'street' => ['method' => 'control', 'parameters' => ['street', ['type' => 'text', 'id' => 'client-street', 'readonly' => true]]],
            'street_unlock' => ['method' => 'unlockField', 'parameters' => ['street']],

            'zip' => ['method' => 'control', 'parameters' => ['zip', ['type' => 'text', 'id' => 'client-zip', 'readonly' => true]]],
            'zip_unlock' => ['method' => 'unlockField', 'parameters' => ['zip']],

            'city' => ['method' => 'control', 'parameters' => ['city', ['type' => 'text', 'id' => 'client-city', 'readonly' => true]]],
            'city_unlock' => ['method' => 'unlockField', 'parameters' => ['city']],

            'country_code' => ['method' => 'control', 'parameters' => ['country_code', ['type' => 'text', 'id' => 'client-country_code', 'readonly' => true]]],
            'country_code_unlock' => ['method' => 'unlockField', 'parameters' => ['country_code']],

            'div_dob-plob_start' => '<div class="input-field">',
            'label_dob-plob' => ['method' => 'label', 'parameters' => ['dob', __('Date and place of birth') . ':', ['class' => 'active']]],

            'dob' => ['method' => 'text', 'parameters' => ['dob', ['type' => 'date', 'id' => 'client-dob', 'label' => false, 'readonly' => true]]],
            'dob_unlock' => ['method' => 'unlockField', 'parameters' => ['dob']],
            'spacer_dob-plob' => '&nbsp;',

            'plob' => ['method' => 'text', 'parameters' => ['plob', ['type' => 'text', 'id' => 'client-plob', 'label' => false, 'readonly' => true]]],
            'plob_unlock' => ['method' => 'unlockField', 'parameters' => ['plob']],

            'div_dob-plob_end' => '</div>',

            'fs_reservator_end' => '</fieldset>',


            'fs_ident_start' => '<fieldset id="RegistrationIdent">',
            'lg_ident' => sprintf('<legend>%s</legend>', __('Client Identification')),
            'ident_kind' => [
                'method' => 'control',
                'parameters' => ['ident_kind', [
                    'type' => 'select',
                    'label' => [
                        'text' => __('Kind') . ':',
                        'class' => 'active'
                    ],
                    'options' => Configure::read('identKinds'),
                    'class' => 'browser-default',
                ]]
            ],
            'ident_no' => [
                'method' => 'control',
                'parameters' => ['ident_no', [
                    'type' => 'text',
                    'label' => __('No.') . ':',
                ]]
            ],
            'fs_ident_end' => '</fieldset>',


            'fs_dates_start' => '<fieldset id="RegistrationDates">',
            'lg_dates' => sprintf('<legend>%s</legend>', __('Registration Dates')),
            'kind' => [
                'method' => 'control',
                'parameters' => ['kind', [
                    'type' => 'select',
                    'label' => [
                        'text' => __('Kind') . ':',
                        'class' => 'active'
                    ],
                    'options' => [
                        'P' => __('Registration'),
                        'S' => __('Cancellation')
                    ],
                    'class' => 'browser-default'
                ]]
            ],
            'start' => [
                'method' => 'control',
                'parameters' => ['start', [
                    'type' => 'date',
                    'label' => __('Start') . ':',
                    'error' => [
                        'format' => __('Invalid Date Format.'),
                        'overlapDates' => __('Invalid or Overlapping Dates'),
                        'overlapReservations' => __('Overlapping with Reservations'),
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
                    ],
                    'default' => $this->getRequest()->getQuery('end') ?: new Time()
                ]]
            ],
            'fs_dates_end' => '</fieldset>',

            'fs_room_start' => '<fieldset>',
            'lg_room' => sprintf('<legend>%s</legend>', __('Room')),
            'room' => [
                'method' => 'control',
                'parameters' => ['room_id', [
                    'type' => 'select',
                    'label' => [
                        'text' => __('Room') . ':',
                        'class' => 'active'
                    ],
                    'class' => 'browser-default',
                    'options' => $rooms,
                ]]
            ],
            'service' => [
                'method' => 'control',
                'parameters' => ['service_id', [
                    'type' => 'select',
                    'label' => [
                        'text' => __('Service') . ':',
                        'class' => 'active'
                    ],
                    'empty' => __('None'),
                    'class' => 'browser-default',
                    'options' => $serviceTypes
                ]]
            ],
            'fs_room_end' => '</fieldset>',


            'fs_touristTax_start' => '<fieldset>',
            'lg_touristTax' => sprintf('<legend>%s</legend>', __('Tourist TAX')),
            'ttax_kind' => [
                'method' => 'control',
                'parameters' => ['ttax_kind', [
                    'type' => 'select',
                    'label' => [
                        'text' => __('Kind') . ':',
                        'class' => 'active'
                    ],
                    'class' => 'browser-default',
                    'options' => Configure::read('touristTaxKinds')
                ]]
            ],
            'ttax_amount' => [
                'method' => 'control',
                'parameters' => ['ttax_amount', [
                    'type' => 'number',
                    'step' => '.01',
                    'label' => __('Amount') . ':',
                ]]
            ],
            'fs_touristTax_end' => '</fieldset>',

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

echo $this->Lil->form($registrationsForm, 'Registrations.edit');
echo $this->Html->script('registrationsClientAutocomplete', ['block' => 'script']);
?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#RegistrationsEditForm").AutocompleteClient({
            clientAutoCompleteUrl: "<?php echo Router::url(['plugin' => 'LilCrm', 'controller' => 'Contacts', 'action' => 'autocomplete', '?' => ['detailed' => true, 'kind' => 'T']]); ?>",
            clientCheckedIconUrl: "<?php echo Router::url('/lil_crm/img/ico_contact_check.gif'); ?>",
            addPersonCaption: "<?= __('Add Person'); ?>",
            editPersonCaption: "<?= __('Edit Person'); ?>",
            addContactDialogUrl: "<?= Router::url(['plugin' => 'LilCrm', 'controller' => 'Contacts', 'action' => 'add', '?' => ['kind' => '__kind__']]); ?>",
            editContactDialogUrl: "<?= Router::url(['plugin' => 'LilCrm', 'controller' => 'Contacts', 'action' => 'edit', '__id__']); ?>",
        });
    });
</script>
