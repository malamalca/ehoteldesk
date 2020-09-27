<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\ORM\TableRegistry;

/**
 * Counters Controller
 *
 * @property \App\Model\Table\CountersTable $Counters
 */
class CountersController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $counters = $this->Authorization->applyScope($this->Counters->find())
            ->order('title')
            ->all();

        $this->set(compact('counters'));
        $this->set('_serialize', ['counters']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->setAction('edit');
    }

    /**
     * Edit method
     *
     * @param string|null $id Counter id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     */
    public function edit($id = null)
    {
        if ($id) {
            $counter = $this->Counters->get($id);
        } else {
            $counter = $this->Counters->newEmptyEntity();
            $counter->company_id = $this->getCurrentUser()->get('company_id');
        }

        $this->Authorization->authorize($counter);

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $counter = $this->Counters->patchEntity($counter, $this->getRequest()->getData());
            if ($this->Counters->save($counter)) {
                $this->Flash->success(__('The counter has been saved.'));

                return $this->redirect(['plugin' => null, 'controller' => 'Counters', 'action' => 'index']);
            }
            $this->Flash->error(__('The counter could not be saved. Please, try again.'));
        }

        $invoicesCounters = TableRegistry::get('LilInvoices.InvoicesCounters')
            ->find('invoicesList')
            ->andWhere(['owner_id' => $this->getCurrentUser()->get('company_id')])
            ->andWhere(['active' => true])
            ->andWhere(['kind' => 'issued'])
            ->toArray();

        $this->set(compact('counter', 'invoicesCounters'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id Counter id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete', 'get']);
        $counter = $this->Counters->get($id);

        $this->Authorization->authorize($counter);

        if ($this->Counters->delete($counter)) {
            $this->Flash->success(__('The counter has been deleted.'));
        } else {
            $this->Flash->error(__('The counter could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
