<?php
namespace App\Model\Table;

use App\Model\Table\IsOwnedByTrait;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Registrations Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Companies
 * @property \Cake\ORM\Association\BelongsTo $Clients
 *
 * @method \App\Model\Entity\Registration get($primaryKey, $options = [])
 * @method \App\Model\Entity\Registration newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Registration[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Registration|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Registration patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Registration[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Registration findOrCreate($search, callable $callback = null)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RegistrationsTable extends Table
{
    use IsOwnedByTrait;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('registrations');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Counters', [
            'foreignKey' => 'counter_id',
            'joinType' => 'INNER'
        ]);

        $this->belongsTo('Clients', [
            'className' => 'LilCrm.Contacts',
            'foreignKey' => 'client_id'
        ]);
        $this->belongsTo('Rooms', [
            'foreignKey' => 'room_id'
        ]);
        $this->belongsTo('ServiceTypes', [
            'foreignKey' => 'service_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('id')
            ->allowEmptyString('id', 'create');

        $validator
            ->notEmptyString('client_id');

        $validator
            ->notEmptyString('room_id');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['counter_id'], 'Counters'));

        //$rules->add($rules->existsIn(['client_id'], 'Clients'));
        $rules->add($rules->existsIn(['room_id'], 'Rooms'));
        $rules->add($rules->existsIn(['service_id'], 'ServiceTypes'));

        // check overlapping
        /*$rules->add(function($entity, $options) {
            return !$this->checkOverlap($entity);
        }, 'overlapDates', ['errorField' => 'start']);*/
        $rules->add(function ($entity, $options) {
            $Reservations = TableRegistry::get('Reservations');

            return !$Reservations->checkOverlap($entity, true, $entity->reservation_id);
        }, 'overlapReservations', ['errorField' => 'start']);

        return $rules;
    }

    /**
     * Check if there are overlapping dates.
     *
     * @param object $entity Registration entity
     * @param bool $skipIdCheck Do not filter entity's own id
     * @return bool
     */
    public function checkOverlap($entity, $skipIdCheck = false)
    {
        $conditions = [
            'company_id' => $entity->company_id,
            'room_id' => $entity->room_id,
            'AND' => [
                'start <=' => $entity->end->toMutable()->addDays(-1),
                'end >=' => $entity->start->toMutable()->addDays(1)
            ]
        ];
        if ($entity->isNew() === false && $skipIdCheck === false) {
            $conditions['NOT']['id'] = $entity->id;
        }
        //return !$options['repository']->exists($conditions);
        return $this->exists($conditions);
    }

    /**
     * Returns list of registrations for specified owner
     *
     * @param uuid $findType Company Id.
     * @param bool $ownerId Show only active accounts.
     * @return mixed
     */
    public function findForOwner($findType, $ownerId)
    {
        $conditions = ['Registrations.company_id' => $ownerId];
        $ret = $this->find()
         ->where($conditions)
         ->contain(['Clients', 'Rooms', 'ServiceTypes']);

        $ret->all();

        return $ret;
    }

    /**
     * Returns list of reservations for specified filter
     *
     * @param string $findType 'all', 'list',..
     * @param array $filter Filter data
     * @return array
     */
    public function filter($findType, $q, &$filter)
    {
        $conditions = [
        ];

        if (isset($filter['on'])) {
            $conditions['Registrations.start'] = $filter['on'];
        }

        if (!empty($filter['start'])) {
            if (empty($filter['end'])) {
                $filter['end'] = $filter['start'];
            }
            $conditions['AND'] = [
                'Registrations.start <= ' => $filter['end'],
                'Registrations.end >= ' => $filter['start']
            ];
        }

        if (isset($filter['room'])) {
            $conditions['Registrations.room_id'] = $filter['room'];
        }
        if (isset($filter['client'])) {
            $conditions['Registrations.client_id'] = $filter['client'];
        }

        if (empty($filter['counter'])) {
            if (!$defaultCounter = $this->Counters->findDefaultCounter('V', $filter['owner'])) {
                return false;
            }
            $filter['counter'] = $defaultCounter->id;
        }
        $conditions['Registrations.counter_id'] = $filter['counter'];

        if (!empty($filter['eturizem'])) {
            if ($filter['eturizem'] == 'notsent') {
                $conditions['Registrations.etur_guid IS'] = null;
            } else {
                $conditions['Registrations.etur_guid IS NOT'] = null;
            }
        }

        $registrations = $q->where($conditions)
            ->contain(['Rooms', 'ServiceTypes', 'Counters'])
            ->order(['start'])
            ->all();

        if ($findType == 'list') {
            $ret = [];
            foreach ($registrations as $r) {
                $dayCount = $r->end->diffInDays($r->start);
                for ($i = 0; $i <= $dayCount; $i++) {
                    if ($i == 0) {
                        $target = 'afternoon';
                    } elseif ($i == $r->end->diffInDays($r->start)) {
                        $target = 'morning';
                    } else {
                        $target = 'main';
                    }
                    if (isset($ret[$r->room_id][$r->start->addDays($i)->toDateString()][$target])) {
                        $ret[$r->room_id][$r->start->addDays($i)->toDateString()][$target]++;
                    } else {
                        $ret[$r->room_id][$r->start->addDays($i)->toDateString()][$target] = 1;
                    }
                }
            }
        } else {
            $ret = $registrations;
        }

        return $ret;
    }

    /**
     * Mark entities as sent
     *
     * @param Collection $registrations Registrations list.
     * @param array $response Remote server response.
     * @return void
     */
    public function markEturizemSent($registrations, $response)
    {
        foreach ($registrations as $registration) {
            $registration->etur_guid = $response['guid'];
            $registration->etur_time = $response['time'];
            $this->save($registration);
        }
    }
}
