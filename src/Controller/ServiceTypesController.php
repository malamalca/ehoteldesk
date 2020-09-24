<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * ServiceTypes Controller
 *
 * @property \App\Model\Table\ServiceTypesTable $ServiceTypes
 */
class ServiceTypesController extends AppController
{

    /**
     * isAuthorized method.
     *
     * @param array $user User
     * @return bool
     */
    /*public function isAuthorized($user)
    {
        switch ($this->getRequest()->getParam('action')) {
            case 'index':
                return true;
            case 'add':
                return $this->userLevel('admin');
            case 'edit':
            case 'delete':
                return !empty($this->getRequest()->getParam('pass.0')) && $this->ServiceTypes->isOwnedBy($this->getRequest()->getParam('pass.0'), $this->getCurrentUser()->get('company_id'));
            default:
                return false;
        }
    }*/

    /**
     * Index method
     *
     * @return \Cake\Network\Response|void
     */
    public function index()
    {
        $serviceTypes = $this->Authorization->applyScope($this->ServiceTypes->find())
            ->order('title')
            ->all();

        $this->set(compact('serviceTypes'));
        $this->set('_serialize', ['serviceTypes']);
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
     * @param string|null $id Service Type id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        if ($id) {
            $serviceType = $this->ServiceTypes->get($id);
        } else {
            $serviceType = $this->ServiceTypes->newEmptyEntity();
            $serviceType->company_id = $this->getCurrentUser()->get('company_id');
        }

        $this->Authorization->authorize($serviceType);


        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $serviceType = $this->ServiceTypes->patchEntity($serviceType, $this->getRequest()->getData());
            if ($this->ServiceTypes->save($serviceType)) {
                $this->Flash->success(__('The service type has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The service type could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('serviceType'));
        $this->set('_serialize', ['serviceType']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Service Type id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete', 'get']);
        $serviceType = $this->ServiceTypes->get($id);

        $this->Authorization->authorize($serviceType);

        if ($this->ServiceTypes->delete($serviceType)) {
            $this->Flash->success(__('The service type has been deleted.'));
        } else {
            $this->Flash->error(__('The service type could not be deleted. Please, try again.'));
        }

        return $this->redirect(['controller' => 'ServiceTypes', 'action' => 'index']);
    }
}
