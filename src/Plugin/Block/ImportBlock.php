<?php

namespace Drupal\import\Plugin\Block;

use Drupal\Core\Block\BlockBase;


/**
 * Provides a 'Import' Block.
 *
 * @Block(
 *   id = "import_block",
 *   admin_label = @Translation("Import block"),
 *   category = @Translation("Import"),
 * )
 */
class ImportBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    
    
   
    
    return array(
      '#markup' => $this->t('Import'),
    );
  }

}