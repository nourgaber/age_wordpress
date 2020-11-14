<?php 
/**
 * Plugin Name: age
 * Description: This is an age plugin for user and api
 */


add_action( 'register_form', 'crf_registration_form' );
function crf_registration_form() {

	$age = ! empty( $_POST['age'] ) ? intval( $_POST['age'] ) : '';

	?>
	<p>
		<label for="age"><?php esc_html_e( 'Age', 'crf' ) ?><br/>
			<input type="number"
			       min="1"
			       max="120"
			       step="1"
			       id="age"
			       name="age"
			       value="<?php echo esc_attr( $age ); ?>"
			       class="input"
			/>
		</label>
	</p>
<?php
}
add_filter( 'registration_errors', 'crf_registration_errors', 10, 3 );
function crf_registration_errors( $errors, $sanitized_user_login, $user_email ) {

	if ( empty( $_POST['age'] ) ) {
		$errors->add( 'age_error', __( '<strong>ERROR</strong>: Please enter your Age.', 'crf' ) );
	}

	else if (intval( $_POST['age'] ) < 1 ) {
		$errors->add( 'age_error', __( '<strong>ERROR</strong>: You must be older than 1 year.', 'crf' ) );
    }
    else if(intval( $_POST['age'] ) >= 120 ) {
		$errors->add( 'age_error', __( '<strong>ERROR</strong>: You can not be older than 120.', 'crf' ) );
	}

	return $errors;
}

add_action( 'user_register', 'crf_user_register' );
function crf_user_register( $user_id ) {
	if ( ! empty( $_POST['age'] ) ) {
		update_user_meta( $user_id, 'age', intval( $_POST['age'] ) );
	}
}


add_action( 'user_new_form', 'crf_admin_registration_form' );
function crf_admin_registration_form( $operation ) {
	if ( 'add-new-user' !== $operation ) {
		return;
	}

	$age = ! empty( $_POST['age'] ) ? intval( $_POST['age'] ) : '';

	?>
	<h3><?php esc_html_e( 'Personal Information', 'crf' ); ?></h3>

	<table class="form-table">
		<tr>
			<th><label for="age"><?php esc_html_e( 'Age', 'crf' ); ?></label> <span class="description"><?php esc_html_e( '(required)', 'crf' ); ?></span></th>
			<td>
				<input type="number"
			       min="1"
			       max="120"
			       step="1"
			       id="age"
			       name="age"
			       value="<?php echo esc_attr( $age ); ?>"
			       class="regular-text"
				/>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'user_profile_update_errors', 'crf_user_profile_update_errors', 10, 3 );
function crf_user_profile_update_errors( $errors, $update, $user ) {


	if ( empty( $_POST['age'] ) ) {
		$errors->add( 'age_error', __( '<strong>ERROR</strong>: Please enter your Age.', 'crf' ) );
	}

	else if (intval( $_POST['age'] ) < 1 ) {
		$errors->add( 'age_error', __( '<strong>ERROR</strong>: You must be older than 1 year.', 'crf' ) );
    }

    else if(intval( $_POST['age'] ) >= 120 ) {
		$errors->add( 'age_error', __( '<strong>ERROR</strong>: You can not be older than 120.', 'crf' ) );
	}
}
add_action( 'edit_user_created_user', 'crf_user_register' );
add_action( 'show_user_profile', 'crf_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'crf_show_extra_profile_fields' );


function crf_show_extra_profile_fields( $user ) {
	$age = get_the_author_meta( 'age', $user->ID );
	?>
	<h3><?php esc_html_e( 'Personal Information', 'crf' ); ?></h3>

	<table class="form-table">
		<tr>
			<th><label for="age"><?php esc_html_e( 'Age', 'crf' ); ?></label></th>
			<td>
				<input type="number"
			       min="1"
			       max="120"
			       step="1"
			       id="age"
			       name="age"
			       value="<?php echo esc_attr( $age ); ?>"
			       class="regular-text"
				/>
			</td>
		</tr>
	</table>
	<?php
}

add_action( 'personal_options_update', 'crf_update_profile_fields' );
add_action( 'edit_user_profile_update', 'crf_update_profile_fields' );

function crf_update_profile_fields( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}

	if ( ! empty( $_POST['age'] ) && intval( $_POST['age'] ) >= 1 && intval( $_POST['age'] ) <= 120 ) {
		update_user_meta( $user_id, 'age', intval( $_POST['age'] ) );
	}
}
add_action('rest_api_init', function () {           
    $latest_posts_controller = new WP_REST_Users_Age_Controller();
   $latest_posts_controller->register_routes();
});

class WP_REST_Users_Age_Controller extends WP_REST_Users_Controller {

	/**
	 * Instance of a user meta fields object.
	 *
	 * @since 4.7.0
	 * @var WP_REST_User_Meta_Fields
	 */
	protected $meta;

	/**
	 * Constructor.
	 *
	 * @since 4.7.0
	 */
	public function __construct() {
		$this->namespace = 'wp/v2';
		$this->rest_base = 'users/age';
		$this->meta = new WP_REST_User_Meta_Fields();
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since 4.7.0
	 *
	 * @see register_rest_route()
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the user.' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_age' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}


  

	/**
	 * Retrieves a single user's age.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_age( $request ) {
        $user = $this->get_user( $request['id'] );
		if ( is_wp_error( $user ) ) {
			return $user;
        }
        $user = $this->prepare_item_for_response( $user->age, $request );
		$response = rest_ensure_response(array('age' => get_user_meta(intval($request['id']),'age')));

		return $response;
	}

}


?>


