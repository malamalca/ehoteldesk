<?php

use App\Lib\ETurizem;
use Cake\I18n\Time;
use Cake\Routing\Router;

$step = $this->getRequest()->getQuery('step');

switch ($step) {
    case 'confirm':
        $on = $this->getRequest()->getQuery('on');
        $reportForm = [
            'title_for_layout' => __('EXPORT: Upload eTurizem XML'),
            'form' => [
                'defaultHelper' => $this->Form,
                'pre' => '<div class="form">' . PHP_EOL,
                'post' => '</div>',
                'lines' => [
                    'form_start' => [
                        'method' => 'create',
                        'parameters' => [null, ['type' => 'POST', 'url' => ['?' => ['step' => 'send', 'on' => $this->getRequest()->getQuery('on')]]]]
                    ],
                    'pkpassword' => [
                        'method' => 'control',
                        'parameters' => ['pkpassword', [
                            'type' => 'password',
                            'label' => __('Private key password') . ':',
                        ]]
                    ],
                    'submit' => [
                        'method' => 'submit',
                        'parameters' => [
                            'label' => __('Sign and Send')
                        ]
                    ],
                    'form_end' => [
                        'method' => 'end',
                        'parameters' => []
                    ],

                    'iframe' => sprintf('<iframe id="eturizem-preview" src="%s"/>', Router::url(['?' => ['step' => 'confirm', 'on' => $on], '_ext' => 'xml'], true)),
                ]
            ]
        ];

        echo $this->Lil->form($reportForm, 'Eturizem.sendGuestBook.confirm');
        break;
    case 'error':
    case 'success':
        $errorPanels = [
            'title_for_layout' => $step == 'error' ? __('EXPORT: eTurizem Errors') : __('EXPORT: eTurizem Success'),
            'pre' => '<div class="form">' . PHP_EOL,
            'post' => '</div>',
            'panels' => [
                'main' => ['lines' => [
                    'status' => [
                        'label' => __('Status') . ':',
                        'html' => $log->status
                    ],
                    'message' => [
                        'label' => __('Message') . ':',
                        'html' => ETurizem::getStatusDescription($log->status)
                    ],
                    'details' => sprintf('<a id="eturizem-error-show-details" href="#">%s</a>', __('Show/Hide Details'))
                ]],
                'details' => [
                    'params' => ['id' => 'eturizem-error-details'],
                    'lines' => [
                        'message' => [
                            'label' => __('Message') . ':',
                            'html' => nl2br($log->message)
                        ],
                        'xml' => [
                            'label' => __('XML') . ':',
                            'html' => nl2br(htmlspecialchars($log->xml))
                        ],
                    ]
                ]
            ]
        ];

        $this->Lil->jsReady('$("#eturizem-error-show-details").click(function() {$("#eturizem-error-details").toggle(); });');
        echo $this->Lil->panels($errorPanels, 'Eturizem.sendGuestBook.end');
        break;
    default:
        $reportForm = [
            'title_for_layout' => __('EXPORT: Registrations to eTurizem format'),
            'form' => [
                'defaultHelper' => $this->Form,
                'pre' => '<div class="form">' . PHP_EOL,
                'post' => '</div>',
                'lines' => [
                    'form_start' => [
                        'method' => 'create',
                        'parameters' => [null, ['type' => 'GET']]
                    ],
                    'step' => [
                        'method' => 'control',
                        'parameters' => ['step', [
                            'type' => 'hidden',
                            'value' => 'confirm',
                        ]]
                    ],
                    'fs_dates_start' => '<fieldset>',
                    'lg_dates' => sprintf('<legend>%s</legend>', __('Date')),

                    'date' => [
                        'method' => 'control',
                        'parameters' => ['on', [
                            'type' => 'lil-date',
                            'label' => false,
                            'default' => $this->getRequest()->getQuery('on', new Time())
                        ]]
                    ],
                    'fs_dates_end' => '</fieldset>',

                    'submit' => [
                        'method' => 'submit',
                        'parameters' => [
                            'label' => __('Send')
                        ]
                    ],
                    'form_end' => [
                        'method' => 'end',
                        'parameters' => []
                    ],
                ]
            ]
        ];
        echo $this->Lil->form($reportForm, 'Eturizem.sendGuestBook.form');
}
