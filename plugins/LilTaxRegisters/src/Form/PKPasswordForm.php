<?php
namespace LilTaxRegisters\Form;

use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Utility\Security;
use Cake\Validation\Validator;

class PKPasswordForm extends Form
{
    private $request = null;
    private $user = null;

    /**
     * Form constructor.
     *
     * @param object $request Request object.
     * @return void
     */
    public function __construct($request, $user)
    {
        $this->request = $request;
        $this->user = $user;
    }

    /**
     * Schema definition.
     *
     * @param Cake\ORM\Schema $schema Schema object.
     * @return object
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        return $schema
            ->addField('password', ['type' => 'string']);
    }

    /**
     * Validator definition
     *
     * @param Validator $validator Validator object.
     * @return object
     */
    protected function _buildValidator(Validator $validator): Validator
    {
        $user = $this->user;

        return $validator
            ->requirePresence('password')
            ->notEmpty('password')
            ->add('password', [
                'tryDecodeCert' => [
                    'rule' => function ($value, $context) use ($user) {
                        if (empty($user->cert_p12)) {
                            return false;
                        }

                        return (bool)Security::decrypt(base64_decode($user->cert_p12), $user->id, $value);
                    }
                ]
            ]);
    }

    /**
     * Form execute action
     *
     * @param array $data Post data.
     * @return bool
     */
    protected function _execute(array $data): bool
    {
        if (isset($data['password'])) {
            $decrypted = Security::decrypt(base64_decode($this->user->cert_p12), $this->user->id, $data['password']);

            if ($decrypted) {
                $this->request->getSession()->write('LilTaxRegisters.P12', base64_decode($decrypted));
                $this->request->getSession()->write('LilTaxRegisters.PKPassword', $data['password']);

                return true;
            }
        }

        return false;
    }
}
