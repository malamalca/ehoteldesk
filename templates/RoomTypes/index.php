<?php
    $pageTitle = '';
    $roomIndex = [
        'title_for_layout' => __('Room Types List'),
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
                'id' => 'RoomTypesIndex', 'class' => 'table index'
            ],
            'head' => [
                'parameters' => ['class' => 'text-primary'],
                'rows' => [
                    ['columns' =>
                        [
                            'title' => __('Title'), ''
                        ],
            ]]],
        ]
    ];

    foreach ($roomTypes as $room) {
        $roomIndex['table']['body']['rows'][]['columns'] = [
            'title' => [
                'html' => h($room->title)
            ],
            'actions' => [
                'params' => ['class' => 'actions text-right'],
                'html' => $this->Lil->editLink($room->id) .
                    $this->Lil->deleteLink($room->id)
            ]
        ];
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////
    // call plugin handlers and output data
    echo $this->Lil->index($roomIndex, 'RoomTypes.index');
