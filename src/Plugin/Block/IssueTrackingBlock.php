<?php

namespace Drupal\issue_tracking_system\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Block list the latest 3 issues assigned to the current user.
 *
 * @Block(
 *   id = "issue_tracking_system_block",
 *   admin_label = @Translation("Issue Tracking System Block"),
 * )
 */
class IssueTrackingBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Account Interface Service.
   *
   * @var Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Constructs a Suppliers Filter block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $account
   *   The user account interface.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, AccountInterface $account) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $currentUser = $this->account->id();
    $nodeStorage = $this->entityTypeManager->getStorage('node');
    $userStorage = $this->entityTypeManager->getStorage('user');
    $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    $nids = $nodeStorage->getQuery()
      ->condition('type', 'issue')
      ->condition('field_assignee', $currentUser)
      ->sort('created', 'DESC')
      ->range(0, 3)
      ->execute();
    $nodes = $nodeStorage->loadMultiple($nids);

    if (!empty($nodes)) {
      foreach ($nodes as $key => $node) {
        $nodeValue[$key] = [
          'title' => $node->getTitle(),
          'description' => $node->body->getValue()[0]["value"],
          'assignee' => $userStorage->load($node->get('field_assignee')->target_id)->getDisplayName(),
        ];
        $watchers = $node->get('field_list_of_watchers')->referencedEntities();
        foreach ($watchers as $index => $watcher) {
          $value[$index] = $userStorage->load($watcher->id())->getDisplayName();
        }
        $nodeValue[$key] += [
          'list_of_watchers' => $value,
        ];
        $nodeValue[$key] += [
          'issue_type' => $termStorage->load($node->get('field_issue_type')->target_id)->getName(),
          'priority' => $termStorage->load($node->get('field_priority')->target_id)->getName(),
          'status' => $termStorage->load($node->get('field_issue_status')->target_id)->getName(),
          'reporter' => $node->getOwner()->getDisplayName(),
          'due_date' => $node->get('field_due_date')->getValue(),
          'url' => $node->toUrl()->toString(),
          'due_date' => $node->get('field_due_date')->getValue()[0]['value'],
        ];
      }
    }

    $build = [
      '#values' => $nodeValue,
      '#theme' => 'issue_tracking_system',
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
