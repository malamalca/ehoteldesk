<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * RoomTypes Controller
 *
 * @property \App\Model\Table\RoomTypesTable $RoomTypes
 */
class RoomTypesController extends AppController
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
                return !empty($this->getRequest()->getParam('pass.0')) && $this->RoomTypes->isOwnedBy($this->getRequest()->getParam('pass.0'), $this->getCurrentUser()->get('company_id'));
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
        $roomTypes = $this->Authorization->applyScope($this->RoomTypes->find())
            ->order('title')
            ->all();

        $this->set(compact('roomTypes'));
        $this->set('_serialize', ['roomTypes']);
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
     * @param string|null $id Room Type id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        if ($id) {
            $roomType = $this->RoomTypes->get($id);
        } else {
            $roomType = $this->RoomTypes->newEmptyEntity();
            $roomType->company_id = $this->getCurrentUser()->get('company_id');
        }

        $this->Authorization->authorize($roomType);

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $roomType = $this->RoomTypes->patchEntity($roomType, $this->getRequest()->getData());
            if ($this->RoomTypes->save($roomType)) {
                $this->Flash->success(__('The room type has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The room type could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('roomType'));
        $this->set('_serialize', ['roomType']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Room Type id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete', 'get']);
        $roomType = $this->RoomTypes->get($id);
        $this->Authorization->authorize($roomType);

        if ($this->RoomTypes->delete($roomType)) {
            $this->Flash->success(__('The room type has been deleted.'));
        } else {
            $err = $roomType->getError('id');
            if (!empty($err)) {
                if (isset($err['existsInRooms'])) {
                    $this->Flash->error(__('The room type is linked to a room and cannot be deleted.'));
                }
            } else {
                $this->Flash->error(__('The room type could not be deleted. Please, try again.'));
            }
        }

        return $this->redirect(['controller' => 'RoomTypes', 'action' => 'index']);
    }
}
