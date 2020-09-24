<?php
namespace App\Model\Table;

use App\Model\Table\IsOwnedByTrait;
use ArrayObject;
use Cake\Event\Event;
use Cake\I18n\Date;
use Cake\I18n\FrozenDate;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Reservations Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Companies
 *
 * @method \App\Model\Entity\Reservation get($primaryKey, $options = [])
 * @method \App\Model\Entity\Reservation newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Reservation[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Reservation|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Reservation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Reservation[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Reservation findOrCreate($search, callable $callback = null)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ReservationsTable extends Table
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

        $this->setTable('reservations');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id'
        ]);

        $this->belongsTo('Counters', [
            'foreignKey' => 'counter_id'
        ]);

        $this->belongsTo('Rooms', [
            'foreignKey' => 'room_id'
        ]);
        $this->belongsTo('Clients', [
            'className' => 'LilCrm.Contacts',
            'foreignKey' => 'client_id'
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
            ->allowEmptyString('id', 'create')
            ->notEmptyString('counter_id')

            ->add('start', [
                'format' => ['rule' => 'date', 'last' => true],
            ])
            ->add('end', [
                'format' => ['rule' => 'date', 'last' => true],
                'greater' => [
                    'rule' => function ($value, $context) {
                        $end = new FrozenDate($value);

                        return $end->gte(new FrozenDate($context['data']['start']));
                    }
                ]
            ])
            ->notEmptyString('name');

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
        $rules->add($rules->existsIn(['company_id'], 'Companies'));
        $rules->add($rules->existsIn(['room_id'], 'Rooms'));
        $rules->add($rules->existsIn(['client_id'], 'Clients'));
        $rules->add($rules->existsIn(['counter_id'], 'Counters'));

        // check overlapping
        $rules->add(function ($entity, $options) {
            return !$this->checkOverlap($entity);
        }, 'overlapDates', ['errorField' => 'start']);
        $rules->add(function ($entity, $options) {
            $Registrations = TableRegistry::get('Registrations');

            return !$Registrations->checkOverlap($entity, true);
        }, 'overlapRegistrations', ['errorField' => 'start']);

        return $rules;
    }

    /**
     * Check if there are overlapping dates.
     *
     * @param object $entity Reservation entity
     * @param bool $skipIdCheck Do not filter entity's own id
     * @param uuid $excludeId Specified Id that should always be excluded.
     * @return bool
     */
    public function checkOverlap($entity, $skipIdCheck = false, $excludeId = null)
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
        if (!empty($excludeId)) {
            $conditions['NOT']['id'] = $excludeId;
        }

        return $this->exists($conditions);
    }

    /**
     * beforeSave method
     *
     * @param Event $event Event object.
     * @param Entity $entity Entity object.
     * @param ArrayObject $options Array object.
     * @return bool
     */
    public function beforeSave(Event $event, Entity $entity, ArrayObject $options)
    {
        if ($entity->isNew()) {
            $entity->no = $this->Counters->incNo($entity->counter_id);
            if (!$entity->no) {
                return false;
            }
        }

        return true;
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
        /*$filter['start'] = new FrozenDate();
        $filter['end'] = $filter['start']->addDays(30);*/
        //if (empty($filter['end'])) $filter['end'] = $filter['start'];

        if (empty($filter['start'])) {
            $theDate = new Date();
            if (empty($filter['month'])) {
                $filter['month'] = $theDate->month;
            } else {
                $theDate->month((int)$filter['month']);
                if (!empty($filter['year'])) {
                    $theDate->year((int)$filter['year']);
                }
            }
            $filter['year'] = $theDate->year;
            $filter['start'] = $theDate->addDays(-5);
            $filter['end'] = $theDate->copy()->addMonth(1)->addDays(10);
        } else {
            $filter['start'] = new Date($filter['start']);
            if (empty($filter['end'])) {
                $filter['end'] = $filter['start'];
            }
        }

        if (empty($filter['counter'])) {
            if (!$defaultCounter = $this->Counters->findDefaultCounter('V', $filter['owner'])) {
                return false;
            }
            $filter['counter'] = $defaultCounter->id;
        }

        $conditions = [
            'Reservations.company_id' => $filter['owner'],
            'Reservations.counter_id' => $filter['counter'],
            'AND' => [
                'Reservations.start <= ' => $filter['end'],
                'Reservations.end >= ' => $filter['start']
            ]
        ];

        if (!empty($filter['room'])) {
            $conditions['Reservations.room_id'] = $filter['room'];
        }

        $reservations = $q
            ->where($conditions)
            ->contain(['Rooms'])
            ->order(['start', 'room_id'])
            ->all();

        if ($findType == 'list') {
            $ret = [];
            foreach ($reservations as $r) {
                $dayCount = $r->end->diffInDays($r->start);
                for ($i = 0; $i <= $dayCount; $i++) {
                    if (empty($ret[$r->room_id][$r->start->addDays($i)->toDateString()])) {
                        $ret[$r->room_id][$r->start->addDays($i)->toDateString()]['main'] = $r;
                    } else {
                        $ret[$r->room_id][$r->start->addDays($i)->toDateString()]['afternoon'] = $r;
                    }
                }
            }
        } else {
            $ret = $reservations;
        }

        return $ret;
    }

    /**
     * Returns minYear
     *
     * @param string $counterId Counter id.
     * @return array
     */
    public function getMinYear($counterId)
    {
        $q = $this->find();

        $reg = $q->select()
            ->where(['counter_id' => $counterId])
            ->first();

        if ($reg) {
            return $reg->start->year;
        } else {
            $theDate = new FrozenDate();

            return $theDate->year;
        }
    }
}
