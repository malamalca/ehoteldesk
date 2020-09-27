<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ServiceTypes Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Owners
 * @method \App\Model\Entity\ServiceType get($primaryKey, $options = [])
 * @method \App\Model\Entity\ServiceType newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\ServiceType[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ServiceType|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ServiceType patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ServiceType[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\ServiceType findOrCreate($search, callable $callback = null)
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ServiceTypesTable extends Table
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

        $this->setTable('service_types');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
        ]);
        $this->belongsTo('Registrations', [
            'foreignKey' => 'service_id',
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
            return !$this->Registrations->exists(['service_id' => $entity->id]);
        }, 'existsInRegistration', ['errorField' => 'id']);

        return $rules;
    }

    /**
     * Returns list of rooms for specified owner
     *
     * @param string $findType Finder type.
     * @param string $ownerId Owner id.
     * @return mixed
     */
    public function findForOwner($findType, $ownerId)
    {
        $conditions = ['company_id' => $ownerId];
        $result = $this->find()
         ->where($conditions)
         ->order('title')
         ->all();

        if ($findType == 'list') {
            $ret = [];
            foreach ($result as $s) {
                $ret[$s->id] = $s->title;
            }
        } else {
            $ret = $result;
        }

        return $ret;
    }
}
