<?php

namespace Drupal\rsvplist\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller to show RVSP reports.
 */
class ReportController extends ControllerBase {

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
   * @param \Drupal\Core\Database\Connection $database
   *   Database service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   */
  public function __construct(Connection $database, MessengerInterface $messenger) {
    $this->database = $database;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('messenger')
    );
  }

  /**
   * Gets and returns all RSVPs for all nodes.
   *
   * These are returned as an associative array, with each row containing the
   * username,the node title and the user email of RSVP.
   *
   * @return array|null
   *   The entries values for querying the database.
   */
  protected function load() {
    try {
      $query = $this->database->select('rsvplist', 'r');

      // Joins the user table, so we can get the creator's name.
      $query->join('users_field_data', 'u', 'r.uid = u.uid');
      // Joins the node table, so we can get the event's name.
      $query->join('node_field_data', 'n', 'r.nid = n.nid');

      // Select this specific fields for the output.
      $query->addField('u', 'name', 'username');
      $query->addField('n', 'title');
      $query->addField('r', 'email');

      // Note that fecthAll and fetchAllAsoc will, by default, fetch using
      // wathever fetch mode was set on the query
      // (i.e. numeric array, assoc array or object).
      // Fetches can be modified by passing in a new fetch mode constant.
      // For fetchAll, it is the first parameter.
      $result = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

      return $result;
    }
    catch (\Exception $e) {
      // Display an user friendly message.
      $this->messenger()->addError($this->t('Unable to access the database at this time. Please try again later.'));
      return NULL;
    }
  }

  /**
   * Creates the RVSP report page.
   *
   * @return array
   *   Render array for the RSVP report output.
   */
  public function report() {
    $content = [];

    $content['sarass'] = [
      "#type" => 'markup',
      '#prefix' => '<h2>',
      '#suffix' => '</h2>',
      '#markup' => '<p><b> ' . $this->t('Testing') . '</b></p>',
    ];

    $content['message'] = [
      '#markup' => $this->t('Below is a list of all Event RVSPs including username, email address, and the name of event they will be attending.'),
    ];

    $headers = [
      $this->t('Username'),
      $this->t('Event'),
      $this->t('Email'),
    ];

    // Beacuse load() method returns an associative array, with each table row
    // as its own array, we can simple define the HTML table row like this:
    $rows = $this->load();

    $content['table'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => $this->t('No entries available.'),
    ];

    // Dont cache this page by setting the max-age to 0.
    $content['#cache']['max-age'] = 0;

    return $content;
  }

}
