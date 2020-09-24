<?php
    $pageTitle = __('Room List');
    $roomIndex = [
        'title_for_layout' => __('Room List'),
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
                'id' => 'RoomsIndex', 'class' => 'table index'
            ],
            'head' => [
                'parameters' => ['class' => 'text-primary'],
                'rows' => [['columns' => [
                    'title' => __('Title'),
                    'type' => [
                        'parameters' => ['class' => 'text-center'],
                        'html' => __('Type'),
                    ],
                    'beds' => [
                        'parameters' => ['class' => 'text-center'],
                        'html' => __('Beds')
                    ],
                    'actions' => [
                        'html' => ''
                    ]
                ]]]
            ],
        ]
    ];

    foreach ($rooms as $room) {
        $roomIndex['table']['body']['rows'][]['columns'] = [
            'title' => [
                'html' => $room->toString()
            ],
            'type' => [
                'parameters' => ['class' => 'text-center'],
                'html' => isset($roomTypes[$room->room_type_id]) ? h($roomTypes[$room->room_type_id]) : '-',
            ],
            'beds' => [
                'parameters' => ['class' => 'text-center'],
                'html' => $room->beds
            ],
            'actions' => [
                'params' => ['class' => 'actions text-right text-nowrap'],
                'html' => $this->Lil->editLink($room->id) .
                    $this->Lil->deleteLink($room->id)
            ]
        ];
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////
    // call plugin handlers and output data
    echo $this->Lil->index($roomIndex, 'Rooms.index');
