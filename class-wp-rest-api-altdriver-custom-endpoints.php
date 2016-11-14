<?php
/*
 *
 * TODO: break routes out into separate classes
 * 
*/
class AltDriver_Custom_Routes_Controller extends WP_REST_CONTROLLER{
    
    protected $post_type;

    public function __construct( $post_type ) {
        $this->post_type = $post_type;
    }

    public function register_routes(){
        $version = '2';
        $namespace = 'wp/v' . $version;
        $base = 'feed';

        $posts_args = array(
            'context'               => array(
                'default'           => 'view',
            ),
            'page'                  => array(
                'default'           => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page'              => array(
                'default'           => 10,
                'sanitize_callback' => 'absint',
            ),
            'meta_key'              => array(
                'default'           => 'run_dates_0_channel'
            ),
            'meta_value'            => array(
                'default'           => 'Facebook Main'
            ),
            'orderby'               => array(
                'default'           => 'date'
            ),
            'order'                 => array(
                'default'           => 'DESC'
            ),
            'post__not_in'          => array(
                'default'           => 0
            ),
            'post_status'           => array(
                'default'           => 'publish'
            )
        );

        foreach ( $this->get_allowed_query_vars() as $var ) {
            if ( ! isset( $posts_args[ $var ] ) ) {
                $posts_args[ $var ] = array();
            }
        }

        register_rest_route($namespace, '/' . $base . '/', array(
            array(
                'methods'       => WP_REST_Server::READABLE,
                'callback'      => array($this, 'get_items'),
                'permission_callback' => array( $this, 'check_read_permission' ),
                'args'            => $posts_args,
            ),
            /*array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_item' ),
                'permission_callback' => array( $this, 'check_create_permission' ),
                'args'            => $this->get_endpoint_args_for_item_schema( true ),
            ),*/
        ) );
        register_rest_route( $namespace, '/' . $base . '/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_item' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
                'args'            => array(
                    'context'          => array(
                        'default'      => 'view',
                    ),
                ),
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_item' ),
                'permission_callback' => array( $this, 'check_update_permission' ),
                'args'            => $this->get_endpoint_args_for_item_schema( false ),
            ),
            array(
                'methods'  => WP_REST_Server::DELETABLE,
                'callback' => array( $this, 'delete_item' ),
                'permission_callback' => array( $this, 'check_delete_permission' ),
                'args'     => array(
                    'force'    => array(
                        'default'      => false,
                    ),
                ),
            ),
        ) );

        register_rest_route( $namespace, '/' . $base . '/vote/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_votes' )
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'post_vote' )
            )
        ) );

        register_rest_route( $namespace, '/' . $base . '/authors', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_authors' )
            )
        ) );

        register_rest_route( $namespace, '/' . $base . '/authors/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_author_posts' ),
                'args'            => array(
                    'context'               => array(
                        'default'           => 'view',
                    ),
                    'date_type'                  => array(
                        'default'           => '',
                    ),
                    'date_offset'              => array(
                        'default'           => '',
                    )
                )
            )
        ) );

        register_rest_route( $namespace, '/' . $base . '/schema', array(
            'methods'         => WP_REST_Server::READABLE,
            'callback'        => array( $this, 'get_public_item_schema' ),
        ) );
        register_rest_route( $namespace, '/' . $base . '/menu', array(
            'methods'       => WP_REST_Server::READABLE,
            'callback'      => array( $this, 'get_menu' ),
            'args'          => array(
                'name' => array(
                    'default' => 'Main Menu'
                )    
            )
        ) );

        /**
        * site routes
        */
        register_rest_route( 'alt/driversenvy', '/posts', array(
            'methods'         => WP_REST_Server::READABLE,
            'callback'        => array( $this, 'driversenvy_get_posts' ),
            'args'            => array(
                'category_name'              => array(
                    'default'           => 'drivers-envy'
                ),
                'context'               => array(
                    'default'           => 'view',
                ),
                'page'                  => array(
                    'default'           => 1,
                    'sanitize_callback' => 'absint',
                ),
                'per_page'              => array(
                    'default'           => 10,
                    'sanitize_callback' => 'absint',
                )
            )
        ) );

        register_rest_route( $namespace, '/sponsors', array(
            'methods'         => WP_REST_Server::READABLE,
            'callback'        => array( $this, 'get_items' ),
            'args'            => array(
                'context'               => array(
                    'default'           => 'view',
                ),
                'page'                  => array(
                    'default'           => 1,
                    'sanitize_callback' => 'absint',
                ),
                'per_page'              => array(
                    'default'           => 10,
                    'sanitize_callback' => 'absint',
                ),
                'post_type'             => array(
                    'default'           => 'altdsc_sponsor'
                )
            )
        ) );

        register_rest_route( $namespace, '/campaigns', array(
            'methods'         => WP_REST_Server::READABLE,
            'callback'        => array( $this, 'get_items' ),
            'args'            => array(
                'context'               => array(
                    'default'           => 'view',
                ),
                'page'                  => array(
                    'default'           => 1,
                    'sanitize_callback' => 'absint',
                ),
                'per_page'              => array(
                    'default'           => 10,
                    'sanitize_callback' => 'absint',
                ),
                'post_type'             => array(
                    'default'           => 'altdsc-campaign'
                )
            )
        ) );

        register_rest_route( $namespace, '/campaigns/(?P<id>[\d]+)', array(
            'methods'         => WP_REST_Server::READABLE,
            'callback'        => array( $this, 'get_items' ),
            'args'            => array(
                'context'               => array(
                    'default'           => 'view',
                ),
                'page'                  => array(
                    'default'           => 1,
                    'sanitize_callback' => 'absint',
                ),
                'per_page'              => array(
                    'default'           => 10,
                    'sanitize_callback' => 'absint',
                ),
                'post_type'             => array(
                    'default'           => 'altdsc-campaign'
                )
            )
        ) );
    }

    public function get_author_posts($request){
        $args = (array) $request->get_params();
        $date_type = $args['date_type'];
        $date_offset = $args['date_offset'];
        $author_id = $args['id'];
        $today = date('d-m-Y');
        $since  = date('d-m-Y', strtotime("$today -$date_offset days"));
        
        switch($date_type){
            case "day":
                

                $post_args = array(
                    'author' => $author_id, 
                    'post_type' => 'post', 
                    'post_status' => array('publish'), 
                    'date_query' => array( 
                        array(
                            'after'=> $since, 
                            'inclusive' => true
                            )
                        ),
                    'posts_per_page'=>-1
                );
                $posts_query = new WP_Query();
                $query_result = $posts_query->query( $post_args );
                $posts = array();
                foreach ($query_result as $result) {
                    $feed_app_url = get_blog_option(get_current_blog_id(),'Feed App Url');
                    $siteurl = get_bloginfo('siteurl');
                    $permalink = get_permalink($result->ID);
                    $permalink = str_replace($siteurl, $feed_app_url, $permalink);
                    $result->permalink = $permalink;
                    array_push($posts, $result);
                }
            break;
        }
        return $posts;
    }

    public function get_authors(){
        $users = get_users();
        $us = array();
        $disallowed = array('user_pass', 'user_activation_key');
        foreach ($users as $user) {
            $nonprops = array('caps', 'cap_key', 'roles', 'allcaps', 'filter');
            foreach ($user as $prop => $val) {
                if(in_array($prop, $nonprops)){
                   unset($user->$prop);
                }
            }
            foreach ($user->data as $key => $value) {
                if(in_array($key, $disallowed)){
                   unset($user->data->$key);
                }
            }
        }
        return $users;
    }

    /**
     * Get a collection of items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_items( $request ) {
        $args = (array) $request->get_params();
        $campaign = false;
        $sponsor = false;
        $sponsor_id = null;
        if($args['post_type'] == 'altdsc-campaign'){
            if(isset($args['id']) && !empty($args['id'])){
                $args['post_parent'] = $args['id'];
                $campaign = true;
                $sponsor_id = $args['id'];
            }
        }

        if($args['post_type'] == 'altdsc_sponsor'){
            $sponsor = true;
            $sponsor_id = $args['id'];
        }

        if(!$args['post_type']) $args['post_type'] = $this->post_type;
        $args['paged'] = $args['page'];
        $args['posts_per_page'] = $args['per_page'];
        unset( $args['page'] );
        
        /**
         * Alter the query arguments for a request.
         *
         * This allows you to set extra arguments or defaults for a post
         * collection request.
         *
         * @param array $args Map of query var to query value.
         * @param WP_REST_Request $request Full details about the request.
         */
        $args = apply_filters( 'rest_post_query', $args, $request );
        
        //$query_args = $this->prepare_items_query( $args );
        
        $args['post__not_in'] = array($args['post__not_in']);
        
        //$args['meta_key'] = 'post_views_count';
        //$args['orderby'] = 'date';
        //$args['order'] = 'DESC';
        unset($args['id']);
        $posts_query = new WP_Query();
        $query_result = $posts_query->query( $args );

        $posts = array();
        foreach ( $query_result as $post ) {
            $post->campaign_active = null;
            $post->sponsor = null;
            $post->campaigns = null;
            $post->campaign_items = null;

            if ( ! $this->check_read_permission( $post ) ) {
                continue;
            }
            if($sponsor){
                $campaign_args = array('post_type' => 'altdsc-campaign', 'post_parent' => $post->ID );
                $campaigns = get_children($campaign_args);
                
                $sponsor = $post;
                $sponsor_name = $sponsor->post_name;
                $sponsor_title = $sponsor->post_title;
                $sponsor_avatar = get_post(get_post_meta($sponsor->ID, '_altdsc_sponsor_avatar', true))->guid;
                $sponsor_permalink = get_the_permalink($sponsor->ID);
                $sponsor_featured_image = wp_get_attachment_image_src(get_post_thumbnail_id( $sponsor->ID ), 'full');
                
                $sponsor_urls = array(
                    'main' => get_post_meta($sponsor->ID, '_altdsc_sponsor_urls[main]', true),
                    'facebook' => get_post_meta($sponsor->ID, '_altdsc_sponsor_urls[facebook]', true),
                    'twitter' => get_post_meta($sponsor->ID, '_altdsc_sponsor_urls[twitter]', true)
                );
                $sponsor_color = get_post_meta($sponsor->ID, '_altdsc_sponsor_color', true);        
                foreach($campaigns as $sponsor_campaign){
                    
    
                    $start_date = date("Y-m-d", get_post_meta($sponsor_campaign->ID, '_altdsc_campaign_start', true));
                    $end_date = date("Y-m-d", get_post_meta($sponsor_campaign->ID, '_altdsc_campaign_end', true));
                    $today = date("Y-m-d");
                    $post->campaign_active = 'null';
                    
                    if(strtotime($end_date) >= strtotime($today)){
                        $post->campaign_active = 'true';
                    }
                    
                    $campaigns_query = new WP_Query();
                    $campaign_id = $sponsor_campaign->ID;
                    
                    $campaign_item = array(
                        'name'      => $sponsor_campaign->post_name,
                        'title'     => $sponsor_campaign->post_title,
                        'notes'    => get_post_meta($sponsor_campaign->ID, '_altdsc_campaign_notes', true),
                        'byline'    => get_post_meta($sponsor_campaign->ID, '_altdsc_campaign_byline', true)
                    );

                    $campaigns_query_args = array(
                        'meta_key' => '_altdsc_campaign_id',
                        'meta_value' => $sponsor_campaign->ID
                    );
                    $campaigns_result = $campaigns_query->query( $campaigns_query_args );
                    $campaign_items = array();
                    foreach ( $campaigns_result as $campaigns_post ) {
                        $campaigns_post->campaign_active = null;
                        if ( ! $this->check_read_permission( $campaigns_post ) ) {
                            continue;
                        }

                        $campaign_item_data = $this->prepare_item_for_response( $campaigns_post, $request );
                        $campaign_items[] = $this->prepare_response_for_collection( $campaign_item_data );
                    }
                    $sponsor_campaign->campaign_items = $campaign_items;
                    $campaign_data = $this->prepare_item_for_response( $sponsor_campaign, $request );
                    $post->campaigns = $this->prepare_response_for_collection( $campaign_data );   
                }
            }
            elseif($campaign){
                $sponsor = get_post($sponsor_id);
                $sponsor_name = $sponsor->post_name;
                $sponsor_title = $sponsor->post_title;
                $sponsor_avatar = get_post(get_post_meta($sponsor_id, '_altdsc_sponsor_avatar', true))->guid;
                $sponsor_permalink = get_the_permalink($sponsor->ID);
                $sponsor_featured_image = wp_get_attachment_image_src(get_post_thumbnail_id( $sponsor->ID ), 'full');
                
                $sponsor_urls = array(
                    'main' => get_post_meta($sponsor_id, '_altdsc_sponsor_urls[main]', true),
                    'facebook' => get_post_meta($sponsor_id, '_altdsc_sponsor_urls[facebook]', true),
                    'twitter' => get_post_meta($sponsor_id, '_altdsc_sponsor_urls[twitter]', true)
                );
                $sponsor_color = get_post_meta($sponsor_id, '_altdsc_sponsor_color', true);
                
                $start_date = date("Y-m-d", get_post_meta($post->ID, '_altdsc_campaign_start', true));
                $end_date = date("Y-m-d", get_post_meta($post->ID, '_altdsc_campaign_end', true));
                $today = date("Y-m-d");

                if(strtotime($end_date) >= strtotime($today)){
                    $post->campaign_active = 'true';
                    $posts_query = new WP_Query();
                    $campaign_id = $post->ID;
                    
                    $campaign_item = array(
                        'name'      => $post->post_name,
                        'title'     => $post->post_title,
                        'notes'    => get_post_meta($campaign_id, '_altdsc_campaign_notes', true),
                        'byline'    => get_post_meta($campaign_id, '_altdsc_campaign_byline', true)
                    );

                    $query_result = $posts_query->query( 'meta_key=_altdsc_campaign_id&meta_value='.$campaign_id );

                    foreach ( $query_result as $post ) {
                        $post->campaign_active = null;
                        if ( ! $this->check_read_permission( $post ) ) {
                            continue;
                        }

                        $post->sponsor = array(
                            'name' => $sponsor_name,
                            'title' => $sponsor_title,
                            'avatar' => $sponsor_avatar,
                            'link' => $sponsor_permalink,
                            'color' => $sponsor_color, 
                            'urls' => $sponsor_urls,
                            'campaign' => $campaign_item,
                            'featured_image' => $sponsor_featured_image
                        );

                        $data = $this->prepare_item_for_response( $post, $request );
                        $posts[] = $this->prepare_response_for_collection( $data );
                    }
                }else{
                    $post->campaign_active = 'false';
                    $posts_query = new WP_Query();
                    $campaign_id = $post->ID;
                    
                    $campaign_item = array(
                        'name'      => $post->post_name,
                        'title'     => $post->post_title,
                        'notes'    => get_post_meta($campaign_id, '_altdsc_campaign_notes', true),
                        'byline'    => get_post_meta($campaign_id, '_altdsc_campaign_byline', true)
                    );

                    $query_result = $posts_query->query( 'meta_key=_altdsc_campaign_id&meta_value='.$post->ID );
                    foreach ( $query_result as $post ) {
                        $post->campaign_active = null;
                        if ( ! $this->check_read_permission( $post ) ) {
                            continue;
                        }
                        $post->sponsor = array(
                            'name' => $sponsor_name,
                            'title' => $sponsor_title,
                            'avatar' => $sponsor_avatar,
                            'link' => $sponsor_permalink,
                            'color' => $sponsor_color, 
                            'urls' => $sponsor_urls,
                            'campaign' => $campaign_item,
                            'featured_image' => $sponsor_featured_image
                        );
                        $data = $this->prepare_item_for_response( $post, $request );
                        $posts[] = $this->prepare_response_for_collection( $data );
                    }
                }
            }elseif($post->post_type == 'altdsc-campaign'){
                $start_date = date("Y-m-d", get_post_meta($post->ID, '_altdsc_campaign_start', true));
                $end_date = date("Y-m-d", get_post_meta($post->ID, '_altdsc_campaign_end', true));
                $today = date("Y-m-d");
                if(strtotime($end_date) >= strtotime($today)){
                    $post->campaign_active = 'true';
                }
                if($post->post_parent){
                    $post->sponsor = get_post_meta($post->post_parent);
                }
            }
                

            $data = $this->prepare_item_for_response( $post, $request );
            $posts[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $posts );
        $count_query = new WP_Query();
        unset( $query_args['paged'] );
        $query_result = $count_query->query( $query_args );
        $total_posts = $count_query->found_posts;
        $response->header( 'X-WP-Total', (int) $total_posts );
        $response->header( 'Access-Control-Allow-Origin', '*' );
        $max_pages = ceil( $total_posts / $request['per_page'] );
        $response->header( 'X-WP-TotalPages', (int) $max_pages );

        $response->total_count = $total_posts;

        $base = add_query_arg( $request->get_query_params(), rest_url( $namespace, '/' . $base . '/' . $this->get_post_type_base( $this->post_type ) ) );
        if ( $request['page'] > 1 ) {
            $prev_page = $request['page'] - 1;
            if ( $prev_page > $max_pages ) {
                $prev_page = $max_pages;
            }
            $prev_link = add_query_arg( 'page', $prev_page, $base );
            $response->link_header( 'prev', $prev_link );
        }
        if ( $max_pages > $request['page'] ) {
            $next_page = $request['page'] + 1;
            $next_link = add_query_arg( 'page', $next_page, $base );
            $response->link_header( 'next', $next_link );
        }

        return $response;
    }

    /**
     * Get one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_item( $request ) {
        $id = (int) $request['id'];
        $post = get_post( $id );

        if ( empty( $id ) || empty( $post->ID ) || $this->post_type !== $post->post_type ) {
            return new WP_Error( 'rest_post_invalid_id', __( 'Invalid post ID.' ), array( 'status' => 404 ) );
        }

        $data = $this->prepare_item_for_response( $post, $request );
        $response = rest_ensure_response( $data );

        $response->link_header( 'alternate',  get_permalink( $id ), array( 'type' => 'text/html' ) );

        return $response;
    }

    /**
     * Create one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Request
     */
    public function create_item( $request ) {

        if ( ! empty( $request['id'] ) ) {
            return new WP_Error( 'rest_post_exists', __( 'Cannot create existing post.' ), array( 'status' => 400 ) );
        }

        $post = $this->prepare_item_for_database( $request );
        if ( is_wp_error( $post ) ) {
            return $post;
        }

        $post->post_type = $this->post_type;
        $post_id = wp_insert_post( $post, true );

        if ( is_wp_error( $post_id ) ) {

            if ( in_array( $post_id->get_error_code(), array( 'db_insert_error' ) ) ) {
                $post_id->add_data( array( 'status' => 500 ) );
            } else {
                $post_id->add_data( array( 'status' => 400 ) );
            }
            return $post_id;
        }
        $post->ID = $post_id;

        $schema = $this->get_item_schema();

        if ( ! empty( $schema['properties']['sticky'] ) ) {
            if ( ! empty( $request['sticky'] ) ) {
                stick_post( $post_id );
            } else {
                unstick_post( $post_id );
            }
        }

        if ( ! empty( $schema['properties']['featured_image'] ) && isset( $request['featured_image'] ) ) {
            $this->handle_featured_image( $request['featured_image'], $post->ID );
        }

        if ( ! empty( $schema['properties']['format'] ) && ! empty( $request['format'] ) ) {
            set_post_format( $post, $request['format'] );
        }

        if ( ! empty( $schema['properties']['template'] ) && isset( $request['template'] ) ) {
            $this->handle_template( $request['template'], $post->ID );
        }

        $this->update_additional_fields_for_object( get_post( $post_id ), $request );

        /**
         * @TODO: Enable rest_insert_post() action after
         * Media Controller has been migrated to new style.
         *
         * do_action( 'rest_insert_post', $post, $request, true );
         */

        $response = $this->get_item( array(
            'id'      => $post_id,
            'context' => 'edit',
        ) );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( '/wp/v2/' . $this->get_post_type_base( $post->post_type ) . '/' . $post_id ) );

        return $response;


    }

    /**
     * Update one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Request
     */
    public function update_item( $request ) {
        $id = (int) $request['id'];
        $post = get_post( $id );

        if ( ! $post ) {
            return new WP_Error( 'rest_post_invalid_id', __( 'Post ID is invalid.' ), array( 'status' => 400 ) );
        }

        $post = $this->prepare_item_for_database( $request );
        if ( is_wp_error( $post ) ) {
            return $post;
        }

        $post_id = wp_update_post( $post, true );
        if ( is_wp_error( $post_id ) ) {
            if ( in_array( $post_id->get_error_code(), array( 'db_update_error' ) ) ) {
                $post_id->add_data( array( 'status' => 500 ) );
            } else {
                $post_id->add_data( array( 'status' => 400 ) );
            }
            return $post_id;
        }

        $schema = $this->get_item_schema();

        if ( ! empty( $schema['properties']['format'] ) && ! empty( $request['format'] ) ) {
            set_post_format( $post, $request['format'] );
        }

        if ( ! empty( $schema['properties']['featured_image'] ) && isset( $request['featured_image'] ) ) {
            $this->handle_featured_image( $request['featured_image'], $post_id );
        }

        if ( ! empty( $schema['properties']['sticky'] ) && isset( $request['sticky'] ) ) {
            if ( ! empty( $request['sticky'] ) ) {
                stick_post( $post_id );
            } else {
                unstick_post( $post_id );
            }
        }

        if ( ! empty( $schema['properties']['template'] ) && isset( $request['template'] ) ) {
            $this->handle_template( $request['template'], $post->ID );
        }

        $this->update_additional_fields_for_object( get_post( $post_id ), $request );

        /**
         * @TODO: Enable rest_insert_post() action after
         * Media Controller has been migrated to new style.
         *
         * do_action( 'rest_insert_post', $post, $request );
         */

        return $this->get_item( array(
            'id'      => $post_id,
            'context' => 'edit',
        ));

    }

    /**
     * Delete one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Request
     */
    public function delete_item( $request ) {
        $id = (int) $request['id'];
        $force = (bool) $request['force'];

        $post = get_post( $id );

        if ( empty( $id ) || empty( $post->ID ) || $this->post_type !== $post->post_type ) {
            return new WP_Error( 'rest_post_invalid_id', __( 'Invalid post ID.' ), array( 'status' => 404 ) );
        }

        $supports_trash = ( EMPTY_TRASH_DAYS > 0 );
        if ( $post->post_type === 'attachment' ) {
            $supports_trash = $supports_trash && MEDIA_TRASH;
        }

        /**
         * Filter whether the post type supports trashing.
         *
         * @param boolean $supports_trash Does the post type support trashing?
         * @param WP_Post $post Post we're attempting to trash.
         */
        $supports_trash = apply_filters( 'rest_post_type_trashable', $supports_trash, $post );

        if ( ! $this->check_delete_permission( $post ) ) {
            return new WP_Error( 'rest_user_cannot_delete_post', __( 'Sorry, you are not allowed to delete this post.' ), array( 'status' => 401 ) );
        }

        $request = new WP_REST_Request( 'GET', '/wp/v2/' . $this->get_post_type_base( $this->post_type ) . '/' . $post->ID );
        $request->set_param( 'context', 'edit' );
        $response = rest_do_request( $request );

        // If we're forcing, then delete permanently
        if ( $force ) {
            $result = wp_delete_post( $id, true );
        } else {
            // If we don't support trashing for this type, error out
            if ( ! $supports_trash ) {
                return new WP_Error( 'rest_trash_not_supported', __( 'The post does not support trashing.' ), array( 'status' => 501 ) );
            }

            // Otherwise, only trash if we haven't already
            if ( 'trash' === $post->post_status ) {
                return new WP_Error( 'rest_already_deleted', __( 'The post has already been deleted.' ), array( 'status' => 410 ) );
            }

            // (Note that internally this falls through to `wp_delete_post` if
            // the trash is disabled.)
            $result = wp_trash_post( $id );
        }

        if ( ! $result ) {
            return new WP_Error( 'rest_cannot_delete', __( 'The post cannot be deleted.' ), array( 'status' => 500 ) );
        }

        return $response;
    }

    public function get_votes($request){
        $args = (array) $request->get_params();
        if(empty($args['id'])) return "Error: missing ID";
    }

    public function post_vote($request){
        $args = (array) $request->get_params();
        
        if(empty($args['vote'])) return "Error: votes param not received";

        if(!empty($args['poll_vote'])){
            $poll_vote = $args['poll_vote'];
            $votes = get_post_meta($args['id'], "poll_votes", true);
            if(empty($votes)){
                $poll_vote_meta = array(
                    "carwash" => 0,
                    "snake" => 0,
                    "corvettes" => 0,
                    "roadrage" => 0,
                    "motowheelie" => 0
                );
                $poll_vote_meta[$poll_vote]++;
                add_post_meta($args['id'], "poll_votes", json_encode($poll_vote_meta), true);
            }else{
                $prev_poll_vote_meta = get_post_meta($args['id'], "poll_votes", true);
                $poll_vote_meta = json_decode($prev_poll_vote_meta, true);
                $poll_vote_meta[$poll_vote]++;
                update_post_meta($args['id'], "poll_votes", json_encode($poll_vote_meta), $prev_poll_vote_meta);
            }
            return;
        }
        $vote = intval($args['vote']);

        $up_or_down = $vote > 1 ? 'up' : 'down';
        $votes = get_post_meta($args['id'], "votes_$up_or_down", true);
        $total_votes = get_post_meta($args['id'], "total_votes", true);
        $votes_tally = get_post_meta($args['id'], "votes_tally", true);

        if(empty($votes)){
            add_post_meta($args['id'], "votes_$up_or_down", 1, true);
        }else{
            update_post_meta($args['id'], "votes_$up_or_down", intval($votes)+1, $votes);
        }

        if(empty($total_votes)){
            add_post_meta($args['id'], "total_votes", 1, true);
        }else{
            update_post_meta($args['id'], "total_votes", intval($total_votes)+1, $total_votes);
        }

        $votes_up = get_post_meta($args['id'], "votes_up", true);
        $votes_down = get_post_meta($args['id'], "votes_down", true);
        $total_votes = get_post_meta($args['id'], "total_votes", true);

        if(empty(get_post_meta($args['id'], "votes_down", true))){
            $votes_down = 0;
        }
        if(empty(get_post_meta($args['id'], "votes_up", true))){
            $votes_up = 0;  
        } 
        
        $points = intval($total_votes) - intval($votes_down);
        $tally = round(intval($votes_up)/intval($total_votes)*100) . '%';

        if(empty($votes_tally)){
            add_post_meta($args['id'], "votes_tally", $tally, true);
        }else{
            update_post_meta($args['id'], "votes_tally", $tally, $votes_tally);
        }

        return $points;
    }

    public function driversenvy_get_posts($request){
        $args = (array) $request->get_params();
        
        $args['post_type'] = $this->post_type;
        $args['paged'] = $args['page'];
        $args['posts_per_page'] = $args['per_page'];
        unset( $args['page'] );
        
        /**
         * Alter the query arguments for a request.
         *
         * This allows you to set extra arguments or defaults for a post
         * collection request.
         *
         * @param array $args Map of query var to query value.
         * @param WP_REST_Request $request Full details about the request.
         */
        $args = apply_filters( 'rest_post_query', $args, $request );
        
        $posts_query = new WP_Query();
        $query_result = $posts_query->query( $args );

        $posts = array();
        foreach ( $query_result as $post ) {


            $data = $this->prepare_item_for_response( $post, $request );
            $posts[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $posts );
        $count_query = new WP_Query();
        unset( $query_args['paged'] );
        $query_result = $count_query->query( $query_args );
        $total_posts = $count_query->found_posts;
        $response->header( 'X-WP-Total', (int) $total_posts );
        $max_pages = ceil( $total_posts / $request['per_page'] );
        $response->header( 'X-WP-TotalPages', (int) $max_pages );

        $response->total_count = $total_posts;

        $base = add_query_arg( $request->get_query_params(), rest_url( $namespace, '/' . $base . '/' . $this->get_post_type_base( $this->post_type ) ) );
        if ( $request['page'] > 1 ) {
            $prev_page = $request['page'] - 1;
            if ( $prev_page > $max_pages ) {
                $prev_page = $max_pages;
            }
            $prev_link = add_query_arg( 'page', $prev_page, $base );
            $response->link_header( 'prev', $prev_link );
        }
        if ( $max_pages > $request['page'] ) {
            $next_page = $request['page'] + 1;
            $next_link = add_query_arg( 'page', $next_page, $base );
            $response->link_header( 'next', $next_link );
        }

        return $response;
    
    }

    /**
    * get menu
    */

    public function get_menu($request){
        $args = (array) $request->get_params();

        return wp_get_nav_menu_items( $args['name'] );
    }

    /**
     * Determine the allowed query_vars for a get_items() response and
     * prepare for WP_Query.
     *
     * @param array $prepared_args
     * @return array $query_args
     */
    protected function prepare_items_query( $prepared_args = array() ) {

        $valid_vars = array_flip( $this->get_allowed_query_vars() );
        $query_args = array();
        foreach ( $valid_vars as $var => $index ) {
            if ( isset( $prepared_args[ $var ] ) ) {
                $query_args[ $var ] = apply_filters( 'rest_query_var-' . $var, $prepared_args[ $var ] );
            }
        }

        if ( empty( $query_args['post_status'] ) && 'attachment' === $this->post_type ) {
            $query_args['post_status'] = 'inherit';
        }

        return $query_args;
    }

    /**
     * Get all the WP Query vars that are allowed for the API request.
     *
     * @return array
     */
    protected function get_allowed_query_vars() {
        global $wp;
        $valid_vars = apply_filters( 'query_vars', $wp->public_query_vars );

        if ( current_user_can( 'edit_posts' ) ) {
            /**
             * Alter allowed query vars for authorized users.
             *
             * If the user has the `edit_posts` capability, we also allow use of
             * private query parameters, which are only undesirable on the
             * frontend, but are safe for use in query strings.
             *
             * To disable anyway, use
             * `add_filter('rest_private_query_vars', '__return_empty_array');`
             *
             * @param array $private List of allowed query vars for authorized users.
             */
            $private = apply_filters( 'rest_private_query_vars', $wp->private_query_vars );
            $valid_vars = array_merge( $valid_vars, $private );
        }
        // Define our own in addition to WP's normal vars
        $rest_valid = array( 'posts_per_page', 'ignore_sticky_posts', 'post_parent' );
        $valid_vars = array_merge( $valid_vars, $rest_valid );

        /**
         * Alter allowed query vars for the REST API.
         *
         * This filter allows you to add or remove query vars from the allowed
         * list for all requests, including unauthenticated ones. To alter the
         * vars for editors only, {@see rest_private_query_vars}.
         *
         * @param array $valid_vars List of allowed query vars.
         */
        $valid_vars = apply_filters( 'rest_query_vars', $valid_vars );

        return $valid_vars;
    }

    /**
     * Check the post excerpt and prepare it for single post output
     *
     * @param string       $excerpt
     * @return string|null $excerpt
     */
    protected function prepare_excerpt_response( $excerpt ) {
        if ( post_password_required() ) {
            return __( 'There is no excerpt because this is a protected post.' );
        }

        $excerpt = apply_filters( 'the_excerpt', apply_filters( 'get_the_excerpt', $excerpt ) );

        if ( empty( $excerpt ) ) {
            return '';
        }

        return $excerpt;
    }

    /**
     * Check the post_date_gmt or modified_gmt and prepare any post or
     * modified date for single post output.
     *
     * @param string       $date_gmt
     * @param string|null  $date
     * @return string|null ISO8601/RFC3339 formatted datetime.
     */
    protected function prepare_date_response( $date_gmt, $date = null ) {
        if ( '0000-00-00 00:00:00' === $date_gmt ) {
            return null;
        }

        if ( isset( $date ) ) {
            return rest_mysql_to_rfc3339( $date );
        }

        return rest_mysql_to_rfc3339( $date_gmt );
    }

    protected function prepare_password_response( $password ) {
        if ( ! empty( $password ) ) {
            /**
             * Fake the correct cookie to fool post_password_required().
             * Without this, get_the_content() will give a password form.
             */
            require_once ABSPATH . 'wp-includes/class-phpass.php';
            $hasher = new PasswordHash( 8, true );
            $value = $hasher->HashPassword( $password );
            $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] = wp_slash( $value );
        }

        return $password;
    }

    /**
     * Prepare a single post for create or update
     *
     * @param WP_REST_Request $request Request object
     * @return WP_Error|obj $prepared_post Post object
     */
    protected function prepare_item_for_database( $request ) {
        $prepared_post = new stdClass;

        // ID
        if ( isset( $request['id'] ) ) {
            $prepared_post->ID = absint( $request['id'] );
        }

        $schema = $this->get_item_schema();

        // Post title
        if ( ! empty( $schema['properties']['title'] ) && isset( $request['title'] ) ) {
            if ( is_string( $request['title'] ) ) {
                $prepared_post->post_title = wp_filter_post_kses( $request['title'] );
            } elseif ( ! empty( $request['title']['raw'] ) ) {
                $prepared_post->post_title = wp_filter_post_kses( $request['title']['raw'] );
            }
        }

        // Post content
        if ( ! empty( $schema['properties']['content'] ) && isset( $request['content'] ) ) {
            if ( is_string( $request['content'] ) ) {
                $prepared_post->post_content = wp_filter_post_kses( $request['content'] );
            } elseif ( isset( $request['content']['raw'] ) ) {
                $prepared_post->post_content = wp_filter_post_kses( $request['content']['raw'] );
            }
        }

        // Post excerpt
        if ( ! empty( $schema['properties']['excerpt'] ) && isset( $request['excerpt'] ) ) {
            if ( is_string( $request['excerpt'] ) ) {
                $prepared_post->post_excerpt = wp_filter_post_kses( $request['excerpt'] );
            } elseif ( isset( $request['excerpt']['raw'] ) ) {
                $prepared_post->post_excerpt = wp_filter_post_kses( $request['excerpt']['raw'] );
            }
        }

        // Post type
        if ( empty( $request['id'] ) ) {
            // Creating new post, use default type for the controller
            $prepared_post->post_type = $this->post_type;
        } else {
            // Updating a post, use previous type.
            $prepared_post->post_type = get_post_type( $request['id'] );
        }
        $post_type = get_post_type_object( $prepared_post->post_type );

        // Post status
        if ( isset( $request['status'] ) ) {
            $status = $this->handle_status_param( $request['status'], $post_type );
            if ( is_wp_error( $status ) ) {
                return $status;
            }

            $prepared_post->post_status = $status;
        }

        // Post date
        if ( ! empty( $request['date'] ) ) {
            $date_data = rest_get_date_with_gmt( $request['date'] );

            if ( ! empty( $date_data ) ) {
                list( $prepared_post->post_date, $prepared_post->post_date_gmt ) = $date_data;
            } else {
                return new WP_Error( 'rest_invalid_date', __( 'The date you provided is invalid.' ), array( 'status' => 400 ) );
            }
        } elseif ( ! empty( $request['date_gmt'] ) ) {
            $date_data = rest_get_date_with_gmt( $request['date_gmt'], true );

            if ( ! empty( $date_data ) ) {
                list( $prepared_post->post_date, $prepared_post->post_date_gmt ) = $date_data;
            } else {
                return new WP_Error( 'rest_invalid_date', __( 'The date you provided is invalid.' ), array( 'status' => 400 ) );
            }
        }
        // Post slug
        if ( isset( $request['slug'] ) ) {
            $prepared_post->post_name = sanitize_title( $request['slug'] );
        }

        // Author
        if ( ! empty( $schema['properties']['author'] ) && ! empty( $request['author'] ) ) {
            $author = $this->handle_author_param( $request['author'], $post_type );
            if ( is_wp_error( $author ) ) {
                return $author;
            }

            $prepared_post->post_author = $author;
        }

        // Post password
        if ( isset( $request['password'] ) ) {
            $prepared_post->post_password = $request['password'];

            if ( ! empty( $schema['properties']['sticky'] ) && ! empty( $request['sticky'] ) ) {
                return new WP_Error( 'rest_invalid_field', __( 'A post can not be sticky and have a password.' ), array( 'status' => 400 ) );
            }

            if ( ! empty( $prepared_post->ID ) && is_sticky( $prepared_post->ID ) ) {
                return new WP_Error( 'rest_invalid_field', __( 'A sticky post can not be password protected.' ), array( 'status' => 400 ) );
            }
        }

        if ( ! empty( $request['sticky'] ) ) {
            if ( ! empty( $prepared_post->ID ) && post_password_required( $prepared_post->ID ) ) {
                return new WP_Error( 'rest_invalid_field', __( 'A password protected post can not be set to sticky.' ), array( 'status' => 400 ) );
            }
        }

        // Parent
        $post_type_obj = get_post_type_object( $this->post_type );
        if ( ! empty( $schema['properties']['parent'] ) && ! empty( $request['parent'] ) ) {
            $parent = get_post( (int) $request['parent'] );
            if ( empty( $parent ) ) {
                return new WP_Error( 'rest_post_invalid_id', __( 'Invalid post parent ID.' ), array( 'status' => 400 ) );
            }

            $prepared_post->post_parent = (int) $parent->ID;
        }

        // Menu order
        if ( ! empty( $schema['properties']['menu_order'] ) && isset( $request['menu_order'] ) ) {
            $prepared_post->menu_order = (int) $request['menu_order'];
        }

        // Comment status
        if ( ! empty( $schema['properties']['comment_status'] ) && ! empty( $request['comment_status'] ) ) {
            $prepared_post->comment_status = sanitize_text_field( $request['comment_status'] );
        }

        // Ping status
        if ( ! empty( $schema['properties']['ping_status'] ) && ! empty( $request['ping_status'] ) ) {
            $prepared_post->ping_status = sanitize_text_field( $request['ping_status'] );
        }

        return apply_filters( 'rest_pre_insert_' . $this->post_type, $prepared_post, $request );
    }

    /**
     * Determine validity and normalize provided status param.
     *
     * @param string $post_status
     * @param object $post_type
     * @return WP_Error|string $post_status
     */
    protected function handle_status_param( $post_status, $post_type ) {
        $post_status = sanitize_text_field( $post_status );

        switch ( $post_status ) {
            case 'draft':
            case 'pending':
                break;
            case 'private':
                if ( ! current_user_can( $post_type->cap->publish_posts ) ) {
                    return new WP_Error( 'rest_cannot_publish', __( 'Sorry, you are not allowed to create private posts in this post type' ), array( 'status' => 403 ) );
                }
                break;
            case 'publish':
            case 'future':
                if ( ! current_user_can( $post_type->cap->publish_posts ) ) {
                    return new WP_Error( 'rest_cannot_publish', __( 'Sorry, you are not allowed to publish posts in this post type' ), array( 'status' => 403 ) );
                }
                break;
            default:
                if ( ! get_post_status_object( $post_status ) ) {
                    $post_status = 'draft';
                }
                break;
        }

        return $post_status;
    }

    /**
     * Determine validity and normalize provided author param.
     *
     * @param object|integer $post_author
     * @param object $post_type
     * @return WP_Error|integer $post_author
     */
    protected function handle_author_param( $post_author, $post_type ) {
        if ( is_object( $post_author ) ) {
            if ( empty( $post_author->id ) ) {
                return new WP_Error( 'rest_invalid_author', __( 'Invalid author object.' ), array( 'status' => 400 ) );
            }
            $post_author = (int) $post_author->id;
        } else {
            $post_author = (int) $post_author;
        }

        // Only check edit others' posts if we are another user
        if ( get_current_user_id() !== $post_author ) {

            $author = get_userdata( $post_author );

            if ( ! $author ) {
                return new WP_Error( 'rest_invalid_author', __( 'Invalid author ID.' ), array( 'status' => 400 ) );
            }
        }

        return $post_author;
    }

    /**
     * Determine the featured image based on a request param
     *
     * @param int $featured_image
     * @param int $post_id
     */
    protected function handle_featured_image( $featured_image, $post_id ) {

        $featured_image = (int) $featured_image;
        if ( $featured_image ) {
            $result = set_post_thumbnail( $post_id, $featured_image );
            if ( $result ) {
                return true;
            } else {
                return new WP_Error( 'rest_invalid_featured_image', __( 'Invalid featured image ID.' ), array( 'status' => 400 ) );
            }
        } else {
            return delete_post_thumbnail( $post_id );
        }

    }

    /**
     * Set the template for a page
     *
     * @param string $template
     * @param integer $post_id
     */
    public function handle_template( $template, $post_id ) {
        if ( in_array( $template, array_values( get_page_templates() ) ) ) {
            update_post_meta( $post_id, '_wp_page_template', $template );
        } else {
            update_post_meta( $post_id, '_wp_page_template', '' );
        }
    }



    /**
     * Check if a given post type should be viewed or managed.
     *
     * @param object|string $post_type
     * @return bool Is post type allowed?
     */
    protected function check_is_post_type_allowed( $post_type ) {
        if ( ! is_object( $post_type ) ) {
            $post_type = get_post_type_object( $post_type );
        }

        if ( ! empty( $post_type ) && $post_type->show_in_rest ) {
            return true;
        }

        return false;
    }


    /**
     * Check if we can read a post
     *
     * Correctly handles posts with the inherit status.
     *
     * @param obj $post Post object
     * @return bool Can we read it?
     */
    public function check_read_permission( $post ) {
        return true;
        if ( ! empty( $post->post_password ) && ! $this->check_update_permission( $post ) ) {
            return false;
        }

        $post_type = get_post_type_object( $post->post_type );
        if ( ! $this->check_is_post_type_allowed( $post_type ) ) {
            return false;
        }

        // Can we read the post?
        if ( 'publish' === $post->post_status || current_user_can( $post_type->cap->read_post, $post->ID ) ) {
            return true;
        }

        // Can we read the parent if we're inheriting?
        if ( 'inherit' === $post->post_status && $post->post_parent > 0 ) {
            $parent = get_post( $post->post_parent );

            if ( $this->check_read_permission( $parent ) ) {
                return true;
            }
        }

        // If we don't have a parent, but the status is set to inherit, assume
        // it's published (as per get_post_status())
        if ( 'inherit' === $post->post_status ) {
            return true;
        }

        return false;
    }

    /**
     * Check if we can edit a post
     *
     * @param obj $post Post object
     * @return bool Can we edit it?
     */
    protected function check_update_permission( $post ) {
        $post_type = get_post_type_object( $post->post_type );

        if ( ! $this->check_is_post_type_allowed( $post_type ) ) {
            return false;
        }

        return true;
        //return current_user_can( $post_type->cap->edit_post, $post->ID );
    }

    /**
     * Check if we can create a post
     *
     * @param obj $post Post object
     * @return bool Can we create it?
     */
    protected function check_create_permission( $post ) {
        $post_type = get_post_type_object( $post->post_type );

        if ( ! $this->check_is_post_type_allowed( $post_type ) ) {
            return false;
        }

        return current_user_can( $post_type->cap->create_posts );
    }

    /**
     * Check if we can delete a post
     *
     * @param obj $post Post object
     * @return bool Can we delete it?
     */
    protected function check_delete_permission( $post ) {
        $post_type = get_post_type_object( $post->post_type );

        if ( ! $this->check_is_post_type_allowed( $post_type ) ) {
            return false;
        }

        return current_user_can( $post_type->cap->delete_post, $post->ID );
    }

    /**
     * Get the base path for a post type's endpoints.
     *
     * @param object|string $post_type
     * @return string       $base
     */
    public function get_post_type_base( $post_type ) {
        if ( ! is_object( $post_type ) ) {
            $post_type = get_post_type_object( $post_type );
        }

        $base = ! empty( $post_type->rest_base ) ? $post_type->rest_base : $post_type->name;

        return $base;
    }

    /**
     * Prepare a single post output for response
     *
     * @param WP_Post $post Post object
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response $data
     */
    public function prepare_item_for_response( $post, $request ) {
        $GLOBALS['post'] = $post;
        setup_postdata( $post );

        // Base fields for every post
        $data = array(
            'id'                => $post->ID,
            'date'              => $this->prepare_date_response( $post->post_date_gmt, $post->post_date ),
            'date_gmt'          => $this->prepare_date_response( $post->post_date_gmt ),
            'campaign_active'   => $post->campaign_active,
            'sponsor'           => $post->sponsor,
            'campaigns'         => $post->campaigns,
            'campaign_items'    => $post->campaign_items,
            'parent'            => $post->post_parent,
            'guid'              => array(
                'rendered' => apply_filters( 'get_the_guid', $post->guid ),
                'raw'      => $post->guid,
            ),
            'modified'     => $this->prepare_date_response( $post->post_modified_gmt, $post->post_modified ),
            'modified_gmt' => $this->prepare_date_response( $post->post_modified_gmt ),
            'password'     => $post->post_password,
            'slug'         => $post->post_name,
            'status'       => $post->post_status,
            'type'         => $post->post_type,
            'link'         => get_permalink( $post->ID ),
        );

        $schema = $this->get_item_schema();

        if ( ! empty( $schema['properties']['title'] ) ) {
            $data['title'] = array(
                'raw'      => $post->post_title,
                'rendered' => get_the_title( $post->ID ),
            );
        }

        if ( ! empty( $schema['properties']['content'] ) ) {

            if ( ! empty( $post->post_password ) ) {
                $this->prepare_password_response( $post->post_password );
            }

            $data['content'] = array(
                'raw'      => $post->post_content,
                'rendered' => apply_filters( 'the_content', $post->post_content ),
            );

            // Don't leave our cookie lying around: https://github.com/WP-API/WP-API/issues/1055
            if ( ! empty( $post->post_password ) ) {
                $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] = '';
            }
        }

        if ( ! empty( $schema['properties']['excerpt'] ) ) {
            $data['excerpt'] = array(
                'raw'      => $post->post_excerpt,
                'rendered' => $this->prepare_excerpt_response( $post->post_excerpt ),
            );
        }

        if ( ! empty( $schema['properties']['author'] ) ) {
            $data['author'] = (int) $post->post_author;
        }

        if ( ! empty( $schema['properties']['featured_image'] ) ) {
            $data['featured_image'] = (int) get_post_thumbnail_id( $post->ID );
        }

        if ( ! empty( $schema['properties']['parent'] ) ) {
            $data['parent'] = (int) $post->post_parent;
        }

        if ( ! empty( $schema['properties']['menu_order'] ) ) {
            $data['menu_order'] = (int) $post->menu_order;
        }

        if ( ! empty( $schema['properties']['comment_status'] ) ) {
            $data['comment_status'] = $post->comment_status;
        }

        if ( ! empty( $schema['properties']['ping_status'] ) ) {
            $data['ping_status'] = $post->ping_status;
        }

        if ( ! empty( $schema['properties']['sticky'] ) ) {
            $data['sticky'] = is_sticky( $post->ID );
        }

        if ( ! empty( $schema['properties']['template'] ) ) {
            if ( $template = get_page_template_slug( $post->ID ) ) {
                $data['template'] = $template;
            } else {
                $data['template'] = '';
            }
        }

        if ( ! empty( $schema['properties']['format'] ) ) {
            $data['format'] = get_post_format( $post->ID );
            // Fill in blank post format
            if ( empty( $data['format'] ) ) {
                $data['format'] = 'standard';
            }
        }

        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
        $data = $this->filter_response_by_context( $data, $context );

        $data = $this->add_additional_fields_to_object( $data, $request );

        // Wrap the data in a response object
        $data = rest_ensure_response( $data );

        $data->add_links( $this->prepare_links( $post ) );

        return apply_filters( 'rest_prepare_' . $this->post_type, $data, $post, $request );
    }

    /**
     * Prepare links for the request.
     *
     * @param WP_Post $post Post object.
     * @return array Links for the given post.
     */
    protected function prepare_links( $post ) {
        $base = '/wp/v2/' . $this->get_post_type_base( $this->post_type );

        // Entity meta
        $links = array(
            'self' => array(
                'href' => rest_url( trailingslashit( $base ) . $post->ID ),
            ),
            'collection' => array(
                'href' => rest_url( $base ),
            ),
        );

        if ( ( in_array( $post->post_type, array( 'post', 'page' ) ) || post_type_supports( $post->post_type, 'author' ) )
            && ! empty( $post->post_author ) ) {
            $links['author'] = array(
                'href'       => rest_url( '/wp/v2/users/' . $post->post_author ),
                'embeddable' => true,
            );
        };

        if ( in_array( $post->post_type, array( 'post', 'page' ) ) || post_type_supports( $post->post_type, 'comments' ) ) {
            $replies_url = rest_url( '/wp/v2/comments' );
            $replies_url = add_query_arg( 'post_id', $post->ID, $replies_url );
            $links['replies'] = array(
                'href'         => $replies_url,
                'embeddable'   => true,
            );
        }

        if ( in_array( $post->post_type, array( 'post', 'page' ) ) || post_type_supports( $post->post_type, 'revisions' ) ) {
            $links['version-history'] = array(
                'href' => rest_url( trailingslashit( $base ) . $post->ID . '/revisions' ),
            );
        }
        $post_type_obj = get_post_type_object( $post->post_type );
        if ( $post_type_obj->hierarchical && ! empty( $post->post_parent ) ) {
            $links['up'] = array(
                'href'       => rest_url( trailingslashit( $base ) . (int) $post->post_parent ),
                'embeddable' => true,
            );
        }

        if ( ! in_array( $post->post_type, array( 'attachment', 'nav_menu_item', 'revision' ) ) ) {
            $attachments_url = rest_url( 'wp/v2/media' );
            $attachments_url = add_query_arg( 'post_parent', $post->ID, $attachments_url );
            $links['http://v2.wp-api.org/attachment'] = array(
                'href'       => $attachments_url,
                'embeddable' => true,
            );
        }

        $taxonomies = get_object_taxonomies( $post->post_type );
        if ( ! empty( $taxonomies ) ) {
            $links['http://v2.wp-api.org/term'] = array();

            foreach ( $taxonomies as $tax ) {
                $taxonomy_obj = get_taxonomy( $tax );
                // Skip taxonomies that are not public.
                if ( false === $taxonomy_obj->public ) {
                    continue;
                }

                if ( 'post_tag' === $tax ) {
                    $terms_url = rest_url( '/wp/v2/terms/tag' );
                } else {
                    $terms_url = rest_url( '/wp/v2/terms/' . $tax );
                }

                $terms_url = add_query_arg( 'post', $post->ID, $terms_url );

                $links['http://v2.wp-api.org/term'][] = array(
                    'href'       => $terms_url,
                    'taxonomy'   => $tax,
                    'embeddable' => true,
                );
            }
        }

        if ( post_type_supports( $post->post_type, 'custom-fields' ) ) {
            $links['http://v2.wp-api.org/meta'] = array(
                'href' => rest_url( trailingslashit( $base ) . $post->ID . '/meta' ),
                'embeddable' => true,
            );
        }

        return $links;
    }

    /**
     * Get the Post's schema, conforming to JSON Schema
     *
     * @return array
     */
    public function get_item_schema() {

        $base = $this->get_post_type_base( $this->post_type );
        $schema = array(
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => $this->post_type,
            'type'       => 'object',
            /*
             * Base properties for every Post
             */
            'properties' => array(
                'date'            => array(
                    'description' => 'The date the object was published.',
                    'type'        => 'string',
                    'format'      => 'date-time',
                    'context'     => array( 'view', 'edit', 'embed' ),
                ),
                'date_gmt'        => array(
                    'description' => 'The date the object was published, as GMT.',
                    'type'        => 'string',
                    'format'      => 'date-time',
                    'context'     => array( 'edit' ),
                ),
                'guid'            => array(
                    'description' => 'The globally unique identifier for the object.',
                    'type'        => 'object',
                    'context'     => array( 'view', 'edit' ),
                    'readonly'    => true,
                    'properties'  => array(
                        'raw'      => array(
                            'description' => 'GUID for the object, as it exists in the database.',
                            'type'        => 'string',
                            'context'     => array( 'edit' ),
                        ),
                        'rendered' => array(
                            'description' => 'GUID for the object, transformed for display.',
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                    ),
                ),
                'id'              => array(
                    'description' => 'Unique identifier for the object.',
                    'type'        => 'integer',
                    'context'     => array( 'view', 'edit', 'embed' ),
                    'readonly'    => true,
                ),
                'link'            => array(
                    'description' => 'URL to the object.',
                    'type'        => 'string',
                    'format'      => 'uri',
                    'context'     => array( 'view', 'edit', 'embed' ),
                    'readonly'    => true,
                ),
                'modified'        => array(
                    'description' => 'The date the object was last modified.',
                    'type'        => 'string',
                    'format'      => 'date-time',
                    'context'     => array( 'view', 'edit' ),
                ),
                'modified_gmt'    => array(
                    'description' => 'The date the object was last modified, as GMT.',
                    'type'        => 'string',
                    'format'      => 'date-time',
                    'context'     => array( 'view', 'edit' ),
                ),
                'password'        => array(
                    'description' => 'A password to protect access to the post.',
                    'type'        => 'string',
                    'context'     => array( 'edit' ),
                ),
                'slug'            => array(
                    'description' => 'An alphanumeric identifier for the object unique to its type.',
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit', 'embed' ),
                ),
                'status'          => array(
                    'description' => 'A named status for the object.',
                    'type'        => 'string',
                    'enum'        => array_keys( get_post_stati( array( 'internal' => false ) ) ),
                    'context'     => array( 'edit' ),
                ),
                'type'            => array(
                    'description' => 'Type of Post for the object.',
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit', 'embed' ),
                    'readonly'    => true,
                ),
            ),
        );

        $post_type_obj = get_post_type_object( $this->post_type );
        if ( $post_type_obj->hierarchical ) {
            $schema['properties']['parent'] = array(
                'description' => 'The ID for the parent of the object.',
                'type'        => 'integer',
                'context'     => array( 'view', 'edit' ),
            );
        }

        $post_type_attributes = array(
            'title',
            'editor',
            'author',
            'excerpt',
            'thumbnail',
            'comments',
            'revisions',
            'page-attributes',
            'post-formats',
        );
        $fixed_schemas = array(
            'post' => array(
                'title',
                'editor',
                'author',
                'excerpt',
                'thumbnail',
                'comments',
                'revisions',
                'post-formats',
            ),
            'page' => array(
                'title',
                'editor',
                'author',
                'excerpt',
                'thumbnail',
                'comments',
                'revisions',
                'page-attributes',
            ),
            'attachment' => array(
                'title',
                'author',
                'comments',
                'revisions',
            ),
        );
        foreach ( $post_type_attributes as $attribute ) {
            if ( isset( $fixed_schemas[ $this->post_type ] ) && ! in_array( $attribute, $fixed_schemas[ $this->post_type ] ) ) {
                continue;
            } elseif ( ! in_array( $this->post_type, array_keys( $fixed_schemas ) ) && ! post_type_supports( $this->post_type, $attribute ) ) {
                continue;
            }

            switch ( $attribute ) {

                case 'title':
                    $schema['properties']['title'] = array(
                        'description' => 'The title for the object.',
                        'type'        => 'object',
                        'context'     => array( 'view', 'edit', 'embed' ),
                        'properties'  => array(
                            'raw' => array(
                                'description' => 'Title for the object, as it exists in the database.',
                                'type'        => 'string',
                                'context'     => array( 'edit' ),
                            ),
                            'rendered' => array(
                                'description' => 'Title for the object, transformed for display.',
                                'type'        => 'string',
                                'context'     => array( 'view', 'edit', 'embed' ),
                            ),
                        ),
                    );
                    break;

                case 'editor':
                    $schema['properties']['content'] = array(
                        'description' => 'The content for the object.',
                        'type'        => 'object',
                        'context'     => array( 'view', 'edit' ),
                        'properties'  => array(
                            'raw' => array(
                                'description' => 'Content for the object, as it exists in the database.',
                                'type'        => 'string',
                                'context'     => array( 'edit' ),
                            ),
                            'rendered' => array(
                                'description' => 'Content for the object, transformed for display.',
                                'type'        => 'string',
                                'context'     => array( 'view', 'edit' ),
                            ),
                        ),
                    );
                    break;

                case 'author':
                    $schema['properties']['author'] = array(
                        'description' => 'The ID for the author of the object.',
                        'type'        => 'integer',
                        'context'     => array( 'view', 'edit', 'embed' ),
                    );
                    break;

                case 'excerpt':
                    $schema['properties']['excerpt'] = array(
                        'description' => 'The excerpt for the object.',
                        'type'        => 'object',
                        'context'     => array( 'view', 'edit', 'embed' ),
                        'properties'  => array(
                            'raw' => array(
                                'description' => 'Excerpt for the object, as it exists in the database.',
                                'type'        => 'string',
                                'context'     => array( 'edit' ),
                            ),
                            'rendered' => array(
                                'description' => 'Excerpt for the object, transformed for display.',
                                'type'        => 'string',
                                'context'     => array( 'view', 'edit', 'embed' ),
                            ),
                        ),
                    );
                    break;

                case 'thumbnail':
                    $schema['properties']['featured_image'] = array(
                        'description' => 'ID of the featured image for the object.',
                        'type'        => 'integer',
                        'context'     => array( 'view', 'edit' ),
                    );
                    break;

                case 'comments':
                    $schema['properties']['comment_status'] = array(
                        'description' => 'Whether or not comments are open on the object.',
                        'type'        => 'string',
                        'enum'        => array( 'open', 'closed' ),
                        'context'     => array( 'view', 'edit' ),
                    );
                    $schema['properties']['ping_status'] = array(
                        'description' => 'Whether or not the object can be pinged.',
                        'type'        => 'string',
                        'enum'        => array( 'open', 'closed' ),
                        'context'     => array( 'view', 'edit' ),
                    );
                    break;

                case 'page-attributes':
                    $schema['properties']['menu_order'] = array(
                        'description' => 'The order of the object in relation to other object of its type.',
                        'type'        => 'integer',
                        'context'     => array( 'view', 'edit' ),
                    );
                    break;

                case 'post-formats':
                    $schema['properties']['format'] = array(
                        'description' => 'The format for the object.',
                        'type'        => 'string',
                        'enum'        => get_post_format_slugs(),
                        'context'     => array( 'view', 'edit' ),
                    );
                    break;

            }
        }

        if ( 'post' === $this->post_type ) {
            $schema['properties']['sticky'] = array(
                'description' => 'Whether or not the object should be treated as sticky.',
                'type'        => 'boolean',
                'context'     => array( 'view', 'edit' ),
            );
        }

        if ( 'page' === $this->post_type ) {
            $schema['properties']['template'] = array(
                'description' => 'The theme file to use to display the object.',
                'type'        => 'string',
                'enum'        => array_values( get_page_templates() ),
                'context'     => array( 'view', 'edit' ),
            );
        }

        return $this->add_additional_fields_schema( $schema );
    }

}