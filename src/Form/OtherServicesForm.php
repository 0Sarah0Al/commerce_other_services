<?php

namespace Drupal\commerce_other_services\Form;

use Drupal\Core\Database\Connection;
use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_store\CurrentStoreInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the donation form.
 */
class OtherServicesForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The cart manager.
   *
   * @var \Drupal\commerce_cart\CartManagerInterface
   */
  protected $cartManager;

  /**
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * The current store.
   *
   * @var \Drupal\commerce_store\CurrentStoreInterface
   */
  protected $currentStore;

    /**
     * The database connection.
     *
     * @var \Drupal\Core\Database\Connection
     */
    protected $databaseConnection;

  /**
   * Constructs a new OtherServicesForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   * @param \Drupal\commerce_store\CurrentStoreInterface $current_store
   *   The current store.
   * @param \Drupal\Core\Database\Connection $databaseConnection
   *   The database connection.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, CartManagerInterface $cart_manager, CartProviderInterface $cart_provider, CurrentStoreInterface $current_store, Connection $databaseConnection) {
    $this->entityTypeManager = $entity_type_manager;
    $this->cartManager = $cart_manager;
    $this->cartProvider = $cart_provider;
    $this->currentStore = $current_store;
    $this->databaseConnection = $databaseConnection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('commerce_cart.cart_manager'),
      $container->get('commerce_cart.cart_provider'),
      $container->get('commerce_store.current_store'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'other_services_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
      $account = $this->currentUser()->id();
      // SELECT service_name, amount, service_description, service_order FROM commerce_other_services
      // WHERE user_id = $account AND processed = 0
      $sql = "SELECT service_name, amount, service_description, service_order FROM commerce_other_services WHERE user_id = :user_id AND processed = 0";
      $result = $this->databaseConnection->query($sql, [':user_id' => $account]);
      if ($result) {
          while ($row = $result->fetchAssoc()) {
              $form['service_order'] = [
                  '#type' => 'number',
                  '#title' => $this->t('Service order number'),
                  '#default_value' => $row['service_order'],
                  '#disabled' => TRUE,
              ];
              $form['service_name'] = [
                  '#type' => 'textfield',
                  '#title' => $this->t('Service requested name'),
                  '#required' => TRUE,
                  '#default_value' => $row['service_name'],
                  '#disabled' => TRUE,
              ];
              $form['amount'] = [
                  '#type' => 'number',
                  '#title' => $this->t('Amount'),
                  '#required' => TRUE,
                  '#default_value' => $row['amount'],
                  '#disabled' => TRUE,
              ];
              $form['service_description'] = [
                  '#type' => 'textarea',
                  '#title' => $this->t('Description of the requested service'),
                  '#required' => TRUE,
                  '#default_value' => $row['service_description'],
                  '#disabled' => TRUE,
              ];
          }
      }
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add to cart'),
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
    $amount = $form_state->getValue('amount');
    $order_item = $this->entityTypeManager->getStorage('commerce_order_item')->create([
      'type' => 'other_services',
      'title' => $form_state->getValue('service_name'),
      'unit_price' => [
        'number' => $amount, 
        'currency_code' => 'USD',
      ],
    ]);
    $store = $this->currentStore->getStore();
    // Always use the 'default' order type.
    $cart = $this->cartProvider->getCart('default', $store);
    if (!$cart) {
      $cart = $this->cartProvider->createCart('default', $store);
    }
    $this->cartManager->addOrderItem($cart, $order_item, FALSE);

      $query = $this->databaseConnection->update('commerce_other_services');
      $query->fields(['processed' => 1])
          ->condition('service_order', $form_state->getValue('service_order'))
          ->execute();

    // Go to checkout.
    $form_state->setRedirect('commerce_checkout.form', ['commerce_order' => $cart->id()]);
  }

}
