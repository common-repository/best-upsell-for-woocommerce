<?php
/**
 * @package Bestupsell
 */

add_action('rest_api_init', function() {
    register_rest_route('wc/v3', 'image-upload', array(
        'methods' => WP_REST_SERVER::CREATABLE,
        'callback' => 'image_upload_url',
        'args' => array(),
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ));
});

function image_upload_url(WP_REST_Request $request){
        $image_all_data = json_decode($request->get_body());
		$file_tmp_name = base64_decode($image_all_data->image_tmp);
        $image_all_data = $image_all_data->image_data;
        $file_name = $image_all_data->image->name;
        $upload_dir = wp_upload_dir();
        $image_data = $file_tmp_name;
        $filename = basename( $file_name );
        $filetype = wp_check_filetype($file_name);
        $filename = time().'.'.$filetype['ext'];

        if ( wp_mkdir_p( $upload_dir['path'] ) ) {
            $file = $upload_dir['path'] . '/' . $filename;
        }
        else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once (ABSPATH . '/wp-admin/includes/file.php');
            WP_Filesystem();
        }
        if(!$wp_filesystem->put_contents( $file, $image_data, 0644) ) {
            return __('Failed to create css file');
        }
        $wp_filetype = wp_check_filetype( $filename, null );
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name( $filename ),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment( $attachment, $file );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        $image_url = wp_get_attachment_url( $attach_id );
        $response = array('image'=>$image_url,'media_id'=>$attach_id);
        return new WP_REST_Response($response, 200);
}
