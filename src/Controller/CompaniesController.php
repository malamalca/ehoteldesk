<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\EventInterface;

/**
 * Companies Controller
 *
 * @property \App\Model\Table\CompaniesTable $Companies
 *
 * @method \App\Model\Entity\Company[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CompaniesController extends AppController
{
    /**
     * BeforeFilter method.
     *
     * @param Cake\Event\EventInterface $event Cake Event object.
     *
     * @return void
     */
    /*public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        //$this->Security->setConfig('unlockedActions', ['add']);
    }*/

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
            case 'add':
            case 'delete':
                return $this->userLevel('root');
            case 'edit':
            case 'view':
                if ($this->userLevel('root')) {
                    return true;
                }

                if ($this->userLevel('admin') && ($this->getCurrentUser()->get('company_id') == $this->getRequest()->getParam('pass.0'))) {
                    return true;
                }

                return false;
        }
    }*/

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $companies = $this->paginate($this->Companies);
        $this->Authorization->skipAuthorization();

        $this->set(compact('companies'));
    }

    /**
     * View method
     *
     * @param string|null $id Company id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $company = $this->Companies->get($id, [
            'contain' => ['Users', 'Counters']
        ]);

        $this->Authorization->authorize($company);

        $this->set('company', $company);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->setAction('edit');
    }

    /**
     * Edit method
     *
     * @param string|null $id Company id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        if (empty($id)) {
            $company = $this->Companies->newEmptyEntity();
        } else {
            $company = $this->Companies->get($id);
        }

        $this->Authorization->authorize($company);

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $company = $this->Companies->patchEntity($company, $this->getRequest()->getData());
            if ($this->Companies->save($company)) {
                $this->Flash->success(__('The company has been saved.'));

                if ($redirect = $this->getRequest()->getData('referer')) {
                    return $this->redirect($redirect);
                }

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The company could not be saved. Please, try again.'));
        }
        $this->set(compact('company'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Company id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete', 'get']);
        $company = $this->Companies->get($id);

        $this->Authorization->authorize($company);

        if ($this->Companies->delete($company)) {
            $this->Flash->success(__('The company has been deleted.'));
        } else {
            $this->Flash->error(__('The company could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
