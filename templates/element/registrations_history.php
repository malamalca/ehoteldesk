<?php

    ////////////////////////////////////////////////////////////////////////////////////////////////
    $activeCounter = null;
    $popupCounters = [];

    foreach ($counters as $counter) {
        $popupCounters['items'][] = [
            'title' => h($counter->title),
            'url' => array_replace_recursive(['filter' => $filter], ['filter' => ['counter' => $counter->id]]),
            'active' => $filter['counter'] == $counter->id
        ];
        if ($filter['counter'] == $counter->id) {
            $activeCounter = $counter;
        }
    }

    $this->Lil->popup('counters', $popupCounters);
    $counterLink = $this->Html->link(
        $activeCounter->title,
        array_replace_recursive(['filter' => $filter], ['filter' => ['counter' => $activeCounter->id]]),
        ['class' => 'popup_link', 'id' => 'popup_counters']
    );

    $pageTitle = __('Registrations History for {0}', $counterLink);

    $history = ['panels' => [0 => [
        'params' => ['id' => 'ContactsHistoryPanel'],
        'lines' => [
            'title' => ['html' => sprintf('<h2>%s</h2>', $pageTitle)],
            'table' => ['table' => [
                'parameters' => ['class' => 'index-static', 'style' => 'width: 700px'],
                'head' => ['rows' => [0 => ['columns' => [
                    __('Date Span'),
                    __('Room'),
                ]]]],
                'foot' => ['rows' => [0 => ['columns' => [
                    0 => ['parameters' => ['colspan' => 2], 'html' => '&nbsp;']
                ]]]]
            ]]
        ]
    ]]];

    if (count($registrations) == 0) {
        $history['panels'][0]['lines']['table'] = __('No registrations found.');
    } else {
        foreach ($registrations as $registration) {
            $history['panels'][0]['lines']['table']['table']['body']['rows'][0]['columns'] = [
                'date_span' => $this->Html->link($registration->start . ' - ' . $registration->end, [
                    'plugin' => false,
                    'controller' => 'Registrations',
                    'action' => 'edit',
                    $registration->id
                ]),
                'room' => h($registration->room->toString()),
            ];
        }
    }

    echo $this->Lil->panels($history);
