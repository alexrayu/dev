<?php

namespace Drupal\dev\Drush\Commands;

use Drupal\mysql\Driver\Database\mysql\Connection;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A Drush commandfile.
 */
class ReplaceInDb extends DrushCommands {

  /**
   * Constructs a GeUsabilityCommands object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(
    private Connection $database
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
    );
  }

  /**
   * Replaces content strings in database.
   * 
   * This function expects that the value field will be in format 
   * table name __ value. 
   *
   * @param array $options
   *   An associative array of options.
   *
   * @option mode
   *   Whether to run replacement or display info about status (dry).
   * @usage arocom_dev_replaceInEntDb
   *   Replaces strings in DB fields.
   *
   * @command arocom_dev:replaceInEntDb
   * @aliases dev_rep_edb
   */
  public function replaceInEntDb($table_name, $find, $replace, $options = ['mode' => 'default']) {
    $parts = explode('__', $table_name);

    // Check if table and field exist.
    if (empty($parts[1])) {
      $this->logger()->error('Table name must be in the format {table_name}__{field_name}');
      return;
    }
    $field_name = $parts[1] . '_value';
    $schema = $this->database->schema();
    if (!$schema->fieldExists($table_name, $field_name)) {
      $this->logger()->error('Field @field_name does not exist in table @table_name', [
        '@field_name' => $field_name,
        '@table_name' => $table_name,
      ]);
      return;
    }

    // Get all rows with the field.
    $result = $this->database->select($table_name, 'tbn')
      ->fields('tbn')
      ->condition($field_name, "%$find%", 'LIKE')
      ->execute()
      ->fetchAll(\PDO::FETCH_ASSOC);
    $total = count($result);
    $this->io()->writeln('Found ' . $total . ' items to check and process in ' . $table_name . ' table.');
    if ($total === 0) {
      $this->io()->writeln('No matches in ' . $table_name . ' table.');
      return;
    }

    // Checks and confirmations.
    if ($options['mode'] !== 'default') {
      return;
    }
    if (!$this->io()->confirm('Continue?')) {
      return;
    }

    // Replace all <code> tags with <code class="language-php">.
    $progress_bar = $this->io()->createProgressBar($total);
    $progress_bar->start();
    foreach ($result as $row) {
      $value = $row[$field_name];
      $progress_bar->advance();

      // Handle the updates.
      $value = preg_replace($find, $replace, $value);

      // Update the row.
      $this->database->update($table_name)
        ->fields([$field_name => $value])
        ->condition('entity_id', $row['entity_id'])
        ->condition('revision_id', $row['revision_id'])
        ->condition('delta', $row['delta'])
        ->execute();
    }
    $progress_bar->finish();
    $this->io()->newLine();
  }

}
