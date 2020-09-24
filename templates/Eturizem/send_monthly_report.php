<?php

use App\Lib\ETurizem;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\Routing\Router;

$step = $this->getRequest()->getQuery('step');

switch ($step) {
    case 'confirm':
        $reportForm = [
            'title_for_layout' => __('EXPORT: eTurizem Monthly Report XML'),
            'form' => [
                'defaultHelper' => $this->Form,
                'pre' => '<div class="form">' . PHP_EOL,
                'post' => '</div>',
                'lines' => [
                    'form_start' => [
                        'method' => 'create',
                        'parameters' => [null, ['type' => 'POST', 'url' =>  ['?' => array_merge($this->getRequest()->getQuery(),['step' => 'send'])]]]
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

                    'iframe_title' => '<h3>' . __('Data Preview') . '</h3>',
                    'iframe' => sprintf('<iframe id="eturizem-preview" src="%s" class="container-fluid"/>', Router::url(
                        ['?' => array_merge($this->getRequest()->getQuery(), ['step' => 'confirm']), '_ext' => 'xml']
                        , true)),
                ]
            ]
        ];

        echo $this->Lil->form($reportForm, 'Registrations.exportEgost.confirmation');
        break;
    case 'error':
    case 'success':
        $errorPanels = [
            'title_for_layout' => $step == 'error' ? __('EXPORT: eTurizem Errors') : __('EXPORT: eTurizem Success'),
            'pre' => '<div class="form">' . PHP_EOL,
            'post' => '</div>',

            'panels' => [
                'main' => ['lines' => [
                    'title' => '<h3>' . __('Data Results') . '</h3>',
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

        $this->Lil->jsReady('$("#eturizem-error-details").hide();');
        $this->Lil->jsReady('$("#eturizem-error-show-details").click(function() {$("#eturizem-error-details").toggle(); });');
        echo $this->Lil->panels($errorPanels, 'Registrations.exportEgost.error');
        break;
    default:
        $reportForm = [
            'title_for_layout' => __('EXPORT: eTurizem Monthly Report'),
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

                    'counter' => [
                        'method' => 'control',
                        'parameters' => ['counter', [
                            'type' => 'select',
                            'label' => [
                                'text' => __('Counter') . ':',
                                'class' => 'active',
                            ],
                            'options' => $counters,
                            'class' => 'browser-default',
                        ]]
                    ],
                    'month' => [
                        'method' => 'control',
                        'parameters' => ['month', [
                            'type' => 'month',
                            'label' => [
                                'text' => __('Month') . ':',
                                'class' => 'active',
                            ],
                            'default' => Time::now()->format('M'),
                            'empty' => false
                        ]]
                    ],

                    'status' => [
                        'method' => 'control',
                        'parameters' => ['status', [
                            'type' => 'select',
                            'label' => [
                                'text' => __('Status') . ':',
                                'class' => 'active',
                            ],
                            'options' => Configure::read('monthlyStatuses'),
                            'class' => 'browser-default',
                        ]]
                    ],

                    'additional' => [
                        'method' => 'control',
                        'parameters' => ['additional', [
                            'type' => 'text',
                            'label' => __('Additional Beds') . ':',
                            'default' => 0
                        ]]
                    ],

                    'units' => [
                        'method' => 'control',
                        'parameters' => ['units', [
                            'type' => 'text',
                            'label' => __('Units Sold') . ':',
                        ]]
                    ],

                    'workdays' => [
                        'method' => 'control',
                        'parameters' => ['workdays', [
                            'type' => 'text',
                            'label' => __('Workday Count') . ':',
                        ]]
                    ],

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
        echo $this->Lil->form($reportForm, 'Eturizem.sendMonthlyReport.form');
}
