<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

$documentTypes = Configure::read('LilInvoices.documentTypes');

// FILTER by active
$activeLink = $this->Html->link(
    empty($filter['inactive']) ? __d('lil_invoices', 'Active') : __d('lil_invoices', 'All'),
    ['action' => 'filter'],
    ['class' => 'dropdown-trigger', 'id' => 'filter-active', 'data-target' => 'dropdown-active']
);
$popupActive = ['items' => [
    ['title' => __d('lil_invoices', 'Active'), 'url' => ['?' => array_merge($this->getRequest()->getQuery(), ['inactive' => null])]],
    ['title' => __d('lil_invoices', 'All'), 'url' => ['?' => array_merge($this->getRequest()->getQuery(), ['inactive' => 1])]],
]];

// FILTER by doc_type
$typeLink = $this->Html->link(
    empty($filter['type']) ? __d('lil_invoices', 'all types') : $documentTypes[$filter['type']],
    ['action' => 'filter'],
    ['class' => 'dropdown-trigger', 'id' => 'filter-type', 'data-target' => 'dropdown-type']
);

$popupType = ['items' => [
    ['title' => __d('lil_invoices', 'All'), 'url' => ['?' => array_merge($this->getRequest()->getQuery(), ['type' => null])]],
]];
foreach ($documentTypes as $docId => $docTitle) {
    $popupType['items'][$docId] = [
        'title' => $docTitle, 'url' => ['?' => array_merge($this->getRequest()->getQuery(), ['type' => $docId])],
    ];
}

$popupActive = $this->Lil->popup('active', $popupActive, true);
$popupType = $this->Lil->popup('type', $popupType, true);

$filterTitle = __d('lil_invoices', '{0} counters of {1}', [$activeLink, $typeLink]);

$countersIndex = [
    'title_for_layout' => $filterTitle,
    'menu' => [
        'add' => [
            'title' => __d('lil_invoices', 'Add'),
            'visible' => true,
            'url' => [
                'plugin' => 'LilInvoices',
                'controller' => 'InvoicesCounters',
                'action' => 'add',
            ],
        ],
    ],
    'actions' => ['lines' => [$popupActive, $popupType]],
    'table' => [
        'pre' => $this->Lil->searchPanel($this->getRequest()->getQuery('search', '')),
        'parameters' => [
            'width' => '100%', 'cellspacing' => 0, 'cellpadding' => 0, 'id' => 'InvoicesCountersIndex',
        ],
        'head' => ['rows' => [['columns' => [
            'title' => $this->Paginator->sort('title', __d('lil_invoices', 'Title')),
            'doc_type' => $this->Paginator->sort('doc_type', __d('lil_invoices', 'Type')),
            'no' => [
                'parameters' => ['class' => 'right-align'],
                'html' => __d('lil_invoices', 'Last no'),
             ],
             'actions' => [],
        ]]]],
        'foot' => ['rows' => [['columns' => [
            'paginator' => [
                'params' => ['colspan' => 4],
                'html' => '<ul class="paginator">' . $this->Paginator->numbers([
                    'first' => '<<',
                    'last' => '>>',
                    'modulus' => 3]) . '</ul>',
            ],
        ]]]],
    ],
];

foreach ($counters as $counter) {
    $countersIndex['table']['body']['rows'][]['columns'] = [
        'descript' => $this->Html->link($counter->title, ['controller' => 'Invoices', 'action' => 'index', '?' => ['counter' => $counter->id]]),
        'doc_type' => isset($documentTypes[$counter->doc_type]) ? h($documentTypes[$counter->doc_type]) : __d('lil_invoices', 'N/A'),
        'no' => [
            'parameters' => ['class' => 'right-align'],
            'html' => $this->Number->precision($counter->counter, 0),
        ],
        'actions' => [
            'parameters' => ['class' => 'right-align'],
            'html' =>
                $this->Html->link(
                    '<i class="material-icons chevron">list</i>',
                    ['controller' => 'Invoices', 'action' => 'index', '?' => ['counter' => $counter->id]],
                    ['escape' => false, 'class' => 'btn btn-small btn-floating waves-effect waves-light waves-circle']
                ) . ' ' .
                $this->Lil->editLink($counter->id) . ' ' . $this->Lil->deleteLink($counter->id),
        ],
    ];
}

echo $this->Lil->index($countersIndex, 'LilInvoices.InvoicesCounters.index');
?>
<script type="text/javascript">
    var searchUrl = "<?php echo Router::url([
        'plugin' => 'LilInvoices',
        'controller' => 'InvoicesCounters',
        'action' => 'index',
        '?' => array_merge($this->getRequest()->getQuery(), ['search' => '__term__']),
    ]); ?>";

    $(document).ready(function() {
        ////////////////////////////////////////////////////////////////////////////////////////////
        // Filter for invoices
        $(".search-panel input").on("input", function(e) {
            var rx_term = new RegExp("__term__", "i");
            $.get(searchUrl.replace(rx_term, encodeURIComponent($(this).val())), function(response) {
                let tTable = $("<body>" + response + "</body>").filter("#InvoicesCountersIndex").html();
                $("#InvoicesCountersIndex").html(tTable);
            });
        });

        $(".search-panel input").focus();
    });
</script>