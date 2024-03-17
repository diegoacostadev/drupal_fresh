<?php

namespace Drupal\rsvplist\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the RSVP main block.
 *
 * @Block(
 *  id="rsvp_block",
 *  admin_label=@Translation("The RSVP Block")
 * )
 */
class RSVPBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Drupal formBuilder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $formBuilder, RouteMatchInterface $routeMatch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $formBuilder;
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
      $container->get('current_route_match'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function build() {
    // Return [
    //      '#type' => 'markup',
    //      '#markup' => $this->t('My RSVP List Block.'),
    //    ];.
    return $this->formBuilder->getForm('Drupal\rsvplist\Form\RSVPForm');
  }

  /**
   * {@inheritDoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $node = $this->routeMatch->getParameter('node');

    if (!is_null($node)) {
      return AccessResult::allowedIfHasPermissions($account, ['view rsvplist']);
    }

    return AccessResult::forbidden();
  }

}
