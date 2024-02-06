<?php

namespace Drupal\dev;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;

/**
 * Service description.
 */
class DevPerf {

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
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * Constructs a Perf object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    Connection $connection,
    RendererInterface $renderer,
    BlockManagerInterface $block_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->connection = $connection;
    $this->renderer = $renderer;
    $this->blockManager = $block_manager;
  }

  /**
   * Method description.
   */
  public function profileBlock($block_name) {
    $profile_data = &drupal_static('dev_profileblock', []);
    $profile_data[] = [
      'key' => 'Block Name',
      'value' => $block_name,
    ];
    $plugin_block = $this->blockManager->createInstance($block_name);

    // Build.
    $start = microtime(TRUE);
    $build = $plugin_block->build();
    $build['#cache']['max-age'] = 0;
    $profile_data[] = [
      'key' => 'Block Build',
      'value' => round((microtime(TRUE) - $start), 2) . 's',
    ];

    // Render.
    $start = microtime(TRUE);
    $html = $this->renderer->renderRoot($build);
    $profile_data[] = [
      'key' => 'Block Render',
      'value' => round((microtime(TRUE) - $start), 2) . 's',
    ];

    $profile_data_table = [
      '#type' => 'table',
      '#header' => [
        'Key',
        'Value',
      ],
      '#rows' => $profile_data,
    ];

    return [
      '#theme' => 'dev_profileblock',
      '#content' => $html,
      '#profile_data' => $profile_data_table,
    ];

  }

}
