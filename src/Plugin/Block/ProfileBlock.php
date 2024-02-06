<?php

namespace Drupal\dev\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dev\DevPerf;

/**
 * Provides a profileblock block.
 *
 * @Block(
 *   id = "dev_profileblock",
 *   admin_label = @Translation("Dev Profile Block"),
 *   category = @Translation("Dev")
 * )
 */
class ProfileBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The dev.perf service.
   *
   * @var \Drupal\dev\DevPerf
   */
  protected $devPerf;

  /**
   * Constructs a new ProfileBlock instance.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\dev\DevPerf $devPerf
   *  The dev.perf service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, Connection $connection, \Drupal\dev\DevPerf $devPerf) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->connection = $connection;
    $this->devPerf = $devPerf;
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
      $container->get('database'),
      $container->get('dev.perf')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $block_name = 'services_block';
    return $this->devPerf->profileBlock($block_name);
  }

}
