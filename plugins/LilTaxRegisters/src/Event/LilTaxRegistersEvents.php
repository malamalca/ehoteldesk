<?php
namespace LilTaxRegisters\Event;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;
use LilTaxRegisters\Lib\TaxRegistersXml;
use Endroid\QrCode;

class LilTaxRegistersEvents implements EventListenerInterface
{

    /**
     * Returns array of implemented events.
     *
     * @return array
     */
    public function implementedEvents(): array
    {
        return [
            'Lil.Form.Lil.Users.properties' => 'addTaxFields',
            'Lil.Form.Lil.Users.edit' => 'addTaxFields',
            'Lil.Form.LilInvoices.InvoicesCounters.edit' => 'addPremiseToInvoiceCounter',
            'Lil.Panels.LilInvoices.Invoices.view' => 'showTaxRegistration',
            'Lil.Sidebar.beforeRender' => 'modifySidebar',

            'Model.beforeMarshal' => 'updateUser',
            'Model.afterSave' => 'confirmInvoice',
            'Controller.beforeRender' => 'checkPKPassword',

            'LilInvoices.Invoices.Export.Html' => 'showTaxBlock',
            'LilInvoices.InvoicesCounters.generateNo' => 'addPremiseToInvoiceNo'
        ];
    }

    /**
     * Add tax registers
     *
     * @param object $event Event object.
     * @param object $data Data.
     * @return object
     */
    public function modifySidebar($event, $sidebar)
    {
        $view = $event->getSubject();

        if (!empty($sidebar['documents']['items'])) {
            $sidebar['documents']['active'] = $sidebar['documents']['active'] ||
                ($view->getRequest()->getParam('controller') == 'BusinessPremises');

            $sidebar['documents']['items']['lookups']['active'] =
                $sidebar['documents']['items']['lookups']['active'] ||
                ($view->getRequest()->getParam('controller') == 'BusinessPremises');

            $sidebar['documents']['items']['lookups']['submenu']['tax_registers'] = [
                'visible' => true,
                'title' => __d('lil_tax_registers', 'Business Premises'),
                'url' => [
                    'plugin' => 'LilTaxRegisters',
                    'controller' => 'BusinessPremises',
                    'action' => 'index'
                ],
                'active' => $view->getRequest()->getParam('controller') == 'BusinessPremises',
            ];
        }

        return $sidebar;
    }

    /**
     * Add tax block to invoices PDF!!!!!
     *
     * @param object $event Event object.
     * @param string $html Invoice html.
     * @return object
     */
    public function showTaxBlock($event, $html)
    {
        $invoice = $event->getSubject();

        $InvoicesTaxconfirmations = TableRegistry::get('LilTaxRegisters.InvoicesTaxconfirmations');
        $invoicesTaxconfirmation = $InvoicesTaxconfirmations->find()
            ->select()
            ->where(['invoice_id' => $invoice->id])
            ->first();

        if ($invoicesTaxconfirmation) {
            if (empty($invoicesTaxconfirmation->eor)) {
            } else {
                return;

                $tmpImage = sys_get_temp_dir() . '/' . uniqid('qr') . '.png';
                QRcode::png($invoicesTaxconfirmation->qr, $tmpImage, 'M', 2, 2);
                if (file_exists($tmpImage)) {
                    $inlineQrImage = base64_encode(file_get_contents($tmpImage));
                    unlink($tmpImage);
                }

                $taxFrame = '<div>';
                $taxFrame .= sprintf('<table style="width: 75%%" padding="0" spacing="0">');
                $taxFrame .= sprintf('<tr><td rowspan="2" width="20%%"><img src="data:image/png;base64,%s" /></td>', $inlineQrImage);
                $taxFrame .= sprintf(
                    '<td width="10%%" valign="bottom">%1$s:<br>%2$s</td>',
                    __d('lil_tax_registers', 'ZOI'),
                    __d('lil_tax_registers', 'EOR')
                );
                $taxFrame .= sprintf('<td width="70%%">%1$s<br>%2$s</td></tr>', $invoicesTaxconfirmation->zoi, $invoicesTaxconfirmation->eor);
                $taxFrame .= '</table></div>';

                $index = strpos($html, '</body>');
                if ($index !== false) {
                    $html = substr_replace($html, $taxFrame . '</body>', $index, strlen('</body>'));
                }
            }
        }

        return $html;
    }

    /**
     * Check and prompt for private key password before entering a new invoice
     *
     * @param object $event Event object.
     * @return object
     */
    public function checkPKPassword($event)
    {
        $controller = $event->getSubject();
        if (($controller->getName() == 'Invoices') && ($controller->getRequest()->getParam('action') == 'edit')) {
            if ($counterId = $controller->getRequest()->getQuery('filter.counter')) {
                $CountersTable = TableRegistry::get('LilInvoices.InvoicesCounters');
                if ($CountersTable->exists(['id' => $counterId, 'tax_confirmation' => true])) {
                    $session = $controller->getRequest()->session();
                    $p12 = $session->read('LilTaxRegisters.P12');

                    if (!$p12 && !$controller->Auth->user('cert_p12')) {
                        $controller->Flash->error(__d('lil_tax_registers', 'Private key not found. Please upload a p12 certificate and relog.'));
                        $controller->redirect(['plugin' => 'Lil', 'controller' => 'Users', 'action' => 'properties']);

                        return false;
                    }

                    if (!$session->read('LilTaxRegisters.PKPassword')) {
                        $controller->redirect(['plugin' => 'LilTaxRegisters', 'controller' => 'TaxRegisters', 'action' => 'password']);

                        return true;
                    }
                }
            }
        }
    }

    /**
     * Add P12 cert upload to user properties form.
     *
     * @param object $event Event object.
     * @param object $data Form data object.
     * @return object
     */
    public function addTaxFields($event, $data)
    {
        $view = $event->getSubject();
        $currentUser = $view->getCurrentUser();

        $userTaxno = [
            'tax_no' => [
                'method' => 'control',
                'parameters' => [
                    'field' => 'tax_no',
                    'options' => [
                        'type' => 'text',
                        'label' => __d('lil_tax_registers', 'Tax no.') . ':',
                    ]
                ]
            ]
        ];
        $view->Lil->insertIntoArray($data->form['lines'], $userTaxno, ['before' => 'fs_basics_end']);

        // set multipart form data as form type
        $data->form['lines']['form_start']['parameters'][] = ['type' => 'file'];

        // add upload cert field
        $entity = $data->form['lines']['form_start']['parameters'][0];
        $userCert = [
            'fs_tr_start' => '<fieldset>',
            'lg_tr' => sprintf('<legend>%s</legend>', __d('lil_tax_registers', 'Tax Registers')),
            'cert_p12' => [
                'method' => 'control',
                'parameters' => [
                    'field' => 'cert_p12',
                    'options' => [
                        'type' => 'file',
                        'label' => __d('lil_tax_registers', 'P12 Cert Store') . ':',
                    ]
                ]
            ],
            'cert_p12_hint' => empty($entity->cert_p12) ? null : sprintf('<div class="hint">%s</div>', __d('lil_tax_registers', 'WARNING! Certificate is already stored for this user. Uploading new file will overwrite existing certificate.')),
            'cert_password' => [
                'method' => 'control',
                'parameters' => [
                    'field' => 'cert_password',
                    'options' => [
                        'type' => 'password',
                        'label' => __d('lil_tax_registers', 'Private Key Password') . ':',
                    ]
                ]
            ],
            'fs_tr_end' => '</fieldset>',
        ];
        $view->Lil->insertIntoArray($data->form['lines'], $userCert, ['after' => 'fs_basics_end']);

        return $data;
    }

    /**
     * Returns array of implemented events.
     *
     * @param object $event Event object.
     * @param object $data Form data object.
     * @return object
     */
    public function addPremiseToInvoiceCounter($event, $data)
    {
        $view = $event->getSubject();
        $currentUser = $view->getCurrentUser();

        $BusinessPremisesTable = TableRegistry::get('LilTaxRegisters.BusinessPremises');
        $businessPremises = $BusinessPremisesTable->findForOwner('list', $currentUser->company_id);

        $trFields = [
            'fs_tr_start' => '<fieldset>',
            'lg_tr' => sprintf('<legend>%s</legend>', __d('lil_tax_registers', 'Tax Registers')),
            'tax_confirmation' => [
                'method' => 'control',
                'parameters' => [
                    'field' => 'tax_confirmation',
                    'options' => [
                        'type' => 'checkbox',
                        'label' => __d('lil_tax_registers', 'Confirm Documents'),
                    ]
                ]
            ],
            'business_premise' => [
                'method' => 'control',
                'parameters' => [
                    'field' => 'business_premise_id',
                    'options' => [
                        'type' => 'select',
                        'label' => __d('lil_tax_registers', 'Business Premise') . ':',
                        'options' => $businessPremises
                    ]
                ]
            ],
            'device_no' => [
                'method' => 'control',
                'parameters' => [
                    'field' => 'device_no',
                    'options' => [
                        'type' => 'text',
                        'label' => __d('lil_tax_registers', 'Device No') . ':',
                    ]
                ]
            ],
            'fs_tr_end' => '</fieldset>',
        ];

        $view->Lil->insertIntoArray($data->form['lines'], $trFields, ['after' => 'fs_layout_end']);

        return $data;
    }

    /**
     * Show fields from tax registration.
     *
     * @param object $event Event object.
     * @param object $data Form data object.
     * @return object
     */
    public function showTaxRegistration($event, $data)
    {
        $view = $event->getSubject();
        if ($data->entity->invoices_counter->tax_confirmation) {
            $data->panels['taxrH.title'] = sprintf('<h2>%s</h2>', __d('lil_tax_registers', 'Tax Registrations Data'));

            $InvoicesTaxconfirmations = TableRegistry::get('LilTaxRegisters.InvoicesTaxconfirmations');
            $invoicesTaxconfirmation = $InvoicesTaxconfirmations->find()
                ->select()
                ->where(['invoice_id' => $data->entity->id])
                ->first();
            if ($invoicesTaxconfirmation) {
                if (empty($invoicesTaxconfirmation->eor)) {
                    $data->panels['taxrH.panel'] = [
                        'lines' => [
                            'message' => __('Confirmation failed.'),
                            'link' => $view->Html->link(__('Retry Tax Confirmation'), ['plugin' => 'LilTaxRegisters', 'controller' => 'TaxRegisters', 'action' => 'retry', $data->entity->id])
                        ]
                    ];
                } else {
                    $tmpImage = sys_get_temp_dir() . '/' . uniqid('qr') . '.png';
                    QRcode::png($invoicesTaxconfirmation->qr, $tmpImage, 'M', 4, 2);
                    if (file_exists($tmpImage)) {
                        $inlineQrImage = base64_encode(file_get_contents($tmpImage));
                        unlink($tmpImage);
                    }
                    $data->panels['taxrH.panel'] = [
                        'lines' => [
                            ['label' => __d('lil_tax_registers', 'ZOI') . ':', 'html' => $invoicesTaxconfirmation->zoi],
                            ['label' => __d('lil_tax_registers', 'EOR') . ':', 'html' => $invoicesTaxconfirmation->eor],
                            ['label' => __d('lil_tax_registers', 'QR') . ':', 'html' => sprintf('<img src="data:image/png;base64,%s" />', $inlineQrImage)],

                            'request' => !Configure::read('debug') ? null :
                                //h($invoicesTaxconfirmation->last_request)
                                print_r(TaxRegistersXml::invoice($data->entity, $invoicesTaxconfirmation), true)
                        ]
                    ];
                }
            } else {
                $data->panels['taxrH.panel'] = [
                    'lines' => [
                        'message' => __('Confirmation failed.'),
                        'link' => $view->Html->link(__('Retry Tax Confirmation'), ['plugin' => 'LilTaxRegisters', 'controller' => 'TaxRegisters', 'action' => 'retry', $data->entity->id])
                    ]
                ];
            }
        }

        return $data;
    }

    /**
     * Returns array of implemented events.
     *
     * @param object $event Event object.
     * @param object $entity Entity object.
     * @param object $options Save options
     * @return object
     */
    public function confirmInvoice($event, $entity, $options)
    {
        if ($entity->isNew() && is_a($entity, 'LilInvoices\Model\Entity\Invoice')) {
            if (!isset($options['authCompany'])) {
                return false;
            }

            $InvoicesCounters = TableRegistry::get('LilInvoices.InvoicesCounters');
            $InvoicesTaxconfirmations = TableRegistry::get('LilTaxRegisters.InvoicesTaxconfirmations');
            $counter = $InvoicesCounters->get($entity->counter_id);
            if ($counter->tax_confirmation) {
                $session = new Session();
                $p12 = $session->read('LilTaxRegisters.P12');
                $p12Password = $session->read('LilTaxRegisters.PKPassword');
                $InvoicesTaxconfirmations->signAndSend($entity->id, $options['authCompany'], $p12, $p12Password);
            }
        }
    }

    /**
     * Upload user cert to field.
     *
     * @param object $event Event object.
     * @param object $data Data array.
     * @param object $options Save options
     * @return void
     */
    public function updateUser($event, $data, $options)
    {
        $model = $event->getSubject();
        if (is_a($model, 'Lil\Model\Table\UsersTable')) {
            if (isset($data['cert_p12'])) {
                if (($data['cert_p12']['error'] == 0) && !empty($data['cert_p12']['tmp_name']) && file_exists($data['cert_p12']['tmp_name'])) {
                    $session = new Session();

                    $session->write('LilTaxRegisters.P12', file_get_contents($data['cert_p12']['tmp_name']));
                    $session->write('LilTaxRegisters.PKPassword', $data['cert_password']);

                    $data['cert_p12'] = base64_encode(file_get_contents($data['cert_p12']['tmp_name']));
                    $data['cert_p12'] = base64_encode(Security::encrypt($data['cert_p12'], $data['id'], $data['cert_password']));
                } else {
                    unset($data['cert_p12']);
                }
            }
        }
    }

    /**
     * Upload user cert to field.
     *
     * @param object $event Event object.
     * @return object
     */
    public function addPremiseToInvoiceNo($event)
    {
        $data = $event->getData();

        $InvoicesCounters = TableRegistry::get('LilInovices.InvoicesCounters');
        $counter = $InvoicesCounters->get($data['counterId']);

        if ($counter && !empty($counter->business_premise_id)) {
            $BusinessPremises = TableRegistry::get('LilTaxRegisters.BusinessPremises');
            $premise = $BusinessPremises->get($counter->business_premise_id);

            if ($premise) {
                $data['no'] = strtr(
                    $data['no'],
                    [
                        '[[premise]]' => $premise->no,
                        '[[device]]' => $counter->device_no
                    ]
                );
            }
        }

        return $data;
    }
}
