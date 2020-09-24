<?php
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\Routing\Router;

$serviceForm = [
    'title_for_layout' => $serviceType->isNew() ? __('Add Service Type') : __('Edit Service Type'),
    'menu' => [
        'delete' => [
            'title' => __('Delete'),
            'visible' => !$serviceType->isNew() && $this->getCurrentUser()->hasRole('admin'),
            'url' => [
                'action' => 'delete',
                $serviceType->id
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
                'parameters' => [$serviceType]
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
            'title' => [
                'method' => 'control',
                'parameters' => ['title', [
                    'type' => 'text',
                    'label' => __('Title') . ':',
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

echo $this->Lil->form($serviceForm, 'ServiceTypes.edit');
