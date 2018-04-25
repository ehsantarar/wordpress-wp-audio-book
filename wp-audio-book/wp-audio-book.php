<?php

/*
Plugin Name: WP Audio Book
Plugin URI: https://github.com/ehsantarar/wordpress-wp-audio-book
Description: Audio Book Plugin
Author: Ehsan Ullah
Version: 1.0
Author URI: http://facebook.com/ehsanullah53
*/

function wab_admin_theme_style() {
	if ( ! did_action( 'wp_enqueue_media' ) ) {
        wp_enqueue_media();
    }
    wp_enqueue_style('wab-admin-theme', plugins_url('wab-admin.css', __FILE__));
	wp_enqueue_script( 'wab-upload-script', plugins_url('wab-upload-script.js', __FILE__), array('jquery'), null, false );
}
function misha_image_uploader_field( $name, $value = '') {
    $image = ' button">Upload Audio';
    $image_size = 'full'; // it would be better to use thumbnail size here (150x150 or so)
    $display = 'none'; // display state ot the "Remove image" button
	
    if( $image_attributes = wp_get_attachment_image_src( $value, $image_size ) ) {

        // $image_attributes[0] - image URL
        // $image_attributes[1] - image width
        // $image_attributes[2] - image height

        $image = '"><img src="' . $image_attributes[0] . '" style="max-width:95%;display:block;" />';
        $display = 'inline-block';

    } 

    return '
    <div class="form-group">		
        <a href="#" class="wab_upload_audio_button' . $image . '</a>
        <input type="hidden" name="attachment[]"  value="' . $value . '" />
        <a href="#" class="wab_remove_audio_button" style="display:inline-block;display:' . $display . '">Remove Audio</a>
    </div>';
}

function wab_login_css() {
  echo '<link rel="stylesheet" type="text/css" href="' .plugins_url('wab-login.css  ', __FILE__). '">';
}
add_action('admin_enqueue_scripts', 'wab_admin_theme_style');
add_action('login_enqueue_scripts', 'wab_admin_theme_style');
add_action('login_head', 'wab_login_css');
function wab_admin_init() {	
	 add_meta_box( 'gpminvoice-group', 'Audio Book Chapters', 'repeatable_audio_meta_box_display', 'audio_book', 'normal', 'default');
    	
}
function wab_register_audio_book_post_type(){
	$labels = array(
		'name'               => 'Audio Books',
		'singular_name'      => 'Audio Book',
		'add_new'            => 'Add New',
		'add_new_item'       => 'Add New Audio Book',
		'edit_item'          => 'Edit Audio Book',
		'new_item'           => 'New Audio Book',
		'all_items'          => 'All Audio Books',
		'view_item'          => 'View Audio Book',
		'search_items'       => 'Search Audio Books',
		'not_found'          =>  'No Audio books found',
		'not_found_in_trash' => 'No Audio books found in Trash',
		'parent_item_colon'  => '',
		'menu_name'          => 'Audio Books'
	);
 
	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'audio-book' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor','thumbnail' )
	);
 
	register_post_type( 'audio_book', $args );
	global $wp_post_types;
  
  	//be sure to set this to the name of your post type!
  	$post_type_name = 'audio_book';
  	if( isset( $wp_post_types[ $post_type_name ] ) ) {
  		$wp_post_types[$post_type_name]->show_in_rest = true;
  		$wp_post_types[$post_type_name]->rest_base = $post_type_name;
  		$wp_post_types[$post_type_name]->rest_controller_class = 'JSON_API_Core_Controller';
  	}
}
add_action( 'init', 'wab_register_audio_book_post_type' );
add_action( 'admin_init', 'wab_admin_init');

function repeatable_audio_meta_box_display() {
    global $post;
    $gpminvoice_group = get_post_meta($post->ID, 'book_audio_chapter_group', true);
     wp_nonce_field( 'gpm_repeatable_meta_box_nonce', 'gpm_repeatable_meta_box_nonce' );
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function( $ ){
        $( '#add-row' ).on('click', function() {
            var row = $( '.empty-row.screen-reader-text' ).clone(true);
            row.removeClass( 'empty-row screen-reader-text' );
            row.insertBefore( '#repeatable-fieldset-one tbody>tr:last' );
            return false;
        });

        $( '.remove-row' ).on('click', function() {
            $(this).parents('tr').remove();
            return false;
        });
    });
  </script>
  <table id="repeatable-fieldset-one" class="wab-repeatable-fieldset" width="100%">
  <tbody>
    <?php
     if ( $gpminvoice_group ) :
      foreach ( $gpminvoice_group as $field ) { 
    ?>
    <tr> 
      <td width="100%">
		<div class="fields-wrapper">
			<div class="form-group">
				<label>Chapter Title</label>
				<input type="text" class="form-control"  placeholder="Title" name="chapter_title[]" value="<?php if($field['chapter_title'] != '') echo esc_attr( $field['chapter_title'] ); ?>" />
			</div>
			<div class="form-group">
			<label>Description</label>
			  <textarea placeholder="Description" cols="55" rows="5" name="chapter_description[]"> <?php if ($field['chapter_description'] != '') echo esc_attr( $field['chapter_description'] ); ?> </textarea>
			</div>
			<?php if(!empty($field['audio'])): ?>
			<div class="form-group">
				<a href="#" class="wab_upload_audio_button">
				<label>Audio File</label>
				<input type="text" name="audio[]" value="<?=esc_attr( $field['audio']); ?>" >
				</a>
				<input type="hidden" name="attachment[]" value="<?php if($field['attachment'] != '') echo esc_attr( $field['attachment'] ); ?>">
				<a href="#" class="wab_remove_audio_button" style="">Remove Audio</a>
			</div>
			<?php else: ?>
			<div class="form-group">		
				<a href="#" class="wab_upload_audio_button button">Upload Audio</a>
				<input type="hidden" name="attachment[]" value="">
				<a href="#" class="wab_remove_audio_button" style="display:inline-block;display:none">Remove Audio</a>
			</div>
			<?php endif; ?>
			
		</div>
		<div class="button-wrapper">
			<a class="button remove-row" href="#1">Remove</a>
		</div>  
	  </td>     
    </tr>
    <?php
    }
    else :
    // show a blank one
    ?>
    <tr>
      <td> 
		<div class="fields-wrapper">
			<div class="form-group">
				<label>Chapter Title</label>
				<input type="text" placeholder="Title" title="Title" name="chapter_title[]" class="form-control" />
			</div>
			<div class="form-group">
			<label>Description</label>
			  <textarea  placeholder="Description" name="chapter_description[]" class="form-control">  </textarea>
			</div>
			<?php
				 $meta_key = 'second_featured_img';
			echo misha_image_uploader_field( $meta_key, get_post_meta($post->ID, $meta_key, true));
			?>
		</div>
		<div class="button-wrapper">
			<a class="button  cmb-remove-row-button button-disabled" href="#">Remove</a>
		</div>
      </td>    
    </tr>
    <?php endif; ?>

    <!-- empty hidden one for jQuery -->
    <tr class="empty-row screen-reader-text">     
      <td>
		<div class="fields-wrapper">
			<div class="form-group">
				<label>Chapter Title</label>
				<input type="text" placeholder="Title" title="Title" name="chapter_title[]" class="form-control" />
			</div>
			<div class="form-group">
			<label>Description</label>
			  <textarea  placeholder="Description" name="chapter_description[]" class="form-control">  </textarea>
			</div>			
			<?php
				 $meta_key = 'second_featured_img';
				echo misha_image_uploader_field( $meta_key, get_post_meta($post->ID, $meta_key, true));
			?>
		</div>
		<div class="button-wrapper">
			<a class="button remove-row" href="#">Remove</a>
		</div>
      </td>
     
    </tr>
  </tbody>
</table>
<p><a id="add-row" class="button" href="#">Add another</a></p>
 <?php
}
add_action('save_post', 'custom_repeatable_meta_box_save');
function custom_repeatable_meta_box_save($post_id) {
    if ( ! isset( $_POST['gpm_repeatable_meta_box_nonce'] ) ||
    ! wp_verify_nonce( $_POST['gpm_repeatable_meta_box_nonce'], 'gpm_repeatable_meta_box_nonce' ) )
        return;


    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    if (!current_user_can('edit_post', $post_id))
        return;

    $old = get_post_meta($post_id, 'book_audio_chapter_group', true);
    $new = array();
    $invoiceItems = $_POST['chapter_title'];
    $prices = $_POST['chapter_description'];
	$audios=$_POST['audio'];
	$attachments=$_POST['attachment'];
     $count = count( $invoiceItems );
     for ( $i = 0; $i < $count; $i++ ) {
        if ( $invoiceItems[$i] != '' ) :
            $new[$i]['chapter_title'] = stripslashes( strip_tags( trim($invoiceItems[$i]) ) );
             $new[$i]['chapter_description'] = stripslashes( trim($prices[$i]) );
			 $new[$i]['audio'] = stripslashes( trim($audios[$i]) );
			 $new[$i]['attachment'] = stripslashes( $attachments[$i] );
        endif;
    }
    if ( !empty( $new ) && $new != $old )
        update_post_meta( $post_id, 'book_audio_chapter_group', $new );
    elseif ( empty($new) && $old )
        delete_post_meta( $post_id, 'book_audio_chapter_group', $old );


}



function wab_menu_page_removing() {	
  
}
add_action( 'admin_menu', 'wab_menu_page_removing',999 );
?>