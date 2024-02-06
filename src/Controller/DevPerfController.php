<?php

namespace Drupal\dev\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\Renderer;
use Drupal\dev\DevPerf;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Dev Tools routes.
 */
class DevPerfController extends ControllerBase {

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
   * The renderer service.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * The dev.perf service.
   *
   * @var \Drupal\dev\DevPerf
   */
  protected $devPerf;

  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The renderer service.
   * @param \Drupal\dev\DevPerf $devPerf
   *   The dev.perf service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $connection, Renderer $renderer, DevPerf $devPerf) {
    $this->entityTypeManager = $entity_type_manager;
    $this->connection = $connection;
    $this->renderer = $renderer;
    $this->devPerf = $devPerf;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('renderer'),
      $container->get('dev.perf')
    );
  }

  /**
   * Builds the response.
   */
  public function build() {
    $block_name = 'products_block';
    $build = $this->devPerf->profileBlock($block_name);

    return new HtmlResponse($this->renderer->renderRoot($build));
  }

}
