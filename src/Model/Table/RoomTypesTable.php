<?php
namespace App\Model\Table;

use App\Model\Table\IsOwnedByTrait;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RoomTypes Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Companies
 * @property \Cake\ORM\Association\HasMany $Rooms
 *
 * @method \App\Model\Entity\RoomType get($primaryKey, $options = [])
 * @method \App\Model\Entity\RoomType newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\RoomType[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RoomType|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RoomType patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RoomType[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\RoomType findOrCreate($search, callable $callback = null)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RoomTypesTable extends Table
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

        $this->setTable('room_types');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id'
        ]);
        $this->hasMany('Rooms', [
            'foreignKey' => 'room_type_id'
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
            ->notEmptyString('title');

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

        $rules->addDelete(function ($entity, $options) {
            return !$this->Rooms->exists(['room_type_id' => $entity->id]);
        }, 'existsInRooms');

        return $rules;
    }

    /**
     * Returns list of rooms for specified owner
     *
     * @param uuid $findType Company Id.
     * @param bool $q Show only active accounts.
     * @return mixed
     */
    public function findForOwner($findType, $q)
    {
        $ret = $q
         ->order('title')
         ->all();

        return $ret;
    }
}
