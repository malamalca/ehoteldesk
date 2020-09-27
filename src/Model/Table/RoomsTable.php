<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Rooms Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Companies
 * @property \Cake\ORM\Association\BelongsTo $RoomTypes
 * @property \Cake\ORM\Association\HasMany $Reservations
 * @method \App\Model\Entity\Room get($primaryKey, $options = [])
 * @method \App\Model\Entity\Room newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Room[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Room|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Room patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Room[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Room findOrCreate($search, callable $callback = null)
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RoomsTable extends Table
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

        $this->setTable('rooms');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
        ]);
        $this->belongsTo('RoomTypes', [
            'foreignKey' => 'room_type_id',
        ]);
        $this->hasMany('Reservations', [
            'foreignKey' => 'room_id',
        ]);
        $this->hasMany('Registrations', [
            'foreignKey' => 'room_id',
        ]);
        $this->belongsTo('Vats', [
            'className' => 'LilInvoices.Vats',
            'foreignKey' => 'vat_id',
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
            ->notEmptyString('no');

        $validator
            ->notEmptyString('title');

        $validator
            ->integer('beds')
            ->requirePresence('beds', 'create')
            ->notEmptyString('beds');

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
        $rules->add($rules->existsIn(['room_type_id'], 'RoomTypes'));

        $rules->addDelete(function ($entity, $options) {
            return !$this->Reservations->exists(['room_id' => $entity->id]);
        }, 'existsInReservations', ['errorField' => 'id']);

        return $rules;
    }

    /**
     * Returns list of rooms for specified owner
     *
     * @param string $findType Company Id.
     * @param bool $ownerId Show only active accounts.
     * @return mixed
     */
    public function findForOwner($findType, $ownerId)
    {
        $conditions = ['company_id' => $ownerId];
        $q = $this->find()
         ->where($conditions)
         ->order('no');

        $result = $q->all();

        if ($findType == 'list') {
            $ret = [];
            foreach ($result as $room) {
                $ret[$room->id] = $room->toString();
            }
        } else {
            $ret = $result;
        }

        return $ret;
    }
}
