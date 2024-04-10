<?php

namespace Drupal\rsvplist;

use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\node\Entity\Node;

/**
 * Service Class.
 */
class EnablerService {

  /**
   * Database service.
   *
   * @var Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Messenger service.
   *
   * @var Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  public function __construct(Connection $connection, MessengerInterface $messenger) {
    $this->database = $connection;
    $this->messenger = $messenger;
  }

  /**
   * Check if an individual node is enabled.
   *
   * @param Drupal\node\Entity\Node $node
   *   whether the node is enabled for RSVP list functionality or not.
   *
   * @return bool
   *   whether the node is enabled for RSVP list functionality or not.
   */
  public function isNodeEnabled(Node &$node) {
    if ($node->isNew()) {
      return FALSE;
    }

    try {
      $select = $this->database->select('rsvplist_enabled', 're');
      $select->fields('re', ['nid']);
      $select->condition('nid', $node->id());
      $results = $select->execute();

      return !empty($results->fetchCol());
    }
    catch (\Exception $e) {
      $this->messenger->addError(
        $this->t('Unable to determine RSVPList settings at this time. Please try again later.')
          );
      return NULL;
    }
  }

  /**
   * Check if an individual node is enabled.
   *
   * @param Drupal\node\Entity\Node $node
   *   whether the node is enabled for RSVP list functionality or not.
   *
   * @trhows Exception
   */
  public function setNodeEnabled(Node &$node) {
    try {
      if (!($this->isNodeEnabled($node))) {
        $insert = $this->database->insert('rsvplist_enabled');
        $insert->fields(['nid']);
        $insert->values([$node->id()]);
        $insert->execute();
      }
    }
    catch (\Exception $e) {
      $this->messenger->addError(
        $this->t('Unable to save RSVP settings at this time. Please try again later.')
      );
    }
  }

  /**
   * Check if an individual node is enabled.
   *
   * @param Drupal\node\Entity\Node $node
   *   whether the node is enabled for RSVP list functionality or not.
   *
   * @trhows Exception
   */
  public function deleteNodeEnabled(Node &$node) {
    try {
      $delete = $this->database->delete('rsvplist_enabled');
      $delete->condition('nid', $node->id());
      $delete->execute();
    }
    catch (\Exception $e) {
      $this->messenger->addError(
        $this->t('Unable to save RSVP settings at this time. Please try again later.')
      );
    }
  }

}
