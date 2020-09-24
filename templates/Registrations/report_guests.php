<?php
use Cake\I18n\Time;

$reportForm = [
    'title_for_layout' => __('REPORT: Guests'),
    'form' => [
        'defaultHelper' => $this->Form,
        'pre' => '<div class="form">' . PHP_EOL,
        'post' => '</div>',
        'lines' => [
            'form_start' => [
                'method' => 'create',
                'parameters' => [null, ['type' => 'GET']]
            ],

            'counter' => [
                'method' => 'control',
                'parameters' => ['counter', [
                    'type' => 'select',
                    'label' => __('Counter') . ':',
                    'options' => $counters
                ]]
            ],
            'date' => [
                'method' => 'control',
                'parameters' => ['on', [
                    'type' => 'lil-date',
                    'label' => __('Registration Date') . ':',
                    'default' => new Time()
                ]]
            ],

            'submit' => [
                'method' => 'submit',
                'parameters' => [
                    'label' => __('Print')
                ]
            ],
            'form_end' => [
                'method' => 'end',
                'parameters' => []
            ],
        ]
    ]
];

echo $this->Lil->form($reportForm, 'Registrations.reportGuests');
