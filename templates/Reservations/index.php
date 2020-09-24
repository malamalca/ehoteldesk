<?php
    use Cake\Core\Configure;
    use Cake\I18n\Date;
    use Cake\I18n\FrozenDate;
    use Cake\I18n\Time;
    use Cake\Routing\Router;

    $startDate = new Time();
    $startDate->year = (int)$filter['year'];
    $startDate->month = (int)$filter['month'];
    $startDate->addDays(-5);

    // workaround - can set Time object's setters but cannot set date object's setter
    $startDate = new Date($startDate->toDateString());

    $endDate = new Date($startDate->toDateString());
    //$endDate->addMonth(1);
    $endDate->addDays(28);

    $noDays = $startDate->diffInDays($endDate);

    ////////////////////////////////////////////////////////////////////////////////////////////////
    foreach ($counters as $counter) {
        $popupCounters['items'][] = [
            'title' => h($counter->title),
            'url' => ['?' => array_replace_recursive($filter, ['counter' => $counter->id])],
            'active' => $filter['counter'] == $counter->id
        ];
        if ($filter['counter'] == $counter->id) {
            $activeCounter = $counter;
        }
    }

    $counterLink = $this->Html->link(
        $activeCounter->title,
        ['action' => 'filter'],
        ['class' => 'dropdown-trigger', 'id' => 'filter-counters', 'data-target' => 'dropdown-counters']
    );

    $popupCounters = $this->Lil->popup('counters', $popupCounters, true);

    ////////////////////////////////////////////////////////////////////////////////////////////////
    $months = [];
    $popupMonths = ['items' => []];
    $tm = new Time();
    $tm->day = 1;
    $tm->month = 1;
    $tm->year = 2000;

    for ($i = 1; $i <= 12; $i++) {
        $months[$i] = $tm->i18nFormat('MMMM');
        $popupMonths['items'][] = [
            'title' => $months[$i],
            'url' => ['?' => array_replace_recursive($filter, ['month' => $i])],
            'active' => (!empty($filter['month'])) ? ($filter['month'] == $i) : false
        ];
        $tm->addMonth();
    }

    $monthLink = $this->Html->link(
        $months[(int)$filter['month']],
        ['action' => 'filter'],
        ['class' => 'dropdown-trigger', 'id' => 'filter-months', 'data-target' => 'dropdown-months']
    );

    $popupMonths = $this->Lil->popup('months', $popupMonths, true);

    ////////////////////////////////////////////////////////////////////////////////////////////////
    $popupYears = ['items' => []];
    $now = new FrozenDate();
    for ($i = $minYear; $i <= $now->year + 1; $i++) {
        $popupYears['items'][] = [
            'title' => (string)$i,
            'url' => ['?' => array_replace_recursive($filter, ['year' => (int)$i])],
            'active' => (!empty($filter['year'])) ? ($filter['year'] == $i) : false
        ];
    }

    $yearLink = $this->Html->link(
        $filter['year'],
        ['action' => 'filter'],
        ['class' => 'dropdown-trigger', 'id' => 'filter-years', 'data-target' => 'dropdown-years']
    );

    $popupYears = $this->Lil->popup('years', $popupYears, true);


    ////////////////////////////////////////////////////////////////////////////////////////////////
    $title = __('Reservations for {0} for {1} {2}', $counterLink, $monthLink, $yearLink);

    $reservationsOverview = [
        'title' => $title,
        'menu' => [
            'add' => [
                'title' => __('Add'),
                'visible' => $this->getCurrentUser()->hasRole('admin'),
                'url' => [
                    'action' => 'add',
                    'counter' => $filter['counter']
                ]
            ],
        ],
        'actions' => ['lines' => [$popupCounters, $popupMonths, $popupYears]],
        'table' => [
            'parameters' => [
                'cellspacing' => 0,
                'cellpadding' => 0,
                'id' => 'ReservationsOverview',
                'class' => 'index-static',
                'style' => 'table-layout: fixed;'
            ],
            'head' => [
                'rows' => [
                    0 => [
                        'columns' => [
                            ['html' => '&nbsp;'],
                            ['params' => ['class' => 'center-align'], 'html' => __('Beds')]
                        ]
                    ]
                ]
            ],
            'foot' => [
                'rows' => [
                    0 => [
                        'columns' => [
                            [
                                'parameters' => ['colspan' => ($noDays * 2) + 2],
                                'html' => '&nbsp;'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    // header column
    $aDate = new Time($startDate);

    for ($i = 0; $i < $noDays; $i++) {
        $reservationsOverview['table']['head']['rows'][0]['columns'][] = [
           'parameters' => ['class' => 'day', 'colspan' => '2', 'style' => 'width:30px;'],
           'html' => '<span>' . $aDate->i18nFormat('ccc, dd.MM') . '</span>'
        ];
        $aDate->addDay();
    }


    $reId = null;
    $colCounter = 2;

    foreach ($rooms as $room) {
        if (empty($filter['room']) || in_array($room->id, (array)$filter['room'])) {
            $reservationsOverview['table']['body']['rows'][$room->id]['columns'][0] = [
                'parameters' => ['class' => 'nowrap room room_' . $room->id],
                'html' => $room->toString()
            ];
            $reservationsOverview['table']['body']['rows'][$room->id]['columns'][1] = [
                'parameters' => ['class' => 'center nowrap main room_' . $room->id],
                'html' => $room->beds
            ];

            $aDate = null;
            $aDate = new Time($startDate);
            $reId = null;

            for ($i = 0; $i < $noDays; $i++) {
                $emptyDay = true;

                ////////////////////////////////////////////////////////////////////////////////////////
                if (isset($reservations[$room->id][$aDate->toDateString()])) {
                    $reservation = $reservations[$room->id][$aDate->toDateString()]['main'];

                    if ($aDate->isSameDay($reservation->end) || $aDate->isSameDay($startDate->toImmutable()->addDays($noDays))) {
                        // has reservation started before <table> start date???
                        $diffStart = max($reservation->start, $startDate);

                        // has another reservation ended on the day this one has started ??
                        if (empty($reservations[$room->id][$diffStart->toDateString()]['afternoon']) && $reservation->start->gte($startDate) && empty($registrations[$room->id][$diffStart->toDateString()])) {
                            // add empty first half
                            $cellHtml = '&nbsp;';
                            $cellClass = 'day day_' . $diffStart->format('Y-m-d') . ' day_half';
                            $colspan = 1;
                            $reservationsOverview['table']['body']['rows'][$room->id]['columns'][$colCounter] = [
                               'parameters' => ['class' => $cellClass, 'colspan' => $colspan],
                               'html' => $cellHtml
                            ];
                            $colCounter++;
                        }

                        $colspan = min($reservation->end->diffInDays($diffStart), $aDate->diffInDays($startDate->toImmutable()->addDays($noDays)));
                        $colspan = $colspan * 2;
                        if ($reservation->start->lt($startDate)) {
                            $colspan++; // special case when reservation starts before <table> start date
                        }

                        $cellHtml = $this->Html->link(
                            $reservation->name . ' (' . $reservation->persons . ')',
                            ['action' => 'view', $reservation->id],
                            ['style' => sprintf('width: %dpx', $colspan * 15)]
                        );

                        $cellClass = 'day day_' . $aDate->format('Y-m-d');
                        $cellClass .= ' reserved';

                        $reservationsOverview['table']['body']['rows'][$room->id]['columns'][$colCounter] = [
                           'parameters' => ['class' => $cellClass, 'colspan' => $colspan, 'style' => sprintf('width: %dpx', $colspan * 15)],
                           'html' => $cellHtml
                        ];
                        $colCounter += $colspan;

                        if (empty($reservations[$room->id][$aDate->toDateString()]['afternoon']) && empty($registrations[$room->id][$aDate->toDateString()])) {
                                // add empty second half
                                $cellHtml = '&nbsp;';
                                $cellClass = 'day day_' . $aDate->format('Y-m-d') . ' day_half';
                                $colspan = 1;
                                $reservationsOverview['table']['body']['rows'][$room->id]['columns'][$colCounter] = [
                                   'parameters' => ['class' => $cellClass, 'colspan' => $colspan],
                                   'html' => $cellHtml
                                ];
                                $colCounter++;
                        }

                        $reId = null;
                    }
                    $emptyDay = false;
                }

                ////////////////////////////////////////////////////////////////////////////////////////

                if (isset($registrations[$room->id][$aDate->toDateString()])) {
                    $occupancy = $registrations[$room->id][$aDate->toDateString()];

                    if (empty($occupancy['main'])) {
                        $occupancy['main'] = 0;
                    }

                    if (!empty($occupancy['morning'])) {
                        // do a real cell cell
                        $colspan = 1;
                        $cellHtml = $this->Html->link(
                            $occupancy['morning'] + $occupancy['main'],
                            [
                            'controller' => 'Registrations',
                            'action' => 'index',
                            '?' => ['start' => $aDate->toDateString(), 'room' => $room->id]
                            ],
                            ['style' => sprintf('width: %dpx', $colspan * 15)]
                        );
                        $cellClass = 'day day_' . $aDate->format('Y-m-d') . ' day_half registered';

                        $reservationsOverview['table']['body']['rows'][$room->id]['columns'][$colCounter] = [
                           'parameters' => ['class' => $cellClass, 'colspan' => $colspan],
                           'html' => $cellHtml
                        ];
                        $colCounter++;

                        // do an empty cell
                        if (empty($occupancy['afternoon']) && empty($occupancy['main']) && empty($reservations[$room->id][$aDate->toDateString()])) {
                                $cellHtml = '&nbsp;';
                                $cellClass = 'day day_' . $aDate->format('Y-m-d') . ' day_half';
                                $colspan = 1;
                                $reservationsOverview['table']['body']['rows'][$room->id]['columns'][$colCounter] = [
                                   'parameters' => ['class' => $cellClass, 'colspan' => $colspan],
                                   'html' => $cellHtml
                                ];
                                $colCounter++;
                        }
                    }
                    if (!empty($occupancy['afternoon'])) {
                        // do an empty cell
                        if (empty($occupancy['morning']) && empty($occupancy['main']) && empty($reservations[$room->id][$aDate->toDateString()])) {
                            $cellHtml = '&nbsp;';
                            $cellClass = 'day day_' . $aDate->format('Y-m-d') . ' day_half';
                            $colspan = 1;
                            $reservationsOverview['table']['body']['rows'][$room->id]['columns'][$colCounter] = [
                               'parameters' => ['class' => $cellClass, 'colspan' => $colspan],
                               'html' => $cellHtml
                            ];
                            $colCounter++;
                        }

                        // do a real cell cell
                        $colspan = 1;
                        $cellHtml = $this->Html->link(
                            $occupancy['afternoon'] + $occupancy['main'],
                            [
                            'controller' => 'Registrations',
                            'action' => 'index',
                            '?' => ['start' => $aDate->toDateString(), 'room' => $room->id]
                            ],
                            ['style' => sprintf('width: %dpx', $colspan * 15)]
                        );
                        $cellClass = 'day day_' . $aDate->format('Y-m-d') . ' day_half registered';

                        $reservationsOverview['table']['body']['rows'][$room->id]['columns'][$colCounter] = [
                           'parameters' => ['class' => $cellClass, 'colspan' => $colspan],
                           'html' => $cellHtml
                        ];
                        $colCounter++;
                    }
                    if (empty($occupancy['morning']) && empty($occupancy['afternoon'])) {
                        // occupancy main only
                        $colspan = 2;
                        $cellHtml = $this->Html->link(
                            $occupancy['main'],
                            [
                            'controller' => 'Registrations',
                            'action' => 'index',
                            '?' => ['start' => $aDate->toDateString(), 'room' => $room->id]
                            ],
                            ['style' => sprintf('width: %dpx', $colspan * 15)]
                        );
                        $cellClass = 'day day_' . $aDate->format('Y-m-d') . ' registered';

                        $reservationsOverview['table']['body']['rows'][$room->id]['columns'][$colCounter] = [
                           'parameters' => ['class' => $cellClass, 'colspan' => $colspan],
                           'html' => $cellHtml
                        ];
                        $colCounter += 2;
                    }


                    $emptyDay = false;
                }

                ////////////////////////////////////////////////////////////////////////////////////////

                if ($emptyDay) {
                    // empty day
                    $cellHtml = '&nbsp;';
                    $cellClass = 'day day_' . $aDate->format('Y-m-d');
                    $colspan = 2;

                    $reservationsOverview['table']['body']['rows'][$room->id]['columns'][$colCounter] = [
                       'parameters' => ['class' => $cellClass, 'colspan' => $colspan],
                       'html' => $cellHtml
                    ];

                    $colCounter += 2;

                    unset($lastReservation);
                }

                $aDate->addDay();
            }
        }
    }

    echo $this->Lil->index($reservationsOverview, 'Reservations.index');

    if ($this->getCurrentUser()->hasRole('admin')) {
        echo $this->Html->script('reservations_overview');
?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#ReservationsOverview").ReservationsOverview({
            title: "<?php echo __('Enter New Reservation'); ?>",
            url: "<?php echo Router::url([
                'controller' => 'Reservations',
                'action' => 'add',
                '?' => [
                    'room' => '__room__',
                    'start' => '__start__',
                    'end' => '__end__',
                ],
            ], true); ?>"
        });
    });
</script>

<?php
    } // check user level
?>
