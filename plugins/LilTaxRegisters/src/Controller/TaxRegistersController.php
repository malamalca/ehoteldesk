<?php
namespace LilTaxRegisters\Controller;

use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use LilTaxRegisters\Controller\AppController;
use LilTaxRegisters\Form\PKPasswordForm;

/**
 * TaxRegisters Controller
 *
 * @property \LilTaxRegisters\Model\Table\TaxRegistersTable $TaxRegisters
 */
class TaxRegistersController extends AppController
{
    /**
     * isAuthorized method.
     *
     * @param array $user User
     * @return bool
     */
    /*public function isAuthorized($user)
    {
        switch ($this->request->getParam('action')) {
            case 'password':
            case 'retry':
                return true;

            default:
                return false;
        }
    }*/

    /**
     * Read users password on first invoice edit attempt.
     *
     * @return \Cake\Network\Response|null
     */
    public function password()
    {
        $this->Authorization->skipAuthorization();

        $PKPassword = new PKPasswordForm($this->getRequest(), $this->getCurrentUser());

        if ($this->request->isPost()) {
            if ($PKPassword->execute($this->getRequest()->getData())) {
                return $this->redirect($this->getRequest()->getData('referer', ['action' => 'index']));
            } else {
                $this->Flash->error(__d('lil_tax_registers', 'Wrong Certificate Password'));
            }
        }

        $this->set(compact('PKPassword'));
    }

    /**
     * Retry tax confirmation
     *
     * @return \Cake\Network\Response|void
     */
    public function retry($invoiceId)
    {
        $this->Authorization->skipAuthorization();

        $InvoicesTaxconfirmations = TableRegistry::get('LilTaxRegisters.InvoicesTaxconfirmations');

        $p12Password = $this->getRequest()->getSession()->read('LilTaxRegisters.PKPassword');
        if (!$p12Password) {
            $this->redirect(['plugin' => 'LilTaxRegisters', 'controller' => 'TaxRegisters', 'action' => 'password']);

            return;
        }

        $p12 = $this->getRequest()->getSession()->read('LilTaxRegisters.P12');
        if (empty($p12)) {
            $this->Flash->error(__d('lil_tax_registers', 'Private key not found. Please upload a p12 certificate and relog.'));
            $this->redirect(['controller' => 'Users', 'action' => 'properties']);
        }

        $CompaniesTable = TableRegistry::get('Companies');
        $company = $CompaniesTable->get($this->getCurrentUser()->get('company_id'));

        $result = $InvoicesTaxconfirmations->signAndSend($invoiceId, $company, $p12, $p12Password);
        if ($result->error_code > 0) {
            $this->Flash->success(__d('lil_tax_registers', 'The invoice confirmation successful.'));
        } else {
            $this->Flash->error(__d('lil_tax_registers', 'The invoice confirmation failed.'));
        }
        $this->redirect(['plugin' => 'LilInvoices', 'controller' => 'Invoices', 'action' => 'view', $invoiceId]);

        $this->set(compact('result'));
    }
}
