<?php
namespace App\Model\Table;

use App\Model\Entity\Counter;
use App\Model\Table\IsOwnedByTrait;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Counters Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Companies
 *
 * @method \App\Model\Entity\Counter get($primaryKey, $options = [])
 * @method \App\Model\Entity\Counter newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Counter[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Counter|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Counter patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Counter[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Counter findOrCreate($search, callable $callback = null)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CountersTable extends Table
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

        $this->setTable('counters');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER'
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
            ->notEmptyString('kind');

        $validator
            ->notEmptyString('title');

        $validator
            ->allowEmptyString('template');

        $validator
            ->integer('counter')
            ->requirePresence('counter', 'create')
            ->notEmptyString('counter');

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
        return $rules;
    }

    /**
     * Returns primary counter of specified kind
     *
     * @param char $kind Counter kind.
     * @param uuid $ownerId Company Id.
     * @return mixed
     */
    public function getPrimary($kind, $ownerId)
    {
        $conditions = ['company_id' => $ownerId, 'kind' => $kind];
        $ret = $this->find()
         ->where($conditions);
        $ret->first();

        return $ret;
    }

    /**
     * Returns list of counters for specified owner
     *
     * @param string $findType Find 'all', 'list',..
     * @param string $kind Counter kind.
     * @param uuid $ownerId Owner Id.
     * @return mixed
     */
    public function findForOwner($findType, $kind, $ownerId)
    {
        $conditions = ['company_id' => $ownerId, 'kind' => $kind];
        if ($kind == '*') {
            unset($conditions['kind']);
        }
        $ret = $this->find()
         ->where($conditions)
         ->order('title');

        $ret->all();

        return $ret;
    }

    /**
     * Returns default counter
     *
     * @param string $kind Counter kind.
     * @param uuid $ownerId Owner Id.
     * @return mixed
     */
    public function findDefaultCounter($kind, $ownerId)
    {
        $conditions = ['company_id' => $ownerId, 'kind' => $kind];
        $ret = false;

        $counter = $this->find()
         ->where($conditions)
         ->first();

        if ($counter) {
            $ret = $counter;
        }

        return $ret;
    }

    /**
     * Returns next number for specified counter id
     *
     * @param uuid $counter Counter Id.
     * @return mixed
     */
    public function getNextNo($counter)
    {
        if (!is_a($counter, 'App\Model\Entity\Counter')) {
            $counter = $this->find()
             ->where(['id' => $counter])
             ->first();
        }

        $ret = false;
        if ($counter) {
            $ret = sprintf($counter->template, $counter->counter + 1);
        }

        return $ret;
    }

    /**
     * Increases specified counter.
     *
     * @param uuid $counter Counter Id.
     * @return mixed
     */
    public function incNo($counter)
    {
        if (!is_a($counter, 'App\Model\Entity\Counter')) {
            $counter = $this->find()
             ->where(['id' => $counter])
             ->first();
        }

        $ret = false;
        if ($counter) {
            $counter->counter++;
            if ($this->save($counter)) {
                $ret = sprintf($counter->template, $counter->counter);
            }
        }

        return $ret;
    }
}
