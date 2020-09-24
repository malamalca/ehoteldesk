<?php
namespace App\Controller;

use App\Lib\ETurizem;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;

/**
 * Eturizem controller
 *
 */
class EturizemController extends AppController
{
    /**
     * isAuthorized method.
     *
     * @param array $user User
     * @return bool
     */
    /*public function isAuthorized($user)
    {
        return $this->getCurrentUser()->get('id');
    }*/

    /**
     * Export and send to eTurizem
     *
     * @return \Cake\Network\Response|null Redirects.
     */
    public function sendGuestBook()
    {
        $this->Authorization->skipAuthorization();

        if (!$this->getCurrentUser()->get('etur_p12')) {
            $this->Flash->error(__('No eTurizem certificate found. Please upload your certificate.'));

            return $this->redirect(['controller' => 'Users', 'action' => 'properties']);
        }


        if ($step = $this->getRequest()->getQuery('step')) {
            $aDate = $this->getRequest()->getQuery('on');
            $filter['owner'] = $this->getCurrentUser()->get('company_id');
            $filter['on'] = new FrozenDate($aDate);
            $filter['eturizem'] = 'notsent';

            $RegistrationsTable = TableRegistry::get('Registrations');

            $q = $this->Authorization->applyScope($RegistrationsTable->find(), 'index');
            $registrations = $RegistrationsTable->filter('all', $q, $filter);

            if ($registrations->isEmpty()) {
                $this->Flash->error(__('There are no registrations starting with specified date.'));

                return $this->redirect(['action' => 'sendGuestBook', 'on' => $aDate]);
            }
            $this->set(compact('registrations'));

            switch ($step) {
                case 'send':
                    $pkPass = $this->getRequest()->getData('pkpassword');
                    // build xml
                    $builder = clone $this->viewBuilder();
                    $builder->setClassName('Xml');
                    $builder->setTemplate('\Eturizem\xml\send_guest_book');
                    $view = $builder->build(['addXSL' => false, 'registrations' => $registrations]);

                    $data = $view->render();

                    $ETurizem = new ETurizem();

                    // validate
                    if (!$ETurizem->validateGuestBookSchema($data)) {
                        $log = $ETurizem->log($this->getCurrentUser()->get('company_id'), ETurizem::ERROR_GB_SCHEMA, $data, $ETurizem->getLastErrorMessage());
                        $this->Flash->error(__('There are errors in XML. Please check your data'));

                        return $this->redirect(['action' => 'sendGuestBook', 'on' => $aDate, 'step' => 'error', 'log' => $log->id]);
                    }

                    $result = false;
                    $errorMessage = 'ok';
                    try {
                        $result = $ETurizem->send($data, $this->getCurrentUser()->get('etur_username'), $this->getCurrentUser()->get('etur_password'), base64_decode($this->getCurrentUser()->get('etur_p12')), $pkPass);
                    } catch (Exception $e) {
                        $errorMessage = $e->getMessage();
                    }

                    if ($result) {
                        $log = $ETurizem->log($this->getCurrentUser()->get('company_id'), ETurizem::SUCCESS_GB, $data, $result['raw']);
                        $RegistrationsTable->markEturizemSent($registrations, $result);
                        $this->Flash->success(__('ETurizem data has been successfully sent.'));

                        return $this->redirect(['action' => 'sendGuestBook', 'on' => $aDate, 'step' => 'success', 'log' => $log->id]);
                    } else {
                        $log = $ETurizem->log($this->getCurrentUser()->get('company_id'), ETurizem::ERROR_GB_SOAP, $data, $ETurizem->getLastErrorMessage());
                        $this->Flash->error(__('An error occured while sending your data.'));

                        return $this->redirect(['action' => 'sendGuestBook', 'on' => $aDate, 'step' => 'error', 'log' => $log->id]);
                    }

                    break;
                case 'error':
                case 'success':
                    $EturizemLogsTable = TableRegistry::get('EturizemLogs');
                    $log = $EturizemLogsTable->get($this->getRequest()->getQuery('log'));
                    $this->set(compact('log'));
                    break;
                default:
                    // confirmation form or raw xml
                    // show input private key password and preview form
                    $this->set('addXSL', true);
                    $this->set('addXmlHeader', true);
            }
        }
    }

    /**
     * Export and send to monthly report eTurizem
     *
     * @return \Cake\Network\Response|null Redirects.
     */
    public function sendMonthlyReport()
    {
        $this->Authorization->skipAuthorization();

        if (!$this->getCurrentUser()->get('etur_p12')) {
            $this->Flash->error(__('No eTurizem certificate found. Please upload your certificate.'));

            return $this->redirect(['controller' => 'Users', 'action' => 'properties']);
        }

        $CountersTable = TableRegistry::get('Counters');
        $counter = null;

        if ($step = $this->getRequest()->getQuery('step')) {

            if (!$counterId = $this->getRequest()->getQuery('counter')) {
                throw new NotFoundException(__('Counter not found'));
            }
            $counter = $CountersTable->get($counterId);
            $this->set(compact('counter'));

            switch ($step) {
                case 'send':
                    $pkPass = $this->getRequest()->getData('pkpassword');

                    // build xml
                    $builder = clone $this->viewBuilder();
                    $builder->setClassName('Xml');
                    $builder->setTemplate('\Eturizem\xml\send_monthly_report');
                    $view = $builder->build(['addXSL' => false, 'counter' => $counter]);
                    $data = $view->render();

                    $ETurizem = new ETurizem();

                    // validate
                    if (!$ETurizem->validateMonthlyReportSchema($data)) {
                        $log = $ETurizem->log($this->getCurrentUser()->get('company_id'), ETurizem::ERROR_GB_SCHEMA, $data, $ETurizem->getLastErrorMessage());
                        $this->Flash->error(__('There are errors in XML. Please check your data'));

                        return $this->redirect(array_merge($this->getRequest()->getQuery(), ['action' => 'sendMonthlyReport', 'step' => 'error', 'log' => $log->id]));
                    }

                    $result = false;
                    $errorMessage = 'ok';
                    try {
                        $result = $ETurizem->send($data, $this->getCurrentUser()->get('etur_username'), $this->getCurrentUser()->get('etur_password'), base64_decode($this->getCurrentUser()->get('etur_p12')), $pkPass);
                    } catch (Exception $e) {
                        $errorMessage = $e->getMessage();
                    }

                    if ($result) {
                        $log = $ETurizem->log($this->getCurrentUser()->get('company_id'), ETurizem::SUCCESS_GB, (string)$data, serialize($result));
                        $this->Flash->success(__('ETurizem data has been successfully sent.'));

                        return $this->redirect(array_merge($this->getRequest()->getQuery(), ['action' => 'sendMonthlyReport', 'step' => 'success', 'log' => $log->id]));
                    } else {
                        $log = $ETurizem->log($this->getCurrentUser()->get('company_id'), ETurizem::ERROR_GB_SOAP, $data, $ETurizem->getLastErrorMessage());
                        $this->Flash->error(__('An error occured while sending your data.'));

                        return $this->redirect(array_merge($this->getRequest()->getQuery(), ['action' => 'sendMonthlyReport', 'step' => 'error', 'log' => $log->id]));
                    }

                    break;
                case 'error':
                case 'success':
                    $EturizemLogsTable = TableRegistry::get('EturizemLogs');
                    $log = $EturizemLogsTable->get($this->getRequest()->getQuery('log'));
                    $this->set(compact('log'));
                    break;
                default:
                    $this->set('addXSL', true);
                    $this->set('addXmlHeader', true);
            }
        } else {
            $counters = $CountersTable->findForOwner('list', 'V', $this->getCurrentUser()->get('company_id'))->combine('id', 'title');
            $this->set(compact('counters', 'counter'));
        }
    }
}
