<?php
namespace LilTaxRegisters\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

use Lil\Model\Table\IsOwnedByTrait;

/**
 * BusinessPremises Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Owners
 *
 * @method \LilTaxRegisters\Model\Entity\BusinessPremise get($primaryKey, $options = [])
 * @method \LilTaxRegisters\Model\Entity\BusinessPremise newEntity($data = null, array $options = [])
 * @method \LilTaxRegisters\Model\Entity\BusinessPremise[] newEntities(array $data, array $options = [])
 * @method \LilTaxRegisters\Model\Entity\BusinessPremise|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \LilTaxRegisters\Model\Entity\BusinessPremise patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \LilTaxRegisters\Model\Entity\BusinessPremise[] patchEntities($entities, array $data, array $options = [])
 * @method \LilTaxRegisters\Model\Entity\BusinessPremise findOrCreate($search, callable $callback = null)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class BusinessPremisesTable extends Table
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

        $this->setTable('business_premises');
        $this->setDisplayField('title');
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
            ->notEmptyString('issuer_taxno')
            ->add('issuer_taxno', [
                'numeric' => ['rule' => ['numeric']],
                'minLength' => ['rule' => ['minLength', 8]],
                'maxLength' => ['rule' => ['maxLength', 8]],
            ]);

        $validator
            ->notEmptyString('no')
            ->add('no', [
                'alphanumeric' => ['rule' => ['alphanumeric']],
                'minLength' => ['rule' => ['minLength', 1]],
                'maxLength' => ['rule' => ['maxLength', 20]],
            ]);

        $validator
            ->notEmptyString('title');

        $validator
            ->requirePresence('kind', 'create')
            ->notEmptyString('kind');

        $validator
            ->allowEmptyString('casadral_number')
            ->add('casadral_number', [
                'numeric' => ['rule' => ['numeric']],
                'minLength' => ['rule' => ['minLength', 1]],
                'maxLength' => ['rule' => ['maxLength', 4]],
            ]);

        $validator
            ->allowEmptyString('building_number')
            ->add('building_number', [
                'numeric' => ['rule' => ['numeric']],
                'minLength' => ['rule' => ['minLength', 1]],
                'maxLength' => ['rule' => ['maxLength', 5]],
            ]);

        $validator
            ->allowEmptyString('building_section_number')
            ->add('building_section_number', [
                'numeric' => ['rule' => ['numeric']],
                'minLength' => ['rule' => ['minLength', 1]],
                'maxLength' => ['rule' => ['maxLength', 4]],
            ]);

        $validator
            ->allowEmptyString('street');

        $validator
            ->allowEmptyString('house_number');

        $validator
            ->allowEmptyString('house_number_additional');

        $validator
            ->allowEmptyString('community');

        $validator
            ->allowEmptyString('city');

        $validator
            ->allowEmptyString('postal_code');

        $validator
            ->allowEmptyString('mo_type');

        $validator
            ->date('validity_date')
            ->notEmptyString('validity_date');

        $validator
            ->boolean('closed')
            ->notEmptyString('closed');

        $validator
            ->allowEmptyString('sw_taxno');

        $validator
            ->allowEmptyString('sw_title');

        $validator
            ->allowEmptyString('notes');

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
     * Returns list of rooms for specified owner
     *
     * @param string $findType Company Id.
     * @param bool $ownerId Show only active accounts.
     * @return mixed
     */
    public function findForOwner($findType, $ownerId)
    {
        $conditions = ['owner_id' => $ownerId];
        $ret = $this->find($findType)
            ->where($conditions)
            ->order('title');

        $ret->all();

        return $ret;
    }
}
