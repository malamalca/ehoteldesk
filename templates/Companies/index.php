<?php
    $companyIndex = [
        'title_for_layout' => __('Companies'),
        'menu' => [
            'add' => [
                'title' => __('Add'),
                'visible' => $this->getCurrentUser()->hasRole('root'),
                'url' => [
                    'action' => 'add',
                ]
            ]
        ],
        'table' => [
            'pre' => '<div class="table-responsive">' . PHP_EOL,
            'post' => '</div>' . PHP_EOL,
            'parameters' => [
                'width' => '100%', 'cellspacing' => 0, 'cellpadding' => 0,
                'id' => 'RoomTypesIndex', 'class' => 'table index'
            ],
            'head' => [
                'parameters' => ['class' => 'text-primary'],
                'rows' => [
                    ['columns' =>
                        [
                            'title' => __('Title'),
                            'street' => __('Street'),
                            'zip' => __('Zip'),
                            'city' => __('City'),
                            'actions' => ''
                        ],
            ]]],
        ]
    ];

    foreach ($companies as $company) {
        $companyIndex['table']['body']['rows'][]['columns'] = [
            'title' => [
                'html' => $this->Html->link($company->name, ['action' => 'view', $company->id])
            ],
            'street' => [
                'html' => h($company->street)
            ],
            'zip' => [
                'html' => h($company->zip)
            ],
            'city' => [
                'html' => h($company->city)
            ],
            'actions' => [
                'params' => ['class' => 'text-right'],
                'html' => $this->Lil->editLink($company->id) .
                    $this->Lil->deleteLink($company->id)
            ]
        ];
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////
    // call plugin handlers and output data
    echo $this->Lil->index($companyIndex, 'Companies.index');
