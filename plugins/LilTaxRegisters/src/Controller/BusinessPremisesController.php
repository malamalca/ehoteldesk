<?php
namespace LilTaxRegisters\Controller;

use Cake\Core\Configure;
use Cake\Utility\Xml;
use LilTaxRegisters\Controller\AppController;
use LilTaxRegisters\Lib\TaxRegistersXml;
use Malamalca\FiscalPHP\FiscalSign;
use Malamalca\FiscalPHP\FiscalSoap;

/**
 * BusinessPremise Controller
 *
 * @property \LilTaxRegisters\Model\Table\BusinessPremiseTable $BusinessPremise
 */
class BusinessPremisesController extends AppController
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
            case 'index':
                return true;
            case 'add':
                return $this->userLevel('admin');
            case 'view':
                return !empty($this->request->getParam('pass.0')) &&
                    $this->BusinessPremises->isOwnedBy($this->request->getParam('pass.0'), $this->Auth->user('company_id'));
            case 'edit':
            case 'delete':
            case 'register':
                return !empty($this->request->getParam('pass.0')) && $this->userLevel('admin') &&
                    $this->BusinessPremises->isOwnedBy($this->request->getParam('pass.0'), $this->Auth->user('company_id'));
            default:
                return false;
        }
    }*/

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $this->Authorization->skipAuthorization();

        $businessPremises = $this->BusinessPremises->findForOwner('all', $this->getCurrentUser()->get('company_id'));

        $this->set(compact('businessPremises'));
        $this->set('_serialize', ['businessPremises']);
    }

    /**
     * View method
     *
     * @param string|null $id Business Premise id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $this->Authorization->skipAuthorization();

        $businessPremise = $this->BusinessPremises->get($id, [
            'contain' => []
        ]);

        $this->set('businessPremise', $businessPremise);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->setAction('edit');
    }

    /**
     * Edit method
     *
     * @param string|null $id Business Premise id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $this->Authorization->skipAuthorization();

        if ($id) {
            $businessPremise = $this->BusinessPremises->get($id);
        } else {
            $businessPremise = $this->BusinessPremises->newEmptyEntity();
            $businessPremise->owner_id = $this->getCurrentUser()->get('company_id');
        }
        if ($this->request->is(['patch', 'post', 'put'])) {
            $businessPremise = $this->BusinessPremises->patchEntity($businessPremise, $this->request->getData());
            if ($this->BusinessPremises->save($businessPremise)) {
                $this->Flash->success(__d('lil_tax_registers', 'The business premise has been saved.'));

                return $this->redirect(['action' => 'view', $businessPremise->id]);
            } else {
                $this->Flash->error(__d('lil_tax_registers', 'The business premise could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('businessPremise'));
        $this->set('_serialize', ['businessPremise']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Business Premise id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->Authorization->skipAuthorization();

        $this->request->allowMethod(['post', 'delete', 'get']);
        $businessPremise = $this->BusinessPremises->get($id);
        if ($businessPremise->closed && $this->BusinessPremises->delete($businessPremise)) {
            $this->Flash->success(__d('lil_tax_registers', 'The business premise has been deleted.'));
        } else {
            $this->Flash->error(__d('lil_tax_registers', 'The business premise could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Register Business Premise with DURS
     *
     * @param string|null $id Business Premise id.
     * @return \Cake\Network\Response|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function register($id = null)
    {
        $this->Authorization->skipAuthorization();

        $businessPremise = $this->BusinessPremises->get($id);

        $bpArray = TaxRegistersXml::businessPremise($businessPremise);
        $envelope = TaxRegistersXml::envelope($bpArray);

        $XmlObject = Xml::fromArray($envelope, ['format' => 'tags', 'return' => 'domdocument', 'pretty' => true]);
        $xmlRequest = $XmlObject->saveXML();

        $session = $this->request->getSession();
        $p12 = $session->read('LilTaxRegisters.P12');
        $p12Password = $session->read('LilTaxRegisters.PKPassword');

        if (!$p12 && !$this->getCurrentUser()->get('cert_p12')) {
            $this->Flash->error(__d('lil_tax_registers', 'Private key not found. Please upload a p12 certificate and relog.'));
            $this->redirect(['plugin' => 'Lil', 'controller' => 'Users', 'action' => 'properties']);

            return;
        }

        if (!$p12Password) {
            return $this->redirect(['plugin' => 'LilTaxRegisters', 'controller' => 'TaxRegisters', 'action' => 'password']);
        }

        $s = new FiscalSign();
        $s->setP12($p12);
        $s->setPassword($p12Password);

        if ($signed = $s->sign($xmlRequest, 'fu:BusinessPremiseRequest')) {
            $s = new FiscalSoap();
            $s->setP12($p12);
            $s->setPassword($p12Password);
            $s->setCert(Configure::read('LilTaxRegisters.security.cert'));

            if ($response = $s->sendPremiseRaw($signed)) {
                $businessPremise->last_request = $signed;
                $businessPremise->last_response = $response;
                $this->BusinessPremises->save($businessPremise);

                if ($s->hasError($response) === false) {
                    $businessPremise->active = true;
                    if ($this->BusinessPremises->save($businessPremise)) {
                        $this->Flash->success(__d('lil_tax_registers', 'The business premise has been registered.'));
                    }
                } else {
                    $this->Flash->error(__d('lil_tax_registers', 'ERROR') . ': ' . $s->elementValue($response, 'fu:ErrorMessage'));
                }
            } else {
                $this->Flash->error($s->lastError());
            }
        }

         return $this->redirect(['action' => 'view', $id]);
    }
}
