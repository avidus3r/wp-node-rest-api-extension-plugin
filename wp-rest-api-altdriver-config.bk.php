<?php

/*
 **
    Response manipulation
 **
*/


/*
 ** Add postmeta to api response
*/

function altd_rest_api_init(){
    include_once(dirname( __FILE__ ) . '/class-wp-rest-api-altdriver-custom-endpoints.php');
}

add_action( 'rest_api_init', 'altd_rest_api_init' );


function alt_api_register_get_votes($args) {
    register_api_field( 'post',
        'votes',
        array(
            'get_callback'    => 'alt_api_get_votes',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}

add_action( 'rest_api_init', 'alt_api_register_get_votes' );

function alt_api_get_votes($object, $field_name, $request){
    $votes_up = get_post_meta($object['id'], 'votes_up', true);
    $votes_down = get_post_meta($object['id'], 'votes_down', true);
    $total_votes = get_post_meta($object['id'], 'total_votes', true);
    $votes_tally = get_post_meta($object['id'], 'votes_tally', true);
    $votes = array(
        'votes_up'      => $votes_up,
        'votes_down'    => $votes_down,
        'total_votes'   => $total_votes,
        'votes_tally'   => $votes_tally
    );
    return $votes;
}

function alt_api_register_get_comment_count($args) {
    register_api_field( 'post',
        'comment_count',
        array(
            'get_callback'    => 'alt_api_get_comment_count',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}
add_action( 'rest_api_init', 'alt_api_register_get_comment_count' );

function alt_api_get_comment_count($object, $field_name, $request){
    return wp_count_comments($object['id']);
}


function alt_api_register_get_postmeta($args) {
    register_api_field( 'post',
        'postmeta',
        array(
            'get_callback'    => 'alt_api_get_postmeta',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}
add_action( 'rest_api_init', 'alt_api_register_get_postmeta' );

function alt_api_get_postmeta($object, $field_name, $request){
    return get_post_meta($object['id']);
}


function alt_api_register_is_active_campaign($args) {
    register_api_field( 'post',
        'sponsor',
        array(
            'get_callback'    => 'alt_api_is_active_campaign',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}
add_action( 'rest_api_init', 'alt_api_register_is_active_campaign' );

function alt_api_is_active_campaign($object, $field_name, $request){
    $sponsor = null;
    $campaign_id = get_post_meta($object['id'], '_altdsc_campaign_id', true);
    if($campaign_id){
        $start_date = date("Y-m-d", get_post_meta($campaign_id, '_altdsc_campaign_start', true));
        $end_date = date("Y-m-d", get_post_meta($campaign_id, '_altdsc_campaign_end', true));
        $today = date("Y-m-d");
        if(strtotime($end_date) >= strtotime($today)){
            
        }
        $campaign = get_post($campaign_id);
        $campaign_post = get_post($object->id);
        $campaign_item = array(
            'name'      => $campaign_post->post_name,
            'title'     => $campaign_post->post_title,
            'notes'    => get_post_meta($campaign_id, '_altdsc_campaign_notes', true),
            'byline'    => get_post_meta($campaign_id, '_altdsc_campaign_byline', true)
        );

        $sponsor = get_post($campaign->post_parent);
        $sponsor_id = $sponsor->ID;

        $sponsor_name = $sponsor->post_name;
        $sponsor_title = $sponsor->post_title;
        $sponsor_avatar = get_post(get_post_meta($sponsor_id, '_altdsc_sponsor_avatar', true))->guid;
        $sponsor_permalink = get_the_permalink($sponsor_id);
        $sponsor_featured_image = wp_get_attachment_image_src(get_post_thumbnail_id( $sponsor_id ), 'full');
        
        $sponsor_urls = array(
            'main' => get_post_meta($sponsor_id, '_altdsc_sponsor_urls[main]', true),
            'facebook' => get_post_meta($sponsor_id, '_altdsc_sponsor_urls[facebook]', true),
            'twitter' => get_post_meta($sponsor_id, '_altdsc_sponsor_urls[twitter]', true)
        );
        $sponsor_color = get_post_meta($sponsor_id, '_altdsc_sponsor_color', true);
        $sponsor = array(
            'name' => $sponsor_name,
            'title' => $sponsor_title,
            'avatar' => $sponsor_avatar,
            'link' => $sponsor_permalink,
            'color' => $sponsor_color, 
            'urls' => $sponsor_urls,
            'campaign' => $campaign_item,
            'featured_image' => $sponsor_featured_image
        );
    
    }
    return $sponsor;
}


/*
 ** Add category terms to api response
*/
add_action( 'rest_api_init', 'alt_api_register_get_category' );
function alt_api_register_get_category() {
    register_api_field( 'post',
        'category',
        array(
            'get_callback'    => 'alt_api_get_category',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}

function alt_api_get_category($object, $field_name, $request){
    $category = wp_get_object_terms( $object['id'], 'category', array('fields' => 'all'));
    return $category;
}


/*
 ** Add featured image to api response
*/
add_action( 'rest_api_init', 'alt_api_register_get_featured_image' );
function alt_api_register_get_featured_image() {
    register_api_field( 'post',
        'featured_image_src',
        array(
            'get_callback'    => 'alt_api_get_featured_image',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}

function alt_api_get_featured_image($object, $field_name, $request){
    $cdn = "s3-us-west-2.amazonaws.com/assets.altdriver/uploads";
    $cdn = $_SERVER['HTTP_HOST'];
    $host = $_SERVER['HTTP_HOST'];
    
    $featured_image['original'] = str_replace($host, $cdn, wp_get_attachment_image_src(get_post_thumbnail_id( $object['id'] ), 'original'));
    $featured_image['full'] = str_replace($host, $cdn, wp_get_attachment_image_src(get_post_thumbnail_id( $object['id'] ), 'full'));
    $featured_image['large'] = str_replace($host, $cdn, wp_get_attachment_image_src(get_post_thumbnail_id( $object['id'] ), 'large'));
    $featured_image['medium'] = str_replace($host, $cdn, wp_get_attachment_image_src(get_post_thumbnail_id( $object['id'] ), 'medium'));
    $featured_image['original_wp'] = wp_get_attachment_image_src(get_post_thumbnail_id( $object['id'] ), 'original');
    $featured_image['full_wp'] = wp_get_attachment_image_src(get_post_thumbnail_id( $object['id'] ), 'full');
    $featured_image['large_wp'] = wp_get_attachment_image_src(get_post_thumbnail_id( $object['id'] ), 'large');
    $featured_image['medium_wp'] = wp_get_attachment_image_src(get_post_thumbnail_id( $object['id'] ), 'medium');
    return $featured_image;
}

/*
 ** Add author meta to api response
*/
add_action( 'rest_api_init', 'alt_api_register_get_author_meta' );
function alt_api_register_get_author_meta() {
    register_api_field( 'post',
        'author_meta',
        array(
            'get_callback'    => 'alt_api_get_author_meta',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}

function alt_api_get_author_meta($object, $field_name, $request){
    $author['name'] = get_the_author_meta( 'display_name', $object['author'] );
    return $author;
}



/*
 **
    Custom api endpoints
 **
*/
function alt_api_endpoint_feed( $data ) {
    $query_args = (array) $data->get_params();
    
    $args = array(
        'orderby'           => 'title',
        'order'             => 'DESC',
        'post__not_in'      => array($query_args['id']),
        'posts_per_page'    => 10,
        'meta_key'          => 'run_dates_0_channel',
        'meta_value'        => 'Facebook Main'
    );
    
    $posts = new WP_Query( $args );
    
    

    if ( empty( $posts ) ) {
        return null;
    }
    
    $response_posts = array();

    /*foreach ($posts as $post) {
        $response_post=[];
        
        foreach ($post as $key => $value) {
            $newKey = strtolower(str_replace("post_", "", $key));
            $response_post[$newKey] = $post;
        }
        $response_post->category = wp_get_object_terms( $post->ID, 'category', array('fields' => 'all') );
        
        $response_post->postmeta = get_post_meta($post->ID);
        array_push($response_posts, $response_post);
    }*/
    
    return $posts;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v2', '/feed-test/(?P<id>[\d]+)', array(
        'methods' => 'GET',
        'callback' => 'alt_api_endpoint_feed',
    ) );
} );


add_action( 'rest_api_init', function () {
    $altdriver_custom_routes_controller = new AltDriver_Custom_Routes_Controller('post');
    $altdriver_custom_routes_controller->register_routes();
} );

function alt_api_get_post_by_name( $data ) {
    $posts = get_posts( array(
        'name' => $data['post_slug'],
    ) );

    if ( empty( $posts ) ) {
        return null;
    }
    
    $response_posts = array();

    foreach ($posts as $post) {
        $response_post=[];
        
        foreach ($post as $key => $value) {
            $newKey = strtolower(str_replace("post_", "", $key));
            $response_post[$newKey] = $post;
        }
        $response_post->category = wp_get_object_terms( $post->ID, 'category', array('fields' => 'all') );
        
        $response_post->postmeta = get_post_meta($post->ID);
        array_push($response_posts, $response_post);
    }
    
    return $posts;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v2', '/posts/(?P<post_slug>[-\w]+)', array(
        'methods' => 'GET',
        'callback' => 'alt_api_get_post_by_name',
    ) );
} );

function alt_api_transition_post_status($new_status, $old_status, $post) {

    $postID = $post->ID;
    $feed_apps = array(2 => 'http://www.altdriver.com', 8 => 'http://driversenvy.com');
    if(isset($_POST['broadcast']['blogs']) && !empty($_POST['broadcast']['blogs'])){
        $broadcasted_to = $_POST['broadcast']['blogs'];
        foreach ($broadcasted_to as $blog_name => $blog_id) {
            $update_route = $feed_apps[$blog_id] . '/update/' . $postID;
            // $update_route
        }
    }

    if($_SERVER['HTTP_HOST'] == 'driversenvy.altmedia.com'){
        $url = 'http://driversenvy.com/update/' . $postID;
    }else{
        $url = "http://www.altdriver.com/update/" . $postID;
    }
    if( !class_exists( 'WP_Http' ) )
        include_once( ABSPATH . WPINC. '/class-http.php' );

    $body = array(
       'id' => $postID
    );
    $request = new WP_Http;
    $result = $request->request( $url );
}
add_action(  'transition_post_status', 'alt_api_transition_post_status',10,3);
