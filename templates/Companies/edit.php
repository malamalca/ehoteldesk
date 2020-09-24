<?php
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\Routing\Router;

$companyForm = [
    'title_for_layout' => $company->isNew() ? __('Add Company') : __('Edit Company'),
    'menu' => [
        'delete' => [
            'title' => __('Delete'),
            'visible' => !$company->isNew() && $this->getCurrentUser()->hasRole('root'),
            'url' => [
                'action' => 'delete',
                $company->id
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
                'parameters' => [$company]
            ],
            'id' => [
                'method' => 'hidden',
                'parameters' => ['id']
            ],
            'referer' => [
                'method' => 'hidden',
                'parameters' => ['referer', [
                    'default' => $this->getRequest()->referer()
                ]]
            ],

            'fs_basics_start' => '<fieldset>',
            'lg_basics' => sprintf('<legend>%s</legend>', __('Basics')),
            'name' => [
                'method' => 'control',
                'parameters' => ['name', [
                    'type' => 'text',
                    'label' => __('Title') . ':',
                ]]
            ],
            'street' => [
                'method' => 'control',
                'parameters' => ['street', [
                    'type' => 'text',
                    'label' => __('Street') . ':',
                ]]
            ],
            'zip' => [
                'method' => 'control',
                'parameters' => ['zip', [
                    'type' => 'text',
                    'label' => __('Zip') . ':',
                ]]
            ],
            'city' => [
                'method' => 'control',
                'parameters' => ['city', [
                    'type' => 'text',
                    'label' => __('City') . ':',
                ]]
            ],
            'tax_no' => [
                'method' => 'control',
                'parameters' => ['tax_no', [
                    'type' => 'text',
                    'label' => __('Tax no.') . ':',
                ]]
            ],
            'tax_status' => [
                'method' => 'control',
                'parameters' => ['tax_status', [
                    'type' => 'select',
                    'label' => __('Tax status') . ':',
                    'options' => [
                        1 => __('Registered for TAX reporting'),
                        0 => __('Not registered for TAX reporting')
                    ]
                ]]
            ],
            'fs_basics_end' => '</fieldset>',

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

echo $this->Lil->form($companyForm, 'Companies.edit');
