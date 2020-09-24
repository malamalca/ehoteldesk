<?php
use Cake\I18n\Time;

$reportForm = [
    'title_for_layout' => __('REPORT: Vacancies'),
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
            'break-1' => '<br />',

            'fs_date_start' => '<fieldset>',
            'lg_date' => sprintf('<legend>%s</legend>', $this->Form->radio('span', ['date' => __('Date')], ['value' => 'date', 'hiddenField' => false]) ),
            'date' => [
                'method' => 'control',
                'parameters' => ['on', [
                    'type' => 'lil-date',
                    'label' => false,
                    'default' => new Time()
                ]]
            ],
            'fs_date_end' => '</fieldset>',

            'fs_month_start' => '<fieldset>',
            'lg__month' => sprintf('<legend>%s</legend>', $this->Form->radio('span', ['month' => __('Month')], ['value' => 'date', 'hiddenField' => false]) ),
            'month' => [
                'method' => 'control',
                'parameters' => ['month', [
                    'type' => 'month',
                    'label' => __('Month') . ':',
                    'default' => new Time(),
                    'empty' => false
                ]]
            ],
            'year' => [
                'method' => 'control',
                'parameters' => ['month', [
                    'type' => 'year',
                    'label' => __('Year') . ':',
                    'default' => new Time(),
                    'empty' => false
                ]]
            ],
            'fs__month_end' => '</fieldset>',

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

echo $this->Lil->form($reportForm, 'Registrations.reportServices');
