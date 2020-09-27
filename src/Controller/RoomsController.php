<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\I18n\Date;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;

/**
 * Rooms Controller
 *
 * @property \App\Model\Table\RoomsTable $Rooms
 */
class RoomsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $rooms = $this->Authorization->applyScope($this->Rooms->find())
            ->order('title')
            ->all();

        $roomTypes = $this->Rooms->RoomTypes
            ->findForOwner(
                'list',
                $this->Authorization->applyScope($this->Rooms->RoomTypes->find('list'))
            )
            ->toArray();
        $this->set(compact('rooms', 'roomTypes'));
        $this->set('_serialize', ['rooms']);
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
     * @param string|null $id Room id.
     * @return \Cake\Http\Response|void Redirects on successful edit, renders view otherwise.
     */
    public function edit($id = null)
    {
        if ($id) {
            $room = $this->Rooms->get($id);
        } else {
            $room = $this->Rooms->newEmptyEntity();
            $room->company_id = $this->getCurrentUser()->get('company_id');
        }
        $this->Authorization->authorize($room);

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $room = $this->Rooms->patchEntity($room, $this->getRequest()->getData());
            if ($this->Rooms->save($room)) {
                $this->Flash->success(__('The room has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The room could not be saved. Please, try again.'));
            }
        }

        $roomTypes = $this->Rooms->RoomTypes
            ->findForOwner('list', $this->Authorization->applyScope($this->Rooms->RoomTypes->find('list'), 'index'))
            ->toArray();

        if (Configure::read('useInvoices')) {
            $vatLevels = TableRegistry::get('LilInvoices.Vats')->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'descript',
                ])
                ->where(['owner_id' => $this->getCurrentUser()->get('company_id')])
                ->order(['descript'])
                ->toArray();
        }

        $this->set(compact('room', 'roomTypes', 'vatLevels'));
        $this->set('_serialize', ['room']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Room id.
     * @return \Cake\Http\Response|null Redirects to index.
     */
    public function delete($id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete', 'get']);
        $room = $this->Rooms->get($id);
        $this->Authorization->authorize($room);

        if ($this->Rooms->delete($room)) {
            $this->Flash->success(__('The room has been deleted.'));
        } else {
            $err = $room->getError('id');
            if (!empty($err)) {
                if (isset($err['existsInReservations'])) {
                    $this->Flash->error(__('The room is linked to a reservation and cannot be deleted.'));
                }
            } else {
                $this->Flash->error(__('The room could not be deleted. Please, try again.'));
            }
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Report Vacancies method
     *
     * @return void
     */
    public function reportVacancies()
    {
        $CountersTable = TableRegistry::get('Counters');
        $counters = $CountersTable
            ->findForOwner('list', 'V', $this->getCurrentUser()->get('company_id'))
            ->combine('id', 'title')
            ->toArray();
        $this->set(compact('counters'));

        $this->Authorization->skipAuthorization();

        $aDate = $this->getRequest()->getQuery('on');
        if (!empty($aDate)) {
            error_reporting(0);
            $aDate = new FrozenDate($aDate);
            $this->viewBuilder()->setClassName('Lil.Pdf');

            $RoomsTable = TableRegistry::get('Rooms');
            $q = $this->Authorization->applyScope($this->Rooms->find(), 'index');

            $rooms = $q
                ->select($RoomsTable)
                ->select(['RoomTypes.title'])
                ->select(['Reservations.id', 'Registrations.id'])
                ->notMatching('Reservations', function ($q) use ($aDate) {
                    return $q->where([
                        'Reservations.counter_id' => $this->getRequest()->getQuery('counter'),
                        'Reservations.start <=' => $aDate,
                        'Reservations.end >=' => $aDate,
                    ]);
                })
                ->notMatching('Registrations', function ($q) use ($aDate) {
                    return $q->where([
                        'Registrations.counter_id' => $this->getRequest()->getQuery('counter'),
                        'Registrations.start <=' => $aDate,
                        'Registrations.end >=' => $aDate,
                    ]);
                })
                ->contain(['RoomTypes'])
                ->order(['RoomTypes.title', 'Rooms.no', 'Rooms.title'])
                ->all();

                /*->where([
                    'Rooms.company_id' => $this->getCurrentUser()->get('company_id'),
                    'NOT' => [
                        function ($exp) use ($q, $aDate) {
                            return $exp->between($aDate->toDateString(), $q->newExpr()->add('Reservations.start'), $q->newExpr()->add('Reservations.end'));
                        }
                    ]
                ])
                ->contain(['Reservations', 'Registrations'])
                ->order('Rooms.beds')
                ->all();*/
            $counter = $counters[$this->getRequest()->getQuery('counter')];
            $this->set(compact('rooms', 'aDate', 'counter'));

            $this->response = $this->response->withType('application/pdf');
        }
    }

    /**
     * Report Booked Rooms method
     *
     * @return void
     */
    public function reportBooked()
    {
        $CountersTable = TableRegistry::get('Counters');
        $counters = $CountersTable
            ->findForOwner('list', 'V', $this->getCurrentUser()->get('company_id'))
            ->combine('id', 'title')
            ->toArray();
        $this->set(compact('counters'));

        $this->Authorization->skipAuthorization();

        $span = $this->getRequest()->getQuery('span');
        if (!empty($span)) {
            if (in_array($span, ['date', 'month'])) {
                error_reporting(0);

                if ($span == 'date') {
                    $aStartDate = new Date($this->getRequest()->getQuery('on'));
                    $aDate = new FrozenDate($this->getRequest()->getQuery('on'));
                    $aDays = 1;
                } elseif ($span == 'month') {
                    $aSpanMonth = $this->getRequest()->getQuery('month.month');
                    $aSpanYear = $this->getRequest()->getQuery('month.year');
                    $aStartDate = new Date(implode('-', [$aSpanYear, $aSpanMonth, '01']));
                    $aDate = implode('-', [
                        $this->getRequest()->getQuery('month.year'),
                        $this->getRequest()->getQuery('month.month'),
                    ]);
                    $aDays = cal_days_in_month(CAL_GREGORIAN, $aSpanMonth, $aSpanYear);
                }

                $this->viewBuilder()->setClassName('Lil.Pdf');

                $RoomsTable = TableRegistry::get('Rooms');

                $days = [];
                for ($i = 0; $i < $aDays; $i++) {
                    $q = $this->Authorization->applyScope($this->Rooms->find(), 'index');
                    $q
                        ->select(['Rooms.no', 'Rooms.title', 'beds_sold' => $q->func()->count('Registrations.id')])
                        ->distinct(['Rooms.no', 'Rooms.title'])
                        ->matching('Registrations', function ($q) use ($aStartDate) {
                            return $q->where([
                                'Registrations.counter_id' => $this->getRequest()->getQuery('counter'),
                                'Registrations.start <=' => $aStartDate,
                                'Registrations.end >=' => $aStartDate,
                            ]);
                        })
                        ->order(['Rooms.no', 'Rooms.title']);

                    $days[$aStartDate->toDateString()] = $q->all();
                    $aStartDate->addDay();
                }

                $counter = $counters[$this->getRequest()->getQuery('counter')];
                $this->set(compact('days', 'aDate', 'counter'));

                $this->response = $this->response->withType('application/pdf');
            }
        }
    }
}
