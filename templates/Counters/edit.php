<?php
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\Routing\Router;

$counterForm = [
    'title_for_layout' => $counter->isNew() ? __('Add Counter') : __('Edit Counter'),
    'menu' => [
        'delete' => [
            'title' => __('Delete'),
            'visible' => !$counter->isNew() && $this->getCurrentUser()->hasRole('admin'),
            'url' => [
                'action' => 'delete',
                $counter->id
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
                'parameters' => [$counter]
            ],
            'id' => [
                'method' => 'hidden',
                'parameters' => ['id']
            ],
            'company_id' => [
                'method' => 'hidden',
                'parameters' => ['company_id']
            ],
            'kind' => [
                'method' => 'hidden',
                'parameters' => ['kind', ['default' => 'V']]
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
            'template' => [
                'method' => 'control',
                'parameters' => ['template', [
                    'type' => 'text',
                    'label' => __('Reservation Template') . ':',
                ]]
            ],
            'counter' => [
                'method' => 'control',
                'parameters' => ['counter', [
                    'type' => 'text',
                    'label' => __('Last used no.') . ':',
                ]]
            ],
            'fs_basics_end' => '</fieldset>',

            'fs_invoices_start' => '<fieldset>',
            'lg_invoices' => sprintf('<legend>%s</legend>', __('Invoices')),
            'invoices_counter_id' => [
                'method' => 'control',
                'parameters' => ['invoices_counter_id', [
                    'type' => 'select',
                    'label' => [
                        'text' => __('Invoices Counter') . ':',
                        'class' => 'active',
                    ],
                    'empty' => '-- ' . __('select') . ' --',
                    'options' => $invoicesCounters,
                    'class' => 'browser-default'
                ]]
            ],
            'fs_invoices_end' => '</fieldset>',

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

echo $this->Lil->form($counterForm, 'Counters.edit');
