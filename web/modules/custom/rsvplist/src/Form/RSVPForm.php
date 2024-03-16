<?php

namespace Drupal\rsvplist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class RSVPForm extends FormBase {

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
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = 0;

    if (!(is_null($node))) {
      $nid = $node->id();
    }

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
      '#value' => t('RSVP'),
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

    if (!(\Drupal::service('email.validator')->isValid($value))) {
      $form_state->setErrorByName('email',
        $this->t('It appears that @mail is not a valid email. Please try again', ['@mail' => $value])
      );
    }
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $submitted_email = $form_state->getValue('email');
    $this->messenger()->addMessage($this->t("The form is working! You entered @entry", ['@entry' => $submitted_email]));
  }

}
