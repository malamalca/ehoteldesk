<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EturizemLogs Model
 *
 * @method \App\Model\Entity\EturizemLog get($primaryKey, $options = [])
 * @method \App\Model\Entity\EturizemLog newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\EturizemLog[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EturizemLog|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EturizemLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EturizemLog[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\EturizemLog findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class EturizemLogsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('eturizem_logs');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
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
            ->integer('status')
            ->allowEmptyString('status');

        $validator
            ->scalar('xml')
            ->allowEmptyString('xml');

        $validator
            ->scalar('message')
            ->allowEmptyString('message');

        return $validator;
    }
}
