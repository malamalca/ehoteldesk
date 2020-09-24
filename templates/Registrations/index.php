<?php
    use Cake\Routing\Router;

    ////////////////////////////////////////////////////////////////////////////////////////////////
    $activeCounter = null;
    $popupCounters = [];

    ////////////////////////////////////////////////////////////////////////////////////////////////
    $dayCaption = (string)$filter['start'];

    if ($filter['start']->isToday()) {
        $dayCaption = __('Today');
    }

    $hiddenControls = sprintf('<input type="hidden" value="%1$s" id="input-date-start" />', $filter['start']->toDateString());
    $dayLink = $this->Html->link($dayCaption, '#', ['id' => 'filter-date', 'class' => 'datepicker no-autoinit', 'data-target' => 'dropdown-days']);

    unset($filter['end']);
    unset($filter['owner']);

    if ($filter['start']->isToday()) {
        unset($filter['start']);
    } else {
        $filter['start'] = $filter['start']->toDateString();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////
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

    $counterLink = $this->Html->link(
        $activeCounter->title,
        ['action' => 'filter'],
        ['class' => 'dropdown-trigger', 'id' => 'filter-counters', 'data-target' => 'dropdown-counters']
    );

    $popupCounters = $this->Lil->popup('counters', $popupCounters, true);

    ////////////////////////////////////////////////////////////////////////////////////////////////
    $popupRooms = ['items' => [[
        'title' => __('All Rooms'),
        'url' => array_replace_recursive(['?' => ['filter' => $filter]], ['?' => ['filter' => ['room' => null]]]),
        'active' => empty($filter['room'])
    ]]];
    foreach ($rooms as $roomId => $roomTitle) {
        $popupRooms['items'][] = [
            'title' => $roomTitle,
            'url' => array_replace_recursive(['?' => ['filter' => $filter]], ['?' => ['filter' => ['room' => $roomId]]]),
            'active' => (!empty($filter['room'])) ? ($filter['room'] == $roomId) : false
        ];
    }

    $roomTitle = __('all rooms');
    if (!empty($filter['room']) && isset($rooms[$filter['room']])) {
        $roomTitle = $rooms[$filter['room']];
    }
    $roomLink = $this->Html->link(
        $roomTitle,
        ['action' => 'filter'],
        ['class' => 'dropdown-trigger', 'id' => 'filter-rooms', 'data-target' => 'dropdown-rooms']
    );
    $popupRooms = $this->Lil->popup('rooms', $popupRooms, true);


    $title = __('Registrations for {0} in {1} for {2}', $roomLink, $counterLink, $dayLink);


    $registrationsIndex = [
        'title' => $title,
        'menu' => [
            'add' => [
                'title' => __('Add'),
                'visible' => $this->getCurrentUser()->hasRole('admin'),
                'url' => [
                    'action' => 'add',
                    '?' => ['counter' => $filter['counter']]
                ]
            ],
        ],
        'actions' => ['lines' => [$hiddenControls, $popupRooms, $popupCounters]],
        'table' => [
            'parameters' => [
                'width' => '100%', 'cellspacing' => 0, 'cellpadding' => 0,
                'id' => 'RegistrationsIndex', 'class' => 'table index'
            ],
            'head' => [
                'parameters' => ['class' => 'text-primary'],
                'rows' => [['columns' => [
                    'span' => __('Start - End'),
                    'kind' => __('Kind'),
                    'client' => __('Client'),
                    'room' => __('Room'),
                    'service' => __('Service'),
                    'actions' => '&nbsp;'
                ]]]
            ],
        ]
    ];

    if ($registrations->isEmpty()) {
        $registrationsIndex['table']['body']['rows'][]['columns'] = [
            'empty' => [
                'html' => __('No registrations found.')
            ], '', '', '', '', ''
        ];
    } else {
        foreach ($registrations as $registration) {
            $registrationsIndex['table']['body']['rows'][]['columns'] = [
                'span' => ($registration->start . ' - ' . $registration->end),
                'kind' => $registration->kind == 'S' ? __('Cancellation') : __('Registration'),
                'client' => h($registration->surname) . ' ' . h($registration->name) . ' ' .
                    $this->Html->image('/lil_crm/img/goto.gif', [
                        'alt' => __('Goto Client'),
                        'url' => ['plugin' => 'LilCrm', 'controller' => 'Contacts', 'action' => 'view', $registration->client_id, 'kind' => 'T']
                    ]) .
                    sprintf('<div class="small">%1$s, %2$s, %3$s</div>', $registration->street, $registration->city, $registration->country_code),
                'room' => h($registration->room->toString()),
                'service' => isset($registration->service_type) ? h($registration->service_type->title) : '',
                'actions' => [
                    'params' => ['class' => 'actions text-right'],
                    'html' =>
                        $this->Lil->viewLink($registration->id) .
                        $this->Lil->editLink($registration->id) .
                        $this->Lil->deleteLink($registration->id)
                ]
            ];
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////
    // call plugin handlers and output data
    echo $this->Lil->index($registrationsIndex, 'Registrations.index');
?>
<script type="text/javascript">
    var registrationsUrl = "<?php echo Router::url([
        'action' => 'index',
        '?' => ['filter' => array_replace_recursive($filter, ['start' => '__start__'])]
    ]); ?>";

    $(document).ready(function() {
        // dates picker

        $(".datepicker")
            .on("click", function(e) { e.preventDefault(); return false; })
            .datepicker({
                firstDay: 1,
                dateFormat: 'yy-mm-dd',
                onSelect: function(day) {
                    const dateWithOffest = new Date(day.getTime() - (day.getTimezoneOffset() * 60000))
                    document.location.href = registrationsUrl.replace("__start__", dateWithOffest.toISOString().slice(0, 10).replace('T', ' '));
                },
                onOpen: function() {
                    this.gotoDate($("input#input-date-start").val());
                }
            });

        /*$("#link-date-start").click(function() {
            $("#input-date-start").datepicker('show');
            return false;
        });*/

    });
</script>
