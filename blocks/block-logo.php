<a href="<?php block_field( 'link' ); ?>" class="logo_block" target="_blank" rel="noreferrer noopener">
  <?php echo wp_get_attachment_image( block_value( 'image' ), 'original' ); ?>
  <span><?php block_field( 'name' ); ?></span>
</a>
