<?php
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\Utility\Inflector;

$action = [
    'plugin' => 'LilInvoices',
    'controller' => 'Invoices',
    'action' => 'export',
    $invoiceId,
    '_ext' => 'pdf',
    'download' => 0
];

$invoicePreview = [
    'title_for_layout' => __('Reservations'),
    'menu' => [
        'back' => [
            'title' => __('Back to Reservation'),
            'visible' => true,
            'url' => [
                'action' => 'view',
                $reservationId
            ]
        ],
        'email' => [
            'title' => __('Send via Email'),
            'visible' => true,
            'url' => [
                'plugin' => 'LilInvoices',
                'controller' => 'Invoices',
                'action' => 'email',
                'filter' => ['invoices' => [$invoiceId]],
                'subject' => __('Reservation Invoice')
            ],
            'params' => [
                'onclick' => sprintf('popup("%s", $(this).attr("href"), 555); return false;', __('Send via Email'))
            ]
        ],
    ],
    'panels' => [
       sprintf('<div class="embed-responsive  embed-responsive-1by1"><iframe id="invoice-view" src="%s" class="embed-responsive-item"></iframe></div>', Router::url($action))
    ]
];

echo $this->Lil->panels($invoicePreview, 'Reservations.previewInvoice');
