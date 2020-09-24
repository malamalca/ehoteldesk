<?php
    $countersIndex = [
        'title_for_layout' => __('Counters List'),
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
            'post' => '</div>',
            'parameters' => [
                'width' => '100%', 'cellspacing' => 0, 'cellpadding' => 0,
                'id' => 'CountersIndex', 'class' => 'table index'
            ],
            'head' => [
                'parameters' => ['class' => 'text-primary'],
                'rows' => [['columns' => [
                    'title' => __('Title'),
                    'actions' => [
                        'params' => ['class' => 'text-right actions'],
                        'html' => '&nbsp;'
                    ]
                ]]]
            ],
        ]
    ];

    foreach ($counters as $counter) {
        $countersIndex['table']['body']['rows'][]['columns'] = [
            'title' => [
                'html' => h($counter->title)
            ],
            'actions' => [
                'params' => ['class' => 'text-right actions'],
                'html' => $this->Lil->editLink($counter->id) .
                    $this->Lil->deleteLink($counter->id)
            ]
        ];
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////
    // call plugin handlers and output data
    echo $this->Lil->index($countersIndex, 'Counters.index');
