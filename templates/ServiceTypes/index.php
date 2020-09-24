<?php
    $serviceIndex = [
        'title_for_layout' => __('Service Types List'),
        'menu' => [
            'add' => [
                'title' => __('Add'),
                'visible' => $this->getCurrentUser()->hasRole('admin'),
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
                'id' => 'ServiceTypesIndex', 'class' => 'table index'
            ],
            'head' => [
                'parameters' => ['class' => 'text-primary'],
                'rows' => [['columns' => [
                    'title' => __('Title'),
                    'actions' => [
                        'params' => ['class' => 'text-right actions'],
                        'html' => ''
                    ]
                ]]]
            ],
        ]
    ];

    foreach ($serviceTypes as $service) {
        $serviceIndex['table']['body']['rows'][]['columns'] = [
            'title' => [
                'html' => h($service->title)
            ],
            'actions' => [
                'params' => ['class' => 'text-right actions'],
                'html' => $this->Lil->editLink($service->id) .
                    $this->Lil->deleteLink($service->id)
            ]
        ];
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////
    // call plugin handlers and output data
    echo $this->Lil->index($serviceIndex, 'ServiceTypes.index');
