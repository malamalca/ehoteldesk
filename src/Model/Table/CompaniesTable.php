<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Companies Model
 *
 * @property \Cake\ORM\Association\HasMany $Reservations
 * @property \Cake\ORM\Association\HasMany $Rooms
 * @property \Cake\ORM\Association\HasMany $Users
 * @method \App\Model\Entity\Company get($primaryKey, $options = [])
 * @method \App\Model\Entity\Company newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Company[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Company|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Company patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Company[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Company findOrCreate($search, callable $callback = null)
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CompaniesTable extends Table
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

        $this->setTable('companies');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Reservations', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('Registrations', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('Rooms', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('RoomTypes', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('ServiceTypes', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('Users', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('Counters', [
            'className' => 'Counters',
            'foreignKey' => 'company_id',
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
            ->notEmptyString('name');

        $validator
            ->allowEmptyString('street');

        return $validator;
    }
}
