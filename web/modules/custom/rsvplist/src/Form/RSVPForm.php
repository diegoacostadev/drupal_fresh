<?php

namespace Drupal\rsvplist\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Utility\EmailValidator;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * RSVPList Form.
 */
class RSVPForm extends FormBase {

  /**
   * User account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Email validator service.
   *
   * @var \Drupal\Component\Utility\EmailValidator
   */
  protected $emailValidator;

  /**
   * Drupal time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Drupal database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Drupal messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Get services by dependency injection.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User account.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   Route match service.
   * @param \Drupal\Component\Utility\EmailValidator $emailValidator
   *   Email validator service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   Time  service.
   * @param \Drupal\Core\Database\Connection $database
   *   Database service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   */
  public function __construct(
    AccountInterface $account,
    RouteMatchInterface $routeMatch,
    EmailValidator $emailValidator,
    TimeInterface $time,
    Connection $database,
    MessengerInterface $messenger,
  ) {
    $this->account = $account;
    $this->routeMatch = $routeMatch;
    $this->emailValidator = $emailValidator;
    $this->time = $time;
    $this->database = $database;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('current_user'),
      $container->get('current_route_match'),
      $container->get('email.validator'),
      $container->get('datetime.time'),
      $container->get('database'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'rsvplist_email_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $node = $this->routeMatch->getParameter('node');
    $nid = 0;

    if (!(is_null($node))) {
      $nid = $node->id();
    }

    $form['#attributes']['class'] = ['mb-5'];

    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email address'),
      '#size' => 25,
      "#description" => $this->t("We will send updates to the email address you provide."),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#attributes' => [
        'class' => ['btn btn-primary'],
      ],
      '#value' => $this->t('RSVP'),
    ];

    $form['nid'] = [
      '#type' => 'hidden',
      '#value' => $nid,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue('email');
    if (!($this->emailValidator->isValid($value))) {
      $form_state->setErrorByName('email',
        $this->t('It appears that @mail is not a valid email. Please try again', ['@mail' => $value])
      );
    }
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // $submitted_email = $form_state->getValue('email');
    // $this->messenger()->addMessage(
    // $this->t("The form is working! You entered @entry", [
    // '@entry' => $submitted_email
    // ]));
    try {
      // Begin phase 1: initiate variables to be saved.
      $uid = intval($this->account->id());
      $email = $form_state->getValue('email');
      $nid = $form_state->getValue('nid');
      $createdAt = $this->time->getRequestTime();
      // End phase 1: initiate variables to be saved.
      // Phase 2
      // Start to build a query builder object $query.
      // $query = \Drupal::database()->insert('rsvplist');
      $query = $this->database->insert('rsvplist');

      // Specify the fields that the query will insert into.
      $query->fields([
        'uid',
        'email',
        'nid',
        'createdAt',
      ])
        ->values([
          $uid,
          $email,
          $nid,
          $createdAt,
        ]);

      // Execute the query!
      $result = $query->execute();
      // End Phase 2.
      // Phase 3, provide a message to the user.
      $this->messenger()->addMessage(
        $this->t('Thank you for your RSVP, you are on the list for the event!')
      );

    }
    catch (\Exception $e) {
      // Phase 3, provide a message to the user.
      $this->messenger()->addError(
        $this->t('Unable to save RSVP settings at this time due to a database error. Please try again later.')
          );
    }
  }

}
