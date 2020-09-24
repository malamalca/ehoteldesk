<?php
use Cake\Core\Plugin;
use Cake\Core\Configure;
use Cake\Routing\Router;

$client = $counter->kind == 'issued' ? 'receiver' : 'issuer';

if ($invoice->isNew()) {
    $layoutTitle = __(
        'Add an Invoice #{0} <span class="light">({1})</span>',
        $counter->counter + 1,
        h($counter->title)
    );
} else {
    $layoutTitle = __(
        'Edit an Invoice #{0} <span class="light">({1})</span>',
        $invoice->counter,
        h($counter->title)
    );
}

$invoiceEdit = [
    'title_for_layout' => $layoutTitle,
    'form' => [
        'defaultHelper' => $this->Form,

        'pre' => '<div class="form">',
        'post' => '</div>',

        'lines' => [
            'form_start' => [
                'method' => 'create',
                'parameters' => [
                    $invoice, [
                        'type' => 'file',
                        'id' => 'InvoiceEditForm',
                        'url' => [
                            'plugin' => 'LilInvoices',
                            'controller' => 'Invoices',
                            'action' => $invoice->id ? 'edit' : 'add',
                            'filter' => ['counter' => $invoice->counter_id],
                            '_ext' => 'json'
                        ]
                    ]
                ]
            ],
            'referer' => [
                'method' => 'control',
                'parameters' => ['referer', ['type' => 'hidden']]
            ],
            'id' => [
                'method' => 'control',
                'parameters' => ['id', ['type' => 'hidden']]
            ],
            'user_id' => [
                'method' => 'control',
                'parameters' => ['user_id', ['type' => 'hidden']]
            ],
            'reservation_id' => [
                'method' => 'control',
                'parameters' => ['reservation_id', ['type' => 'hidden']]
            ],
            'counter_id' => [
                'method' => 'control',
                'parameters' => ['counter_id', ['type' => 'hidden']]
            ],
            'doc_type' => [
                'method' => 'control',
                'parameters' => ['doc_type', ['type' => 'hidden']]
            ],
            'counter' => [
                'method' => 'control',
                'parameters' => [
                    'counter', [
                        'type' => 'hidden',
                        'default' => $counter->counter + 1,
                    ]
                ]
            ],
            'duplicate' => [
                'method' => 'control',
                'parameters' => ['duplicate', ['type' => 'hidden']]
            ],
            'tpl_header_id' => [
                'method' => 'control',
                'parameters' => ['tpl_header_id', ['type' => 'hidden', 'default' => $counter->tpl_header_id]]
            ],
            'tpl_footer_id' => [
                'method' => 'control',
                'parameters' => ['tpl_footer_id', ['type' => 'hidden', 'default' => $counter->tpl_footer_id]]
            ],
            'tpl_body_id' => [
                'method' => 'control',
                'parameters' => ['tpl_body_id', ['type' => 'hidden', 'default' => $counter->tpl_body_id]]
            ],

            ////////////////////////////////////////////////////////////////////////////////////
            'fs_basic_start' => '<fieldset>',
            'fs_basic_legend' => sprintf('<legend>%s</legend>', __('Basics')),
            'title' => [
                'method' => 'control',
                'parameters' => ['title', ['type' => 'hidden']]
            ],

            'no' => [
                'method' => 'control',
                'parameters' => [
                    'field' => 'no', [
                        'label' => __('Invoice no') . ':',
                        'disabled' => !empty($counter->mask)
                    ]
                ]
            ],
            'location' => [
                'method' => 'control',
                'parameters' => [
                    'field' => 'location', ['type' => 'hidden']]
            ],
            'fs_basic_end' => '</fieldset>', // basics

            ////////////////////////////////////////////////////////////////////////////////////
            'fs_receiver_start' => '<fieldset id="invoice-receiver">',
            'fs_receiver_legend' => sprintf('<legend>%s</legend>', __('Receiver')),
            'receiver_title' => [
                'method' => 'control',
                'parameters' => [
                    'field' => 'receiver.title',
                    [
                        'type' => 'text',
                        'label' => __('Name/Surname OR Company Title') . ':'
                    ]
                ]
            ],
            'receiver_address' => [
                'method' => 'control',
                'parameters' => [
                    'field' => 'receiver.street',
                    [
                        'type' => 'text',
                        'label' => __('Street') . ':'
                    ]
                ]
            ],
            'fieldset-city-zip-start' => '<div class="input-field text">',
            'label-city-zip' => '<label for="receiver-zip" class="active">' . __('ZIP and City') . ':</label>',
            'receiver_zip' => [
                'method' => 'text',
                'parameters' => [
                    'field' => 'receiver.zip',
                    [
                        'type' => 'text',
                        'label' => __('ZIP') . ':',
                        'id' => 'receiver-zip'
                    ]
                ]
            ],
            'receiver_city' => [
                'method' => 'text',
                'parameters' => [
                    'field' => 'receiver.city',
                    [
                        'type' => 'text',
                        'label' => __('City') . ':',
                        'id' => 'receiver-city'
                    ]
                ]
            ],
            'fieldset-city-zip-end' => '</div>',
            'receiver_country_code' => [
                'method' => 'control',
                'parameters' => [
                    'field' => 'receiver.country_code',
                    [
                        'type' => 'select',
                        'options' => Configure::read('LilCrm.countries'),
                        'label' => __('Country') . ':',
                        'class' => 'browser-default'
                    ]
                ]
            ],

            'receiver_taxno' => [
                'method' => 'control',
                'parameters' => [
                    'field' => 'receiver.taxno',
                    [
                        'type' => 'text',
                        'label' => __('Tax no.') . ':'
                    ]
                ]
            ],
            'fs_receiver_end' => '</fieldset>', // receiver

            ////////////////////////////////////////////////////////////////////////////////////
            'fs_dates_start' => '<fieldset>',
            'fs_dates_legend' => sprintf('<legend>%s</legend>', __('Dates')),
            'fs_dates_table_start' => '<table id="invoice-dates-table"><tr><td>',
            'dat_issue' => [
                'method' => 'control',
                'parameters' => [
                    'dat_issue',
                    'options' => [
                        'type' => 'lil-date',
                        'label' => __('Date of issue') . ':',
                        'error' => [
                            'empty' => __('Blank')
                        ]
                    ],
                ]
            ],
            'fs_dates_col1_end' => '</td>',

            'fs_dates_table_end' => '</tr></table>',
            'fs_dates_end' => '</fieldset>',

            ////////////////////////////////////////////////////////////////////////////////////
            'fs_descript_start' => '<fieldset>',
            'fs_descript_legend' => sprintf('<legend>%s</legend>', __('Description')),
            'description' => [
                'method' => 'control',
                'parameters' => [
                    'descript',
                    'options' => [
                        'type' => 'textarea',
                        'label' => false,
                        'default' => $counter->template_descript
                    ]
                ]
            ],
            'fs_descript_end' => '</fieldset>',


            ////////////////////////////////////////////////////////////////////////////////////
            'submit' => [
                'method' => 'submit',
                'parameters' => [
                    __('Save')
                ]
            ],

            'form_end' => [
                'method' => 'end',
                'parameters' => []
            ],

            'progress_dialog' => '<div id="progress-dialog" style="display:none"></div>'
        ]
    ]
];


////////////////////////////////////////////////////////////////////////////////////////////////
// hidden client fields
$pluginPath = Plugin::path('LilInvoices');
require $pluginPath . 'templates' . DS . 'element' . DS . 'edit_client.php';
//$this->Lil->insertIntoArray($invoiceEdit['form']['lines'], clientFields('receiver', $invoice->receiver, true), ['after' => 'fs_basic_end']);
$this->Lil->insertIntoArray($invoiceEdit['form']['lines'], clientFields('issuer', $invoice->issuer, true), ['after' => 'fs_basic_end']);
$this->Lil->insertIntoArray($invoiceEdit['form']['lines'], clientFields('buyer', $invoice->buyer, true), ['after' => 'fs_basic_end']);

    ////////////////////////////////////////////////////////////////////////////////////////////////
    // additional dates - INVOICES ONLY
    $invoiceDates = [
        'fs_dates_col2_start' => '<td>',
        'dat_service' => [
            'method' => 'control',
            'parameters' => [
                'field' => 'dat_service',
                'options' => [
                    'type' => 'lil-date',
                    'label' => __('Service date') . ':',
                    'error' => ['empty' => __('Blank')]
                ]
            ]
        ],
        'fs_dates_col2_end' => '</td>',
        'fs_dates_col3_start' => '<td>',
        'dat_expire' => [
            'method' => 'control',
            'parameters' => [
                'field' => 'dat_expire',
                'options' => [
                    'type' => 'lil-date',
                    'label' => __('Expiration date') . ':',
                    'error' => ['empty' => __('Blank')]
                ]
            ]
        ],
        'fs_dates_col3_end' => '</td>',
    ];
    $this->Lil->insertIntoArray($invoiceEdit['form']['lines'], $invoiceDates, ['after' => 'fs_dates_col1_end']);

    ////////////////////////////////////////////////////////////////////////////////////////////////
    // analytics and taxes
    $analytics = [];
    $analytics['fs_analytics_start'] = '<fieldset>';
    $analytics['fs_analytics_legend'] = sprintf('<legend>%s</legend>', __('Analytics'));

    require $pluginPath . 'templates' . DS . 'element' . DS . 'edit_items.php';

    $analytics['fs_analytics_end'] = '</fieldset>';

    $this->Lil->insertIntoArray($invoiceEdit['form']['lines'], $analytics, ['after' => 'fs_dates_end']);


    echo $this->Html->script('/LilInvoices/js/invoice_edit_items');
    //echo $this->Html->script('/LilInvoices/js/invoice_edit');

    echo $this->Lil->form($invoiceEdit, 'Reservations.createInvoice');

?>


<script type="text/javascript">
    // constants for scripts
    var itemsAutocompleteUrl = '<?php echo Router::url(['plugin' => 'LilInvoices', 'controller' => 'Items', 'action' => 'autocomplete', '_ext' => 'json']); ?>';
    var toggleUnlinkItemConfirmation = '<?php echo __('Are you sure you want to unlink item?'); ?>';
    var onCompleteRedirectUrl = '<?php echo Router::url(['controller' => 'reservations', 'action' => 'view', $reservation->id]); ?>';

    function InfinityEditorSubmit(event) {
        $.ajax({
            type: "POST",
            url: $("#invoice-edit-form").prop("action"),
            data: $("#invoice-edit-form").serialize(), // serializes the form's elements.
            success: function(data) {
                if (data.errors) {
                    alert("<?= __('There are some errors. Please check your data and try again.'); ?>");
                } else {
                    document.location.href = onCompleteRedirectUrl;
                }
            },
            error: function(data) {
                alert("Saving invoice failed.");
            },
            complete: function() {
                //
            }
        });
       event.preventDefault();
       return false;
    }

    $(document).ready(function() {
        var applyDates = function(dateText, inst) {
            var dateVal = $(this).val();
            if ($('#invoice-dat-expire').val() == "") $('#invoice-dat-expire').val(dateVal);
        }
        var vatLevels = <?= json_encode($vatLevels); ?>;

        $("#invoice-dat-issue").change(applyDates);
        $("#invoice-edit-form").on("submit", {button: $(this)}, InfinityEditorSubmit);

        $("#invoice-items-table").InvoiceItemEditor({vats: vatLevels, itemsAutocompleteUrl: itemsAutocompleteUrl});
    });


</script>
