<?php
namespace App\Lib;

use Cake\ORM\TableRegistry;

class AppSidebar
{

    /**
     * setAdminSidebar method
     *
     * Add admin sidebar elements.
     *
     * @param mixed $event Event object.
     * @access public
     * @return array
     */
    public static function setAdminSidebar($event, $sidebar)
    {
        $view = $event->getSubject();

        $controller = $view->getRequest()->getParam('controller');
        $action = $view->getRequest()->getParam('action');
        $plugin = $view->getRequest()->getParam('plugin');

        $currentUser = $view->getCurrentUser();

        $sidebar['crm']['visible'] = false;
        $sidebar['crm']['active'] = false;
        $sidebar['documents']['visible'] = false;
        $sidebar['documents']['active'] = false;

        $sidebar['welcome']['active'] = !empty($currentUser) && (empty($plugin) || in_array($plugin, ['LilCrm', 'LilInvoices', 'LilTaxRegisters']));
        $sidebar['welcome']['items'] = [
            'reservations' => [
                'visible' => $currentUser,
                'title' => __('Reservations'),
                'url' => ['plugin' => false, 'controller' => 'Reservations', 'action' => 'index'],
                'expandable' => false,
                'params' => [],
                'active' => self::testCA($controller, $action, 'Reservations', ['index', 'edit', 'view', 'createInvoice', 'previewInvoice']),
                'expand' => false,
                'submenu' => []
            ],
            'registrations' => [
                'visible' => $currentUser,
                'title' => __('Registrations'),
                'url' => ['plugin' => false, 'controller' => 'Registrations', 'action' => 'index'],
                'expandable' => false,
                'params' => [],
                'active' => self::testCA($controller, $action, 'Registrations', ['index', 'edit', 'view']),
                'expand' => false,
                'submenu' => []
            ],
            'contacts' => [
                'visible' => $currentUser,
                'title' => __('Contacts'),
                'url' => ['plugin' => 'LilCrm', 'controller' => 'Contacts', 'action' => 'index'],
                'expandable' => false,
                'params' => [],
                'active' => self::testCA($controller, $action, 'Contacts', ['index', 'edit', 'view']),
                'expand' => false,
                'submenu' => []
            ],
            'invoices' => [
                'visible' => $currentUser,
                'title' => __('Invoices'),
                'url' => null,
                'expandable' => true,
                'params' => [],
                'active' => self::testCA($controller, $action, 'Invoices', ['*']),
                'active' => ($plugin == 'LilInvoices' && (
                    self::testCA($controller, $action, 'Invoices', ['index', 'edit', 'view'])
                )),
                'submenu' => []
            ],
            'companies' => [
                'visible' => $currentUser && $currentUser->hasRole('root'),
                'title' => __('Companies'),
                'url' => ['plugin' => false, 'controller' => 'Companies', 'action' => 'index'],
                'expandable' => false,
                'params' => [],
                'active' => self::testCA($controller, $action, 'Companies', ['index', 'edit', 'view']),
                'expand' => false,
                'submenu' => []
            ],
            'reports' => [
                'visible' => $currentUser,
                'title' => __('Reports'),
                'url' => null,
                'expandable' => true,
                'params' => [],
                'active' => false,
                'active' => self::testCA($controller, $action, 'Reservations', ['reportAnalytics']) ||
                    self::testCA($controller, $action, 'Registrations', ['reportAnalytics', 'reportServices', 'reportGuests']) ||
                    self::testCA($controller, $action, 'Rooms', ['reportVacancies', 'reportBooked']),
                'submenu' => [
                    'reservations_analytics' => [
                        'visible' => true,
                        'title' => __('Reservations'),
                        'url' => [
                            'plugin' => false,
                            'controller' => 'Reservations',
                            'action' => 'reportAnalytics'
                        ],
                        'active' => self::testCA($controller, $action, 'Reservations', ['reportAnalytics']),
                    ],
                    'registrations_analytics' => [
                        'visible' => true,
                        'title' => __('Registrations'),
                        'url' => [
                            'plugin' => false,
                            'controller' => 'Registrations',
                            'action' => 'report_analytics'
                        ],
                        'active' => self::testCA($controller, $action, 'Registrations', ['reportAnalytics']),
                    ],
                    'registrations_services' => [
                        'visible' => true,
                        'title' => __('Services'),
                        'url' => [
                            'plugin' => false,
                            'controller' => 'Registrations',
                            'action' => 'report_services'
                        ],
                        'active' => self::testCA($controller, $action, 'Registrations', ['reportServices']),
                    ],
                    'registrations_guests' => [
                        'visible' => true,
                        'title' => __('Guests'),
                        'url' => [
                            'plugin' => false,
                            'controller' => 'Registrations',
                            'action' => 'report_guests'
                        ],
                        'active' => self::testCA($controller, $action, 'Registrations', ['reportGuests']),
                    ],
                    'vacancies' => [
                        'visible' => true,
                        'title' => __('Vacancies'),
                        'url' => [
                            'plugin' => false,
                            'controller' => 'Rooms',
                            'action' => 'report_vacancies'
                        ],
                        'active' => self::testCA($controller, $action, 'Rooms', ['reportVacancies']),
                    ],
                    'booked_rooms' => [
                        'visible' => true,
                        'title' => __('Booked Rooms'),
                        'url' => [
                            'plugin' => false,
                            'controller' => 'Rooms',
                            'action' => 'report_booked'
                        ],
                        'active' => self::testCA($controller, $action, 'Rooms', ['reportBooked']),
                    ],
                ]
            ],
            'eturizem' => [
                'visible' => $currentUser && $currentUser->hasRole('admin'),
                'title' => __('eTurizem'),
                'url' => null,
                'expandable' => false,
                'params' => [],
                'active' => self::testCA($controller, '*', 'Eturizem'),
                'submenu' => [
                    'send_guest_book' => [
                        'visible' => true,
                        'title' => __('Send Guest Book'),
                        'url' => [
                            'plugin' => false,
                            'controller' => 'Eturizem',
                            'action' => 'sendGuestBook'
                        ],
                        'active' => self::testCA($controller, $action, 'Eturizem', ['sendGuestBook']),
                    ],
                    'send_monthly_report' => [
                        'visible' => true,
                        'title' => __('Send Monthly Report'),
                        'url' => [
                            'plugin' => false,
                            'controller' => 'Eturizem',
                            'action' => 'sendMonthlyReport'
                        ],
                        'active' => self::testCA($controller, $action, 'Eturizem', ['sendMonthlyReport']),
                    ],
                ]
            ],
            'lookups' => [
                'visible' => $currentUser && $currentUser->hasRole('admin'),
                'title' => __('Lookups'),
                'expandable' => true,
                'params' => [],
                'active' => self::testCA($controller, $action, 'Counters', ['index', 'edit']) ||
                    self::testCA($controller, $action, 'Rooms', ['index', 'edit']) ||
                    self::testCA($controller, $action, 'RoomTypes', ['index', 'edit']) ||
                    self::testCA($controller, $action, 'ServiceTypes', ['index', 'edit']) ||
                    ($plugin == 'LilInvoices' && (
                        self::testCA($controller, $action, 'InvoicesCounters', ['index', 'edit']) ||
                        self::testCA($controller, $action, 'Items', ['index', 'edit']) ||
                        self::testCA($controller, $action, 'Vats', ['index', 'edit']) ||
                        self::testCA($controller, $action, 'InvoicesTemplates', ['index', 'edit'])
                    )) ||
                    ($plugin == 'LilTaxRegisters' && (
                        self::testCA($controller, $action, 'BusinessPremises', ['index', 'edit', 'view'])
                    )),
                'submenu' => [
                    'counters' => [
                        'visible' => true,
                        'title' => __('Counters'),
                        'url' => [
                            'plugin' => false,
                            'controller' => 'Counters',
                            'action' => 'index'
                        ],
                        'active' => self::testCA($controller, $action, 'Counters', ['index', 'edit']),
                    ],
                    'rooms' => [
                        'visible' => true,
                        'title' => __('Rooms'),
                        'url' => [
                            'plugin' => false,
                            'controller' => 'Rooms',
                            'action' => 'index'
                        ],
                        'active' => self::testCA($controller, $action, 'Rooms', ['index', 'edit']),
                    ],
                    'room_types' => [
                        'visible' => true,
                        'title' => __('Room Types'),
                        'url' => [
                            'plugin' => false,
                            'controller' => 'RoomTypes',
                            'action' => 'index'
                        ],
                        'active' => self::testCA($controller, $action, 'RoomTypes', ['index', 'edit']),
                    ],
                    'service_types' => [
                        'visible' => true,
                        'title' => __('Service Types'),
                        'url' => [
                            'plugin' => false,
                            'controller' => 'ServiceTypes',
                            'action' => 'index'
                        ],
                        'active' => self::testCA($controller, $action, 'ServiceTypes', ['index', 'edit']),
                    ],
                    'invoices_counters' => [
                        'visible' => true,
                        'title' => __('Invoices Counters'),
                        'url' => [
                            'plugin' => 'LilInvoices',
                            'controller' => 'InvoicesCounters',
                            'action' => 'index'
                        ],
                        'active' => ($plugin == 'LilInvoices') && self::testCA($controller, $action, 'InvoicesCounters', ['index', 'edit']),
                    ],
                    'invoices_items' => [
                        'visible' => true,
                        'title' => __('Invoices Items'),
                        'url' => [
                            'plugin' => 'LilInvoices',
                            'controller' => 'Items',
                            'action' => 'index'
                        ],
                        'active' => ($plugin == 'LilInvoices') && self::testCA($controller, $action, 'Items', ['index', 'edit']),
                    ],
                    'invoices_vats' => [
                        'visible' => true,
                        'title' => __('Invoices Vats'),
                        'url' => [
                            'plugin' => 'LilInvoices',
                            'controller' => 'Vats',
                            'action' => 'index'
                        ],
                        'active' => ($plugin == 'LilInvoices') && self::testCA($controller, $action, 'Vats', ['index', 'edit']),
                    ],
                    'invoices_templates' => [
                        'visible' => true,
                        'title' => __('Invoices Templates'),
                        'url' => [
                            'plugin' => 'LilInvoices',
                            'controller' => 'InvoicesTemplates',
                            'action' => 'index'
                        ],
                        'active' => ($plugin == 'LilInvoices') && self::testCA($controller, $action, 'InvoicesTemplates', ['index', 'edit']),
                    ],
                    'business_premisses' => [
                        'visible' => true,
                        'title' => __('Business Premises'),
                        'url' => [
                            //'plugin' => 'LilTaxRegisters',
                            'controller' => 'BusinessPremises',
                            'action' => 'index'
                        ],
                        'active' => ($plugin == 'LilTaxRegisters') && self::testCA($controller, $action, 'BusinessPremises', ['index', 'edit', 'view']),
                    ],
                ]
            ]
        ];

        $invoicesCounters = TableRegistry::get('LilInvoices.InvoicesCounters')
            ->find('invoicesList')
            ->andWhere(['owner_id' => empty($currentUser['company_id']) ? '' : $currentUser['company_id']])
            ->andWhere(['active' => true])
            ->andWhere(['kind' => 'issued'])
            ->toArray();

        $isFirst = true;
        foreach ($invoicesCounters as $counter_id => $counter) {
            $sidebar['welcome']['items']['invoices']['submenu'][$counter_id] = [
                'visible' => true,
                'title' => $counter,
                'url' => [
                    'plugin' => 'LilInvoices',
                    'controller' => 'Invoices',
                    'action' => 'index',
                    '?' => ['counter' => $counter_id]
                ],
                'active' => ($plugin == 'LilInvoices' &&
                    (self::testCA($controller, $action, 'Invoices', ['index', 'edit', 'view'])) &&
                    $view->getRequest()->getQuery('filter.counter') == $counter_id
                ),
            ];
        }

        return ['sidebar' => $sidebar];
    }

    /**
     * Check if passed controller/action matches valid
     *
     * @param string $controller Controller name.
     * @param string $action Action name.
     * @param array $validControllers List of valid controllers.
     * @param array $validActions List of valid actions.
     * @return bool
     */
    public static function testCA($controller, $action, $validControllers, $validActions = [])
    {
        return in_array($controller, (array)$validControllers) && (($action === '*') || in_array($action, (array)$validActions));
    }
}
