<?php
class recstory_post_metabox{

    function admin_init()
    {
		/* List all post types */
		$post_types=get_post_types('','names'); 
		
		/* Eliminate certain post types from the list: mediapage, attachment, revision, nav_menu_item - (compatible up to WordPress version 3.2)  */
		foreach( $post_types as $key => $value ) {
	
			if( $value == 'mediapage' || $value == 'attachment' || $value == 'revision' || $value == 'nav_menu_item' ) {
		
				unset( $post_types[$key] );
		
			}
		}
	
		/*  */
		$screens = apply_filters('recstory_post_metabox_screens', $post_types );
        
		foreach($screens as $screen)
        {
			add_meta_box('recstory', 'Reccomended Stories for WordPress', array($this, 'post_metabox'), $screen, 'side', 'default'  );
        }
			add_action('save_post', array($this, 'save_post') );
        
			add_filter('default_hidden_meta_boxes', array($this,  'default_hidden_meta_boxes' )  );
    }

    function default_hidden_meta_boxes($hidden)
    {
        $hidden[] = 'recstory';
        
		return $hidden;
    }

    function post_metabox(){
        global $post_id;

        if ( is_null($post_id) )
        		$checked = '';
        else
        {
            $custom_fields = get_post_custom($post_id);
            $checked = ( isset ($custom_fields['recstory_exclude'])   ) ? 'checked="checked"' : '' ;
        }

        wp_nonce_field('recstory_postmetabox_nonce', 'recstory_postmetabox_nonce');
        
		echo '<label for="recstory_show_option">';
        
		_e("Remove recommended stories box on this page:", 'recstory' );
        
		echo '</label> ';
        
		echo '<input type="checkbox" id="recstory_show_option" name="recstory_show_option" value="1" '.$checked.'>';
    }

    function save_post($post_id)
    {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
            return;

        if ( ! isset($_POST['recstory_postmetabox_nonce'] ) ||  !wp_verify_nonce( $_POST['recstory_postmetabox_nonce'], 'recstory_postmetabox_nonce' ) ) 
            return;

        if ( ! isset($_POST['recstory_show_option']) )
        {
            delete_post_meta($post_id, 'recstory_exclude');
        }
        else
        {
            $custom_fields = get_post_custom($post_id);
            if (! isset ($custom_fields['recstory_exclude'][0])  )
            {
                add_post_meta($post_id, 'recstory_exclude', 'true');
            }
            else
            {
                update_post_meta($post_id, 'recstory_exclude', 'true' , $custom_fields['recstory_exclude'][0]  ); 
            }
        }

    }

}

$recstory_post_metabox = new recstory_post_metabox;
add_action('admin_init', array($recstory_post_metabox, 'admin_init'));

