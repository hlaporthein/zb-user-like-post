<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Abstract class which has helper functions to get data from the database
 */
abstract class ZB_ULP_DB_HELPER {
	/**
	 * The current table name
	 *
	 * @var boolean
	 */
	private $tableName = false;

	/**
	 * Constructor for the database class to inject the table name
	 *
	 * @param String $tableName - The current table name
	 */
	public function __construct( $tableName ) {
		$this->tableName = $tableName;
	}

	/**
	 * Insert data into the current data
	 *
	 * @param  array $data - Data to enter into the database table
	 *
	 * @return InsertQuery Object
	 */
	public function insert( array $data ) {
		global $wpdb;
		if ( empty( $data ) ) {
			return false;
		}
		$wpdb->insert( $this->tableName, $data );

		return $wpdb->insert_id;
	}

	/**
	 * Get all from the selected table
	 *
	 * @param  String $orderBy - Order by column name
	 *
	 * @return Table result
	 */
	public function get_all( $orderBy = null ) {
		global $wpdb;
		$sql = 'SELECT * FROM `' . $this->tableName . '`';
		if ( ! empty( $orderBy ) ) {
			$sql .= ' ORDER BY ' . $orderBy;
		}
		$all = $wpdb->get_results( $sql );

		return $all;
	}


	/**
	 * Get a value by a condition
	 *
	 * @param  Array $conditionValue - A key value pair of the conditions you want to search on
	 * @param  String $condition - A string value for the condition of the query default to equals
	 *
	 * @return Table result
	 */
	public function get_by( array $conditionValue, $condition = '=' ) {
		global $wpdb;
		$sql = 'SELECT * FROM `' . $this->tableName . '` WHERE ';
		foreach ( $conditionValue as $field => $value ) {
			switch ( strtolower( $condition ) ) {
				case 'in':
					if ( ! is_array( $value ) ) {
						throw new Exception( "Values for IN query must be an array.", 1 );
					}
					$sql .= $wpdb->prepare( '`%s` IN (%s)', $field, implode( ',', $value ) );
					break;
				default:
					$sql .= $wpdb->prepare( '`' . $field . '` ' . $condition . ' %s', $value );
					break;
			}
		}
		$result = $wpdb->get_results( $sql );

		return $result;
	}

	/**
	 * Update a table record in the database
	 *
	 * @param  array $data - Array of data to be updated
	 * @param  array $conditionValue - Key value pair for the where clause of the query
	 *
	 * @return Updated object
	 */
	public function update( array $data, array $conditionValue ) {
		global $wpdb;
		if ( empty( $data ) ) {
			return false;
		}
		$updated = $wpdb->update( $this->tableName, $data, $conditionValue );

		return $updated;
	}

	/**
	 * Delete row on the database table
	 *
	 * @param  array $conditionValue - Key value pair for the where clause of the query
	 *
	 * @return Int - Num rows deleted
	 */
	public function delete( array $conditionValue ) {
		global $wpdb;
		$deleted = $wpdb->delete( $this->tableName, $conditionValue );

		return $deleted;
	}
}

/**
 * Class ZB_Table Extending ZB_Base_Custom_Data Class
 */
class ZB_ULP_Table extends ZB_ULP_DB_HELPER {
	public function __construct( $tableName ) {
		parent::__construct( $tableName );
	}
}


/**
 * user like post action to db
 *
 * @param $user_id
 * @param $post_id
 *
 * @return InsertQuery|int return insert Id
 */
function _zb_ulp_user_like_action_to_db( $user_id, $post_id ) {
	global $wpdb;

	$table_name       = $wpdb->prefix . ZB_ULP_PLUGIN_TABLE_NAME;
	$table            = new ZB_ULP_Table( $table_name );
	$check_data_exist = $wpdb->get_var( "SELECT id FROM $table_name WHERE user_id = $user_id AND post_id = $post_id" );


	if ( $check_data_exist ) {
		$result = (int) $check_data_exist;
	} else {
		$result = $table->insert( array( 'user_id' => $user_id, 'post_id' => $post_id ) );

	}

	return $result;
}


/**
 * Check user like this post or not
 *
 *
 * @param $user_id
 * @param $post_id
 *
 * @return bool user like post or not
 */
function _zb_ulp_this_post_like( $user_id, $post_id ) {
	global $wpdb;

	$table_name       = $wpdb->prefix . ZB_ULP_PLUGIN_TABLE_NAME;
	$check_query      = "SELECT * FROM $table_name WHERE user_id= $user_id AND post_id = $post_id";
	$check_data_exist = $wpdb->get_var( $check_query );

	return ( ! empty( $check_data_exist ) ) ? true : false;

}


/**
 * It would give you user like post list by array
 *
 *
 * @param $user_id
 *
 * @return array|int user_like_post_list_by_array
 */
function _zb_ulp_this_user_like_posts_by_user_id( $user_id ) {
	global $wpdb;

	$table_name       = $wpdb->prefix . ZB_ULP_PLUGIN_TABLE_NAME;
	$table            = new ZB_ULP_Table( ( $table_name ) );
	$total_user_likes = $table->get_by( array( 'user_id' => $user_id ), '=' );

	if ( ! empty( $total_user_likes ) ) {

		foreach ( $total_user_likes as $like ) {
			$result[] = (int) $like->post_id;
		}

	} else {
		$result = 0;
	}

	return $result;

}


/**
 * It would give user like list of given post
 *
 *
 * @param $post_id
 *
 * @return array|int user list by array
 */
function _zb_ulp_this_post_like_users_by_post_id( $post_id ) {
	global $wpdb;

	$table_name       = $wpdb->prefix . ZB_ULP_PLUGIN_TABLE_NAME;
	$table            = new ZB_ULP_Table( ( $table_name ) );
	$total_user_likes = $table->get_by( array( 'post_id' => $post_id ), '=' );

	if ( ! empty( $total_user_likes ) ) {

		foreach ( $total_user_likes as $like ) {
			$result[] = (int) $like->user_id;
		}

	} else {
		$result = 0;
	}

	return $result;

}


/**
 * Remove Post Like Row
 *
 *
 *
 * @param $user_id
 * @param $post_id
 *
 * @return bool it would remove like row
 */
function _zb_ulp_unlike( $user_id, $post_id ) {
	global $wpdb;

	$table_name = $wpdb->prefix . ZB_ULP_PLUGIN_TABLE_NAME;
	$table      = new ZB_ULP_Table( ( $table_name ) );
	$result     = $table->delete( array( 'user_id' => $user_id, 'post_id' => $post_id ), '=' );

	return ( $result ) ? true : false;

}


