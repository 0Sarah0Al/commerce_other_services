<?php

namespace Drupal\commerce_other_services\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides the other services dashboard form.
 */
class OtherServicesDashboardForm extends FormBase {

    /**
     * The database connection.
     *
     * @var \Drupal\Core\Database\Connection
     */
    protected $databaseConnection;

    /**
     * Constructs a new OtherServicesDashboardForm object.
     *
     * @param \Drupal\Core\Database\Connection $databaseConnection
     *   The database connection.
     */
    public function __construct(Connection $databaseConnection) {
        $this->databaseConnection = $databaseConnection;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('database')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'other_services_dashboard_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $user_id = NULL, $user = NULL, $mail = NULL, $service_order = NULL) {
        $form['user_id'] = [
            '#type' => 'number',
            '#title' => $this->t('User id'),
            '#default_value' => $user_id,
            '#disabled' => TRUE,
        ];
        $form['user_name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('User name'),
            '#default_value' => $user,
            '#disabled' => TRUE,
        ];
        $form['user_email'] = [
            '#type' => 'email',
            '#title' => $this->t('User email'),
            '#default_value' => $mail,
            '#disabled' => TRUE,
        ];
        $form['service_order'] = [
            '#type' => 'number',
            '#title' => $this->t('Service order number'),
            '#default_value' => $service_order,
            '#disabled' => TRUE,
        ];
        $form['service_name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Service requested name'),
            '#required' => TRUE,
        ];
        $form['amount'] = [
            '#type' => 'number',
            '#title' => $this->t('Amount'),
            '#default_value' => 0,
            '#required' => TRUE,
        ];
        $form['service_description'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Description of the requested service'),
            '#required' => TRUE,
        ];
        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Submit'),
            '#button_type' => 'primary',
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        $amount = $form_state->getValue('amount');
        if (!is_numeric($amount)) {
            $form_state->setError($form['amount'], t('The amount must be a valid number.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $account = $this->currentUser();
        $entry = [
            'uid' => $account->id(),
            'user_id' => $form_state->getValue('user_id'),
            'user_name' => $form_state->getValue('user_name'),
            'user_email' => $form_state->getValue('user_email'),
            'service_order' => $form_state->getValue('service_order'),
            'service_name' => $form_state->getValue('service_name'),
            'amount' => $form_state->getValue('amount'),
            'service_description' => $form_state->getValue('service_description'),
        ];
        $query = $this->databaseConnection->insert('commerce_other_services');
        $query->fields($entry)->execute();
    }
}
