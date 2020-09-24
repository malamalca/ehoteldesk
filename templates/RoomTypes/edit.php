<?php
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\Routing\Router;

$roomForm = [
    'title_for_layout' => $roomType->isNew() ? __('Add Room Type') : __('Edit Room Type'),
    'menu' => [
        'delete' => [
            'title' => __('Delete'),
            'visible' => !$roomType->isNew() && $this->getCurrentUser()->hasRole('admin'),
            'url' => [
                'action' => 'delete',
                $roomType->id
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
                'parameters' => [$roomType]
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

echo $this->Lil->form($roomForm, 'RoomTypes.edit');
