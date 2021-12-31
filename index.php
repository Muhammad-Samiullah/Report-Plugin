<?php
/**
 * @package Smart_AMR_Monitor
 * @version 1.0.0
 */
/*
Plugin Name: System for Monitoring of Antimicrobial Threats
Plugin URI: http://taleemkahani.com/smart
Description: This plugin provides the ability to record antimicrobial resistance data and generate reports
Author: Muhammad Bilal
Version: 1.0.0
Author URI: http://taleemkahani.com
*/

// Home page code
add_shortcode('SMART_AMR_Home', 'SMART_AMR_Home');
function SMART_AMR_Home($atts) {

	$current_user = wp_get_current_user();

	if ( is_user_logged_in() && (! user_can( $current_user, "subscriber") ) ) {
		$Content = '<p>Welcome privileged user</p>';
	} else {
		$Content = '<p>Please contact system administrator for access</p>';
	} 
    return $Content;
}


// Hospital page code
add_shortcode('SMART_AMR_Hospital', 'SMART_AMR_Hospital');
function SMART_AMR_Hospital($atts) {

	$current_user = wp_get_current_user();

	if ( is_user_logged_in()  ) {
		$Content = '<p><form id="hospital-form">';
		//$Content .= wp_nonce_field('add_transfer','HakooNahMataTa');
		$Content .= file_get_contents (plugin_dir_path( __FILE__ ) . "hospital-form.txt");
		$Content .= '</form></p>';
	} else {
		$Content = '<p>Please contact system administrator for access</p>';
	} 
    return $Content;
}


// Patient page code
add_shortcode('SMART_AMR_Patient', 'SMART_AMR_Patient');
function SMART_AMR_Patient($atts) {

	$current_user = wp_get_current_user();

	if ( is_user_logged_in() && (! user_can( $current_user, "subscriber") ) ) {
		$Content = '';
		$Content .= file_get_contents (plugin_dir_path( __FILE__ ) . "patient-form.txt");
	} else {
		$Content = '<p>Please contact system administrator for access</p>';
	} 
    return $Content;
}


// Reports page code
add_shortcode('SMART_AMR_Reports', 'SMART_AMR_Reports');
function SMART_AMR_Reports($atts) {

	$current_user = wp_get_current_user();

	if ( is_user_logged_in() && (! user_can( $current_user, "subscriber") ) ) {
		$Content .= '<form id="report-form">';
		$Content .= '<input name="action" type="hidden" value="panel_handler" />';
		$Content .= '<select id="panel_select" name="panel_select" style="margin-bottom: 20px"><option value="">Select a Panel</option></select>';
		$Content .= '<div id="report-inputs" style="display: none"></div>';
		$Content .= '<div id="panel_success" style="display: none"><h2>Report Submitted Successfully</h2><br><br></div>';
		$Content .= '<div id="report-table" style="display: none"><h3>Hospital Name</h3></div>';
		$Content .= '<div id="panel_error" style="display: none"><h3>Could not fetch the form</h3></div>';
		$Content .= '</form>';
	} else {
		$Content = '<p>Please contact system administrator for access</p>';
	} 
    return $Content;
}




 // Add menu items
add_filter('wp_nav_menu_items', 'add_admin_link', 10, 2);
function add_admin_link($items, $args){
// Get Menu Locations
// print_r(get_registered_nav_menus());
	
    if( $args->theme_location == 'footer' ){
        $items .= '<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home menu-item-44"><a title="Hospital" href="'. esc_url( home_url() ) .'/hospital/"> ' . __( 'Hospital' ) . ' </a></li>';
		$items .= '<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home menu-item-45"><a title="Patient" href="'. esc_url( home_url() ) .'/patient/"> ' . __( 'Patient' ) . ' </a></li>';
		$items .= '<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home menu-item-46"><a title="Reports" href="'. esc_url( home_url() ) .'/reports/"> ' . __( 'Reports' ) . ' </a></li>';
    }
    return $items;
}


// Retrieve Hospital Information
add_action( 'wp_ajax_data_getter_hospital', 'data_getter_hospital' );
function data_getter_hospital(){
	$current_user = wp_get_current_user();
    global $wpdb;
	$table = $wpdb->prefix . "SMART_AMR";
	$rows = $wpdb->get_results('SELECT field,text FROM '.$table.'  WHERE `user` = "' . $current_user->user_email . '"');
	

	if ( $rows ) {
	   $list= json_encode($rows, JSON_FORCE_OBJECT );
    } else {
		$list = json_encode (json_decode ("{}"));
	}
	echo $list;
	die();
	
}

// Retrieve Patient Information
add_action( 'wp_ajax_patient_finder', 'patient_finder' );
function patient_finder(){
	$current_user = wp_get_current_user();
    global $wpdb;
	$table = $wpdb->prefix . "SMART_AMR_Patients";
	$patient_id = $_POST['patient-find-id'];
	$patient_name = $_POST['patient-find-name'];
	if($patient_name.length > 2 && $patient_id.length == 0) {
		$rows = $wpdb->get_results("SELECT * FROM ".$table." WHERE `new-patient-name` LIKE '%".$patient_name."%'");	
	}
	else if($patient_id > 0) {
		$rows = $wpdb->get_results("SELECT * FROM ".$table." WHERE id='".$patient_id."'");		
	}
	else {
		$rows = $wpdb->get_results("SELECT * FROM ".$table." WHERE `new-patient-name` LIKE '%".$patient_name."%' OR id='".$patient_id."'");		
	}
	
	
	

	if ( $rows ) {
	   $list= json_encode($rows, JSON_FORCE_OBJECT );
    } else {
		$list = json_encode (json_decode ("{}"));
	}
	echo $list;
	die();
	
}


add_action( 'wp_ajax_data_getter_panels', 'data_getter_panels' );
function data_getter_panels(){
	$current_user = wp_get_current_user();
    global $wpdb;
	$table = $wpdb->prefix . "SMART_AMR_Panels";
	$rows = $wpdb->get_results('SELECT DISTINCT(panels) FROM '.$table);
	

	if ( $rows ) {
	   $list= json_encode($rows, JSON_FORCE_OBJECT );
    } else {
		$list = json_encode (json_decode ("{}"));
	}
	echo $list;
	die();
	
}


add_action( 'wp_ajax_see_reports_handler', 'see_reports_handler' );
function see_reports_handler(){
    global $wpdb;
	$id=$_POST['patient-id'];
	$table1 = $wpdb->prefix . "SMART_AMR_Reports";
	$rows = $wpdb->get_results('SELECT DISTINCT(id), panel_name, patient_id, time_stamp FROM '.$table1.' WHERE patient_id='.$id);
	

	if ( $rows ) {
	   $list= json_encode($rows, JSON_FORCE_OBJECT );
    } else {
		$list = json_encode (json_decode ("{}"));
	}
	echo $list;
	die();
	
}


add_action( 'wp_ajax_see_report', 'see_report' );
function see_report(){
    global $wpdb;
	$report_id = $_POST['report-id'];
	$patient_id = $_POST['patient-id'];
	$panel_name = $_POST['panel-name'];
	$time_stamp = $_POST['time-stamp'];
	$table1 = $wpdb->prefix . "SMART_AMR_Reports";
	$table2 = $wpdb->prefix . "SMART_AMR_Panels";
	$table3 = $wpdb->prefix . "SMART_AMR_Patients";
	$table4 = $wpdb->prefix . "SMART_AMR";
 	$rows = $wpdb->get_results("SELECT * FROM ".$table1." t1 JOIN ".$table2." t2 ON t1.name=t2.abreviation JOIN ".$table3." t3 ON t1.patient_id=t3.id WHERE t1.id='$report_id' AND t1.panel_name=t2.panels AND t1.panel_name='$panel_name' AND t1.time_stamp='$time_stamp'");
	

	if ( $rows ) {
	   $list= json_encode($rows, JSON_FORCE_OBJECT );
    } else {
		$list = json_encode (json_decode ("{}"));
	}
	echo $list;
	die();
	
}



// Including Bootstrap 4
add_action('wp_enqueue_scripts', 'pwwp_enqueue_bootstrap4');
function pwwp_enqueue_bootstrap4() {
    wp_enqueue_style( 'bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css' );
    wp_enqueue_script( 'popper','//cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js', array( 'jquery' ),'',true );
    wp_enqueue_script( 'boot3','//maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array( 'jquery' ),'',true );

}


/* Disable WordPress Admin Bar for all users */
add_filter( 'show_admin_bar', '__return_false' );

// Disable Non-Admin Dashboard
add_action( 'admin_init', 'wpse_11244_restrict_admin', 1 );
function wpse_11244_restrict_admin() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_redirect( home_url() );
        exit;
    }
}


// Panel Form Generator
add_action( 'wp_ajax_panel_handler', 'panel_handler' );
function panel_handler() {
	global $selected;
	global $wpdb;
	$panels_table = $wpdb->prefix . "SMART_AMR_Panels";
	$reports_table = $wpdb->prefix . "SMART_AMR_Reports";
	if(isset($_POST['panel_select'])) {
		
		$selected = $_POST['panel_select'];

		$rows = $wpdb->get_results('SELECT * FROM ' . $panels_table . ' WHERE `panels` = "' . $selected . '"');


		if ( $rows ) {
		   $list= json_encode($rows, JSON_FORCE_OBJECT );
		} else {
			$list = json_encode (json_decode ("{}"));
		}
		echo $list;
		unset($_POST['panel_select']);
    }
	else {
		$id = DateTime::createFromFormat('U.u', microtime(true))->format("mdYHisu");
		$selected = $_POST['panel_name'];

		$values = array();
		
		$patient_id = $_POST['patient-id'];

		foreach ( $_POST as $key => $value ) {
			echo $key;
			echo $value;
				$values[] = $wpdb->prepare( "(%s,%s,%s,%s,%s)", $id, $patient_id, $selected, $key, $value );
		}

		$query = "INSERT INTO " . $reports_table .  " (id, patient_id, panel_name, name, value) VALUES ";
		$query .= implode( ",\n", $values );

		$success = $wpdb->query( $wpdb->prepare( $query , $values ) );

		if($success){
			$content .= 'Report Data saved successfully. ' ; 
			json_encode($id, JSON_FORCE_OBJECT);
			} else {
			$content .= 'Error saving Report data. ';
		}
	}
	die();
}







// HOSPITAL FORM DATA HANDLER
add_action( 'wp_ajax_hospital_handler', 'hospital_handler' );
function hospital_handler() {
	
	if ( !is_user_logged_in() ) {
	echo 'User is not logged in';
    die();
	}

	$content = "User is logged in. ";
	
	$current_user = wp_get_current_user();
    global $wpdb;
	$table = $wpdb->prefix . "SMART_AMR";


    if (isset($_POST['hospital-name'])) {
	$content .= 'Data submitted. ';

	$wpdb->delete( $table, array( 'user' => $current_user->user_email, 'dtype'=>'hospital' ) );        

	$values = array();

	foreach ( $_POST as $key => $value ) {
			$values[] = $wpdb->prepare( "(%s,%s,%s,%s)",$current_user->user_email, 'hospital', $key, $value );
	}

	$query = "INSERT INTO " . $table .  " (user, dtype, field, text) VALUES ";
	$query .= implode( ",\n", $values );

    $success = $wpdb->query( $wpdb->prepare( $query , $values ) );

    if($success){
        $content .= 'Hospital Data saved successfully. ' ; 
		} else {
		$content .= 'Error saving Hospital data. ';
		}


    }

	echo $content;
	die();
}

















// Patient FORM DATA HANDLER
add_action( 'wp_ajax_Patient_Data_Handler', 'Patient_Data_Handler' );
function Patient_Data_Handler() {
	
	if ( !is_user_logged_in() ) {
	echo 'User is not logged in';
    die();
	}

	
	$current_user = wp_get_current_user();
    global $wpdb;
	$table = $wpdb->prefix . "SMART_AMR_Patients";
	
	if (isset($_POST['new-patient-name'])) {
		$content .= 'Data submitted . ';    

		$id = DateTime::createFromFormat('U.u', microtime(true))->format("mdYHisu");
		$user = 'patient';
		$name = $_POST['new-patient-name'];
		$dob = $_POST['new-patient-dob'];
		$gender = $_POST['new-patient-gender'];
		$contact = $_POST['new-patient-contact'];
		$disease = $_POST['new-patient-disease'];
		$residence = $_POST['new-patient-residence'];

		$query = "INSERT INTO " . $table .  " (`id`, `user`, `new-patient-name`, `new-patient-dob`, `new-patient-gender`, `new-patient-contact`, `new-patient-disease`, `new-patient-residence`) VALUES ('$id', '$user', '$name', '$dob', '$gender', '$contact', '$disease', '$residence');";
		$success = $wpdb->query( $query );

		if($success){
			$content .= ' . Patient Data saved successfully. ' ; 
			} else {
			$content .= ' . Error saving Patient data. ';
			}

    }
	
	echo $content;
	die();
}





// LOAD JQUERY SCRIPT
function SMART_AMR_scripts() {
    wp_enqueue_script( 'frontend-ajax', plugins_url( 'js/demo.js?x=' . rand(), __FILE__ ), array('jquery'), null, true );
    wp_localize_script( 'frontend-ajax', 'frontend_ajax_object',
        array( 'ajaxurl' => admin_url( 'admin-ajax.php' ))
    	);
}
add_action( 'wp_enqueue_scripts', 'SMART_AMR_scripts' );




// Check initial table 
function SMART_AMR_Table_Check(){
        global $wpdb;
		
		$my_products_db_version = '1.0.0';
		$charset_collate = $wpdb->get_charset_collate();

		$table_name = $wpdb->prefix . "SMART_AMR";
		if ( $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name ) {
			$sql = "CREATE TABLE  $table_name ( 
				`id`  int NOT NULL AUTO_INCREMENT,
				`user`  varchar(256)   NOT NULL,
				`dtype`  varchar(256)   NOT NULL,
				`field`  varchar(256)   NOT NULL,
				`text`  varchar(1024)   NOT NULL, 
				PRIMARY KEY  (id)
				) $charset_collate;";
    		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    		dbDelta($sql);
    		add_option('my_db_version', $my_products_db_version);
			}
			
		$table_name = $wpdb->prefix . "SMART_AMR_Patients";
		if ( $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name ) {
			$sql = "CREATE TABLE  $table_name ( 
				`id`  int NOT NULL,
				`user`  varchar(256)   NOT NULL,
				`new-patient-name`  varchar(256)   NOT NULL,
				`new-patient-dob`  varchar(256)   NOT NULL,
				`new-patient-gender`  varchar(24)   NOT NULL, 
				`new-patient-contact`  varchar(32)   NOT NULL, 
				`new-patient-disease`  varchar(1024)   NOT NULL, 
				`new-patient-residence`  varchar(1024)   NOT NULL, 
				PRIMARY KEY  (id)
				) $charset_collate;";
    		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    		dbDelta($sql);
    		add_option('my_db_version', $my_products_db_version);
			}
	
			$table_name = $wpdb->prefix . "SMART_AMR_Panels";
		if ( $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name ) {
			$sql = "CREATE TABLE $table_name (
					  `panels` varchar(256) NOT NULL,
					  `abreviation` varchar(256) NOT NULL,
					  `name` varchar(256) NOT NULL,
					  `lower_range` int(11) NOT NULL,
					  `upper_range` int(11) NOT NULL
					) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; $charset_collate;";
    		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    		dbDelta($sql);
    		add_option('my_db_version', $my_products_db_version);
			}
	
			$table_name = $wpdb->prefix . "SMART_AMR_Reports";
			if ( $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name ) {
				$sql = "CREATE TABLE $table_name (
						  `id` int(11) NOT NULL,
						  `patient_id` int(11) NOT NULL,
						  `panel_name` varchar(256) NOT NULL,
						  `name` varchar(256) NOT NULL,
						  `value` int(11) NOT NULL,
						  `time_stamp` timestamp NOT NULL DEFAULT current_timestamp()
						) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; $charset_collate;";
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);
				add_option('my_db_version', $my_products_db_version);
			}
	
			$table_name = $wpdb->prefix . "SMART_AMR_Panels";
			if ( $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name ) {
			$sql = "
				INSERT INTO $table_name (`panels`, `abreviation`, `name`, `lower_range`, `upper_range`) VALUES
				('All Antibiotic', 'AMC', 'Amoxixillin / Clavulanic acid', 14, 17),
				('All Antibiotic', 'AMK', 'Amikacin', 15, 16),
				('All Antibiotic', 'AMP', 'Ampicillin', 14, 16),
				('All Antibiotic', 'ATM', 'Aztreonam', 18, 20),
				('All Antibiotic', 'AZM', 'Azithromycin', 14, 17),
				('All Antibiotic', 'CAZ', 'Ceftazidime', 18, 20),
				('All Antibiotic', 'CEP', 'Cephalothin', 15, 17),
				('All Antibiotic', 'CFM', 'Cefixime', 16, 18),
				('All Antibiotic', 'CHL', 'Chloramphenicol', 13, 17),
				('All Antibiotic', 'CIP', 'Ciprofloxin', 16, 20),
				('All Antibiotic', 'CLI', 'Clindamycin', 15, 20),
				('All Antibiotic', 'COL', 'Colistin', 11, 11),
				('All Antibiotic', 'CRB', 'Carbenicillin', 20, 22),
				('All Antibiotic', 'CRO', 'Ceftriaxone', 20, 22),
				('All Antibiotic', 'CTX', 'Cefotaxime', 23, 25),
				('All Antibiotic', 'CXM', 'Cefuroxime', 15, 17),
				('All Antibiotic', 'CZX', 'Ceftizoxime', 22, 24),
				('All Antibiotic', 'DOR', 'Doripenem', 20, 22),
				('All Antibiotic', 'DOX', 'Doxycycline', 11, 13),
				('All Antibiotic', 'ERY', 'Erythromycin', 14, 22),
				('All Antibiotic', 'ETP', 'Ertapenem', 19, 21),
				('All Antibiotic', 'FEP', 'Cefepime', 19, 24),
				('All Antibiotic', 'FOX', 'Cefoxtin', 15, 17),
				('All Antibiotic', 'GEN', 'Gentamicin', 13, 14),
				('All Antibiotic', 'IPM', 'Imipenem', 20, 22),
				('All Antibiotic', 'LVX', 'Levofloxacin', 14, 16),
				('All Antibiotic', 'MAN', 'Cefamandole', 15, 17),
				('All Antibiotic', 'MEM', 'Meropenem', 20, 22),
				('All Antibiotic', 'MEZ', 'Mezlocillin', 18, 20),
				('All Antibiotic', 'MNO', 'Monocycline', 13, 15),
				('All Antibiotic', 'NIT', 'Nitrofurantoin', 15, 16),
				('All Antibiotic', 'NOR', 'Norfloxacin', 13, 16),
				('All Antibiotic', 'OFX', 'Ofloxacin', 13, 15),
				('All Antibiotic', 'OXA', 'Oxacillin', 18, 18),
				('All Antibiotic', 'PEN', 'Penicillin G', 29, 29),
				('All Antibiotic', 'PIP', 'Piperacillin', 18, 20),
				('All Antibiotic', 'RIF', 'Rifampin', 17, 19),
				('All Antibiotic', 'SSS', 'Sulfonamides', 13, 16),
				('All Antibiotic', 'SXT', 'Trimethoprime / Sulfamethoxazole', 11, 15),
				('All Antibiotic', 'TCC', 'Ticarcillin/Clavulanic acid', 15, 19),
				('All Antibiotic', 'TCY', 'Tetracycline', 12, 14),
				('All Antibiotic', 'TEC', 'Teicoplanin', 11, 13),
				('All Antibiotic', 'TIC', 'Ticarcillin', 15, 19),
				('All Antibiotic', 'TOB', 'Tobramycin', 13, 14),
				('All Antibiotic', 'VAN', 'Vancomycin', 15, 15),
				('Staphylococcus sp.', 'AMC', 'Amoxixillin / Clavulanic acid', 14, 17),
				('Staphylococcus sp.', 'AMP', 'Ampicillin', 14, 16),
				('Staphylococcus sp.', 'CEP', 'Cephalothin', 15, 17),
				('Staphylococcus sp.', 'CHL', 'Chloramphenicol', 13, 17),
				('Staphylococcus sp.', 'CIP', 'Ciprofloxin', 16, 20),
				('Staphylococcus sp.', 'CLI', 'Clindamycin', 15, 20),
				('Staphylococcus sp.', 'CZX', 'Ceftizoxime', 22, 24),
				('Staphylococcus sp.', 'ERY', 'Erythromycin', 14, 22),
				('Staphylococcus sp.', 'NIT', 'Nitrofurantoin', 15, 16),
				('Staphylococcus sp.', 'OFX', 'Ofloxacin', 13, 15),
				('Staphylococcus sp.', 'OXA', 'Oxacillin', 18, 18),
				('Staphylococcus sp.', 'PEN', 'Penicillin G', 29, 29),
				('Staphylococcus sp.', 'SXT', 'Trimethoprime / Sulfamethoxazole', 11, 15),
				('Staphylococcus sp.', 'TCY', 'Tetracycline', 12, 14),
				('Staphylococcus sp.', 'VAN', 'Vancomycin', 15, 15),
				('Streptococcus sp.', 'AMC', 'Amoxixillin / Clavulanic acid', 14, 17),
				('Streptococcus sp.', 'AMP', 'Ampicillin', 14, 16),
				('Streptococcus sp.', 'CEP', 'Cephalothin', 15, 17),
				('Streptococcus sp.', 'CHL', 'Chloramphenicol', 13, 17),
				('Streptococcus sp.', 'CIP', 'Ciprofloxin', 16, 20),
				('Streptococcus sp.', 'CLI', 'Clindamycin', 15, 20),
				('Streptococcus sp.', 'CZX', 'Ceftizoxime', 22, 24),
				('Streptococcus sp.', 'ERY', 'Erythromycin', 14, 22),
				('Streptococcus sp.', 'GEN', 'Gentamicin', 13, 14),
				('Streptococcus sp.', 'NIT', 'Nitrofurantoin', 15, 16),
				('Streptococcus sp.', 'OFX', 'Ofloxacin', 13, 15),
				('Streptococcus sp.', 'OXA', 'Oxacillin', 18, 18),
				('Streptococcus sp.', 'PEN', 'Penicillin G', 29, 29),
				('Streptococcus sp.', 'SXT', 'Trimethoprime / Sulfamethoxazole', 11, 15),
				('Streptococcus sp.', 'TCY', 'Tetracycline', 12, 14),
				('Streptococcus sp.', 'VAN', 'Vancomycin', 15, 15),
				('Gram Negative', 'AMP', 'Ampicillin', 14, 16),
				('Gram Negative', 'ATM', 'Aztreonam', 18, 20),
				('Gram Negative', 'CAZ', 'Ceftazidime', 18, 20),
				('Gram Negative', 'CEP', 'Cephalothin', 15, 17),
				('Gram Negative', 'CIP', 'Ciprofloxin', 16, 20),
				('Gram Negative', 'CTX', 'Cefotaxime', 23, 25),
				('Gram Negative', 'CXM', 'Cefuroxime', 15, 17),
				('Gram Negative', 'FOX', 'Cefoxtin', 15, 17),
				('Gram Negative', 'GEN', 'Gentamicin', 13, 14),
				('Gram Negative', 'IPM', 'Imipenem', 20, 22),
				('Gram Negative', 'MEZ', 'Mezlocillin', 18, 20),
				('Gram Negative', 'SXT', 'Trimethoprime / Sulfamethoxazole', 11, 15),
				('Enterococcus sp.', 'AMC', 'Amoxixillin / Clavulanic acid', 14, 17),
				('Enterococcus sp.', 'AMP', 'Ampicillin', 14, 16),
				('Enterococcus sp.', 'CEP', 'Cephalothin', 15, 17),
				('Enterococcus sp.', 'CIP', 'Ciprofloxin', 16, 20),
				('Enterococcus sp.', 'CLI', 'Clindamycin', 15, 20),
				('Enterococcus sp.', 'DOX', 'Doxycycline', 11, 13),
				('Enterococcus sp.', 'ERY', 'Erythromycin', 14, 22),
				('Enterococcus sp.', 'GEN', 'Gentamicin', 13, 14),
				('Enterococcus sp.', 'MNO', 'Monocycline', 13, 15),
				('Enterococcus sp.', 'OXA', 'Oxacillin', 18, 18),
				('Enterococcus sp.', 'PEN', 'Penicillin G', 29, 29),
				('Enterococcus sp.', 'RIF', 'Rifampin', 17, 19),
				('Enterococcus sp.', 'SXT', 'Trimethoprime / Sulfamethoxazole', 11, 15),
				('Enterococcus sp.', 'TEC', 'Teicoplanin', 11, 13),
				('Enterococcus sp.', 'VAN', 'Vancomycin', 15, 15),
				('Gram positive urine', 'AMC', 'Amoxixillin / Clavulanic acid', 14, 17),
				('Gram positive urine', 'AMP', 'Ampicillin', 14, 16),
				('Gram positive urine', 'CEP', 'Cephalothin', 15, 17),
				('Gram positive urine', 'CHL', 'Chloramphenicol', 13, 17),
				('Gram positive urine', 'CIP', 'Ciprofloxin', 16, 20),
				('Gram positive urine', 'CLI', 'Clindamycin', 15, 20),
				('Gram positive urine', 'CZX', 'Ceftizoxime', 22, 24),
				('Gram positive urine', 'ERY', 'Erythromycin', 14, 22),
				('Gram positive urine', 'GEN', 'Gentamicin', 13, 14),
				('Gram positive urine', 'NIT', 'Nitrofurantoin', 15, 16),
				('Gram positive urine', 'OFX', 'Ofloxacin', 13, 15),
				('Gram positive urine', 'OXA', 'Oxacillin', 18, 18),
				('Gram positive urine', 'PEN', 'Penicillin G', 29, 29),
				('Gram positive urine', 'SXT', 'Trimethoprime / Sulfamethoxazole', 11, 15),
				('Gram positive urine', 'TCY', 'Tetracycline', 12, 14),
				('Gram positive urine', 'VAN', 'Vancomycin', 15, 15),
				('Gram Negative Urine', 'AMC', 'Amoxixillin / Clavulanic acid', 14, 17),
				('Gram Negative Urine', 'AMP', 'Ampicillin', 14, 16),
				('Gram Negative Urine', 'ATM', 'Aztreonam', 18, 20),
				('Gram Negative Urine', 'CEP', 'Cephalothin', 15, 17),
				('Gram Negative Urine', 'CTX', 'Cefotaxime', 23, 25),
				('Gram Negative Urine', 'CXM', 'Cefuroxime', 15, 17),
				('Gram Negative Urine', 'GEN', 'Gentamicin', 13, 14),
				('Gram Negative Urine', 'IPM', 'Imipenem', 20, 22),
				('Gram Negative Urine', 'MEZ', 'Mezlocillin', 18, 20),
				('Gram Negative Urine', 'NIT', 'Nitrofurantoin', 15, 16),
				('Gram Negative Urine', 'NOR', 'Norfloxacin', 13, 16),
				('Gram Negative Urine', 'SXT', 'Trimethoprime / Sulfamethoxazole', 11, 15),
				('Salmonella sp.', 'AMP', 'Ampicillin', 14, 0),
				('Salmonella sp.', 'ATM', 'Aztreonam', 18, 0),
				('Salmonella sp.', 'CAZ', 'Ceftazidime', 18, 0),
				('Salmonella sp.', 'CEP', 'Cephalothin', 15, 0),
				('Salmonella sp.', 'CIP', 'Ciprofloxin', 16, 0),
				('Salmonella sp.', 'CTX', 'Cefotaxime', 23, 0),
				('Salmonella sp.', 'CXM', 'Cefuroxime', 15, 0),
				('Salmonella sp.', 'FOX', 'Cefoxtin', 15, 0),
				('Salmonella sp.', 'GEN', 'Gentamicin', 13, 0),
				('Salmonella sp.', 'IPM', 'Imipenem', 20, 0),
				('Salmonella sp.', 'MEZ', 'Mezlocillin', 18, 0),
				('Salmonella sp.', 'SXT', 'Trimethoprime / Sulfamethoxazole', 11, 0),
				('Salmonella sp.', 'AMP', 'Ampicillin', 14, 16),
				('Salmonella sp.', 'ATM', 'Aztreonam', 18, 20),
				('Salmonella sp.', 'CAZ', 'Ceftazidime', 18, 20),
				('Salmonella sp.', 'CEP', 'Cephalothin', 15, 17),
				('Salmonella sp.', 'CIP', 'Ciprofloxin', 16, 20),
				('Salmonella sp.', 'CTX', 'Cefotaxime', 23, 25),
				('Salmonella sp.', 'CXM', 'Cefuroxime', 15, 17),
				('Salmonella sp.', 'FOX', 'Cefoxtin', 15, 17),
				('Salmonella sp.', 'GEN', 'Gentamicin', 13, 14),
				('Salmonella sp.', 'IPM', 'Imipenem', 20, 22),
				('Salmonella sp.', 'MEZ', 'Mezlocillin', 18, 20),
				('Salmonella sp.', 'SXT', 'Trimethoprime / Sulfamethoxazole', 11, 15),
				('Shigella sp.', 'AMP', 'Ampicillin', 14, 16),
				('Shigella sp.', 'ATM', 'Aztreonam', 18, 20),
				('Shigella sp.', 'CAZ', 'Ceftazidime', 18, 20),
				('Shigella sp.', 'CEP', 'Cephalothin', 15, 17),
				('Shigella sp.', 'CIP', 'Ciprofloxin', 16, 20),
				('Shigella sp.', 'CTX', 'Cefotaxime', 23, 25),
				('Shigella sp.', 'CXM', 'Cefuroxime', 15, 17),
				('Shigella sp.', 'FOX', 'Cefoxtin', 15, 17),
				('Shigella sp.', 'GEN', 'Gentamicin', 13, 14),
				('Shigella sp.', 'IPM', 'Imipenem', 20, 22),
				('Shigella sp.', 'MEZ', 'Mezlocillin', 18, 20),
				('Shigella sp.', 'SXT', 'Trimethoprime / Sulfamethoxazole', 11, 15),
				('Pseudomonas sp.', 'AMC', 'Amoxixillin / Clavulanic acid', 14, 17),
				('Pseudomonas sp.', 'AMK', 'Amikacin', 15, 16),
				('Pseudomonas sp.', 'AMP', 'Ampicillin', 14, 16),
				('Pseudomonas sp.', 'ATM', 'Aztreonam', 18, 20),
				('Pseudomonas sp.', 'CAZ', 'Ceftazidime', 18, 20),
				('Pseudomonas sp.', 'CEP', 'Cephalothin', 15, 17),
				('Pseudomonas sp.', 'CHL', 'Chloramphenicol', 13, 17),
				('Pseudomonas sp.', 'CIP', 'Ciprofloxin', 16, 20),
				('Pseudomonas sp.', 'CLI', 'Clindamycin', 15, 20),
				('Pseudomonas sp.', 'CRB', 'Carbenicillin', 20, 22),
				('Pseudomonas sp.', 'CTX', 'Cefotaxime', 23, 25),
				('Pseudomonas sp.', 'CXM', 'Cefuroxime', 15, 17),
				('Pseudomonas sp.', 'CZX', 'Ceftizoxime', 22, 24),
				('Pseudomonas sp.', 'DOX', 'Doxycycline', 11, 13),
				('Pseudomonas sp.', 'ERY', 'Erythromycin', 14, 22),
				('Pseudomonas sp.', 'FOX', 'Cefoxtin', 15, 17),
				('Pseudomonas sp.', 'GEN', 'Gentamicin', 13, 14),
				('Pseudomonas sp.', 'IPM', 'Imipenem', 20, 22),
				('Pseudomonas sp.', 'MAN', 'Cefamandole', 15, 17),
				('Pseudomonas sp.', 'MEZ', 'Mezlocillin', 18, 20),
				('Pseudomonas sp.', 'MNO', 'Monocycline', 13, 15),
				('Pseudomonas sp.', 'NIT', 'Nitrofurantoin', 15, 16),
				('Pseudomonas sp.', 'NOR', 'Norfloxacin', 13, 16),
				('Pseudomonas sp.', 'OFX', 'Ofloxacin', 13, 15),
				('Pseudomonas sp.', 'OXA', 'Oxacillin', 18, 18),
				('Pseudomonas sp.', 'PEN', 'Penicillin G', 29, 29),
				('Pseudomonas sp.', 'PIP', 'Piperacillin', 18, 20),
				('Pseudomonas sp.', 'RIF', 'Rifampin', 17, 19),
				('Pseudomonas sp.', 'SSS', 'Sulfonamides', 13, 16),
				('Pseudomonas sp.', 'SXT', 'Trimethoprime / Sulfamethoxazole', 11, 15),
				('Pseudomonas sp.', 'TCC', 'Ticarcillin/Clavulanic acid', 15, 19),
				('Pseudomonas sp.', 'TCY', 'Tetracycline', 12, 14),
				('Pseudomonas sp.', 'TEC', 'Teicoplanin', 11, 13),
				('Pseudomonas sp.', 'TIC', 'Ticarcillin', 15, 19),
				('Pseudomonas sp.', 'TOB', 'Tobramycin', 13, 14),
				('Pseudomonas sp.', 'VAN', 'Vancomycin', 15, 15),
				('Non-fermenters', 'AMK', 'Amikacin', 15, 16),
				('Non-fermenters', 'ATM', 'Aztreonam', 18, 20),
				('Non-fermenters', 'CAZ', 'Ceftazidime', 18, 20),
				('Non-fermenters', 'CHL', 'Chloramphenicol', 13, 17),
				('Non-fermenters', 'CIP', 'Ciprofloxin', 16, 20),
				('Non-fermenters', 'CRB', 'Carbenicillin', 20, 22),
				('Non-fermenters', 'GEN', 'Gentamicin', 13, 14),
				('Non-fermenters', 'IPM', 'Imipenem', 20, 22),
				('Non-fermenters', 'MEZ', 'Mezlocillin', 18, 20),
				('Non-fermenters', 'PIP', 'Piperacillin', 18, 20),
				('Non-fermenters', 'SXT', 'Trimethoprime / Sulfamethoxazole ', 11, 15),
				('Non-fermenters', 'TOB', 'Tobramycin', 13, 14),
				('Haemophilus sp.', 'AMC', 'Amoxixillin / Clavulanic acid', 14, 17),
				('Haemophilus sp.', 'AMP', 'Ampicillin', 14, 16),
				('Haemophilus sp.', 'CIP', 'Ciprofloxin', 16, 20),
				('Haemophilus sp.', 'CTX', 'Cefotaxime', 23, 25),
				('Haemophilus sp.', 'CXM', 'Cefuroxime', 15, 17),
				('Haemophilus sp.', 'IPM', 'Imipenem', 20, 22),
				('Haemophilus sp.', 'SXT', 'Trimethoprime / Sulfamethoxazole ', 11, 15),
				('Campylobacter sp.', 'AMP', 'Ampicillin', 14, 16),
				('Campylobacter sp.', 'ATM', 'Aztreonam', 18, 20),
				('Campylobacter sp.', 'CAZ', 'Ceftazidime', 18, 20),
				('Campylobacter sp.', 'CEP', 'Cephalothin', 15, 17),
				('Campylobacter sp.', 'CIP', 'Ciprofloxin', 16, 20),
				('Campylobacter sp.', 'CTX', 'Cefotaxime', 23, 25),
				('Campylobacter sp.', 'CXM', 'Cefuroxime', 15, 17),
				('Campylobacter sp.', 'FOX', 'Cefoxtin', 15, 17),
				('Campylobacter sp.', 'GEN', 'Gentamicin', 13, 14),
				('Campylobacter sp.', 'IPM', 'Imipenem', 20, 22),
				('Campylobacter sp.', 'MEZ', 'Mezlocillin', 18, 20),
				('Campylobacter sp.', 'SXT', 'Trimethoprime / Sulfamethoxazole ', 11, 15),
				('Neisseria gonorrhoeae', 'AMC', 'Amoxixillin / Clavulanic acid', 14, 17),
				('Neisseria gonorrhoeae', 'AMP', 'Ampicillin', 14, 16),
				('Neisseria gonorrhoeae', 'CIP', 'Ciprofloxin', 16, 20),
				('Neisseria gonorrhoeae', 'CTX', 'Cefotaxime', 23, 25),
				('Neisseria gonorrhoeae', 'CXM', 'Cefuroxime', 15, 17),
				('Neisseria gonorrhoeae', 'ERY', 'Erythromycin', 14, 22),
				('Neisseria gonorrhoeae', 'IPM', 'Imipenem', 20, 22),
				('Neisseria gonorrhoeae', 'SXT', 'Trimethoprime / Sulfamethoxazole ', 11, 15),
				('Neisseria meningitidis', 'AMC', 'Amoxixillin / Clavulanic acid', 14, 17),
				('Neisseria meningitidis', 'AMK', 'Amikacin', 15, 16),
				('Neisseria meningitidis', 'AMP', 'Ampicillin', 14, 16),
				('Neisseria meningitidis', 'ATM', 'Aztreonam', 18, 20),
				('Neisseria meningitidis', 'CAZ', 'Ceftazidime', 18, 20),
				('Neisseria meningitidis', 'CEP', 'Cephalothin', 15, 17),
				('Neisseria meningitidis', 'CHL', 'Chloramphenicol', 13, 17),
				('Neisseria meningitidis', 'CIP', 'Ciprofloxin', 16, 20),
				('Neisseria meningitidis', 'CLI', 'Clindamycin', 15, 20),
				('Neisseria meningitidis', 'CRB', 'Carbenicillin', 20, 22),
				('Neisseria meningitidis', 'CTX', 'Cefotaxime', 23, 25),
				('Neisseria meningitidis', 'CXM', 'Cefuroxime', 15, 17),
				('Neisseria meningitidis', 'CZX', 'Ceftizoxime', 22, 24),
				('Neisseria meningitidis', 'DOX', 'Doxycycline', 11, 13),
				('Neisseria meningitidis', 'ERY', 'Erythromycin', 14, 22),
				('Neisseria meningitidis', 'FOX', 'Cefoxtin', 15, 17),
				('Neisseria meningitidis', 'GEN', 'Gentamicin', 13, 14),
				('Neisseria meningitidis', 'IPM', 'Imipenem', 20, 22),
				('Neisseria meningitidis', 'MAN', 'Cefamandole', 15, 17),
				('Neisseria meningitidis', 'MEZ', 'Mezlocillin', 18, 20),
				('Neisseria meningitidis', 'MNO', 'Monocycline', 13, 15),
				('Neisseria meningitidis', 'NIT', 'Nitrofurantoin', 15, 16),
				('Neisseria meningitidis', 'NOR', 'Norfloxacin', 13, 16),
				('Neisseria meningitidis', 'OFX', 'Ofloxacin', 13, 15),
				('Neisseria meningitidis', 'OXA', 'Oxacillin', 18, 18),
				('Neisseria meningitidis', 'PEN', 'Penicillin G ', 29, 29),
				('Neisseria meningitidis', 'PIP', 'Piperacillin', 18, 20),
				('Neisseria meningitidis', 'RIF', 'Rifampin', 17, 19),
				('Neisseria meningitidis', 'SSS', 'Sulfonamides', 13, 16),
				('Neisseria meningitidis', 'SXT', 'Trimethoprime / Sulfamethoxazole ', 11, 15),
				('Neisseria meningitidis', 'TCC', 'Ticarcillin/Clavulanic acid', 15, 19),
				('Neisseria meningitidis', 'TCY', 'Tetracycline ', 12, 14),
				('Neisseria meningitidis', 'TEC', 'Teicoplanin', 11, 13),
				('Neisseria meningitidis', 'TIC', 'Ticarcillin', 15, 19),
				('Neisseria meningitidis', 'TOB', 'Tobramycin', 13, 14),
				('Neisseria meningitidis', 'VAN', 'Vancomycin', 15, 15),
				('Anaerobes', 'AMC', 'Amoxixillin / Clavulanic acid', 14, 17),
				('Anaerobes', 'AMK', 'Amikacin', 15, 16),
				('Anaerobes', 'AMP', 'Ampicillin', 14, 16),
				('Anaerobes', 'ATM', 'Aztreonam', 18, 20),
				('Anaerobes', 'CAZ', 'Ceftazidime', 18, 20),
				('Anaerobes', 'CEP', 'Cephalothin', 15, 17),
				('Anaerobes', 'CHL', 'Chloramphenicol', 13, 17),
				('Anaerobes', 'CIP', 'Ciprofloxin', 16, 20),
				('Anaerobes', 'CLI', 'Clindamycin', 15, 20),
				('Anaerobes', 'CRB', 'Carbenicillin', 20, 22),
				('Anaerobes', 'CTX', 'Cefotaxime', 23, 25),
				('Anaerobes', 'CXM', 'Cefuroxime', 15, 17),
				('Anaerobes', 'CZX', 'Ceftizoxime', 22, 24),
				('Anaerobes', 'DOX', 'Doxycycline', 11, 13),
				('Anaerobes', 'ERY', 'Erythromycin', 14, 22),
				('Anaerobes', 'FOX', 'Cefoxtin', 15, 17),
				('Anaerobes', 'GEN', 'Gentamicin', 13, 14),
				('Anaerobes', 'IPM', 'Imipenem', 20, 22),
				('Anaerobes', 'MAN', 'Cefamandole', 15, 17),
				('Anaerobes', 'MEZ', 'Mezlocillin', 18, 20),
				('Anaerobes', 'MNO', 'Monocycline', 13, 15),
				('Anaerobes', 'NIT', 'Nitrofurantoin', 15, 16),
				('Anaerobes', 'NOR', 'Norfloxacin', 13, 16),
				('Anaerobes', 'OFX', 'Ofloxacin', 13, 15),
				('Anaerobes', 'OXA', 'Oxacillin', 18, 18),
				('Anaerobes', 'PEN', 'Penicillin G ', 29, 29),
				('Anaerobes', 'PIP', 'Piperacillin', 18, 20),
				('Anaerobes', 'RIF', 'Rifampin', 17, 19),
				('Anaerobes', 'SSS', 'Sulfonamides', 13, 16),
				('Anaerobes', 'SXT', 'Trimethoprime / Sulfamethoxazole ', 11, 15),
				('Anaerobes', 'TCC', 'Ticarcillin/Clavulanic acid', 15, 19),
				('Anaerobes', 'TCY', 'Tetracycline ', 12, 14),
				('Anaerobes', 'TEC', 'Teicoplanin', 11, 13),
				('Anaerobes', 'TIC', 'Ticarcillin', 15, 19),
				('Anaerobes', 'TOB', 'Tobramycin', 13, 14),
				('Anaerobes', 'VAN', 'Vancomycin', 15, 15),
				('Mycobacteria', 'AMC', 'Amoxixillin / Clavulanic acid', 14, 17),
				('Mycobacteria', 'AMK', 'Amikacin', 15, 16),
				('Mycobacteria', 'AMP', 'Ampicillin', 14, 16),
				('Mycobacteria', 'ATM', 'Aztreonam', 18, 20),
				('Mycobacteria', 'CAZ', 'Ceftazidime', 18, 20),
				('Mycobacteria', 'CEP', 'Cephalothin', 15, 17),
				('Mycobacteria', 'CHL', 'Chloramphenicol', 13, 17),
				('Mycobacteria', 'CIP', 'Ciprofloxin', 16, 20),
				('Mycobacteria', 'CLI', 'Clindamycin', 15, 20),
				('Mycobacteria', 'CRB', 'Carbenicillin', 20, 22),
				('Mycobacteria', 'CTX', 'Cefotaxime', 23, 25),
				('Mycobacteria', 'CXM', 'Cefuroxime', 15, 17),
				('Mycobacteria', 'CZX', 'Ceftizoxime', 22, 24),
				('Mycobacteria', 'DOX', 'Doxycycline', 11, 13),
				('Mycobacteria', 'ERY', 'Erythromycin', 14, 22),
				('Mycobacteria', 'FOX', 'Cefoxtin', 15, 17),
				('Mycobacteria', 'GEN', 'Gentamicin', 13, 14),
				('Mycobacteria', 'IPM', 'Imipenem', 20, 22),
				('Mycobacteria', 'MAN', 'Cefamandole', 15, 17),
				('Mycobacteria', 'MEZ', 'Mezlocillin', 18, 20),
				('Mycobacteria', 'MNO', 'Monocycline', 13, 15),
				('Mycobacteria', 'NIT', 'Nitrofurantoin', 15, 16),
				('Mycobacteria', 'NOR', 'Norfloxacin', 13, 16),
				('Mycobacteria', 'OFX', 'Ofloxacin', 13, 15),
				('Mycobacteria', 'OXA', 'Oxacillin', 18, 18),
				('Mycobacteria', 'PEN', 'Penicillin G ', 29, 29),
				('Mycobacteria', 'PIP', 'Piperacillin', 18, 20),
				('Mycobacteria', 'RIF', 'Rifampin', 17, 19),
				('Mycobacteria', 'SSS', 'Sulfonamides', 13, 16),
				('Mycobacteria', 'SXT', 'Trimethoprime / Sulfamethoxazole ', 11, 15),
				('Mycobacteria', 'TCC', 'Ticarcillin/Clavulanic acid', 15, 19),
				('Mycobacteria', 'TCY', 'Tetracycline ', 12, 14),
				('Mycobacteria', 'TEC', 'Teicoplanin', 11, 13),
				('Mycobacteria', 'TIC', 'Ticarcillin', 15, 19),
				('Mycobacteria', 'TOB', 'Tobramycin', 13, 14),
				('Mycobacteria', 'VAN', 'Vancomycin', 15, 15),
				('Fungi', 'AMC', 'Amoxixillin / Clavulanic acid', 14, 17),
				('Fungi', 'AMK', 'Amikacin', 15, 16),
				('Fungi', 'AMP', 'Ampicillin', 14, 16),
				('Fungi', 'ATM', 'Aztreonam', 18, 20),
				('Fungi', 'CAZ', 'Ceftazidime', 18, 20),
				('Fungi', 'CEP', 'Cephalothin', 15, 17),
				('Fungi', 'CHL', 'Chloramphenicol', 13, 17),
				('Fungi', 'CIP', 'Ciprofloxin', 16, 20),
				('Fungi', 'CLI', 'Clindamycin', 15, 20),
				('Fungi', 'CRB', 'Carbenicillin', 20, 22),
				('Fungi', 'CTX', 'Cefotaxime', 23, 25),
				('Fungi', 'CXM', 'Cefuroxime', 15, 17),
				('Fungi', 'CZX', 'Ceftizoxime', 22, 24),
				('Fungi', 'DOX', 'Doxycycline', 11, 13),
				('Fungi', 'ERY', 'Erythromycin', 14, 22),
				('Fungi', 'FOX', 'Cefoxtin', 15, 17),
				('Fungi', 'GEN', 'Gentamicin', 13, 14),
				('Fungi', 'IPM', 'Imipenem', 20, 22),
				('Fungi', 'MAN', 'Cefamandole', 15, 17),
				('Fungi', 'MEZ', 'Mezlocillin', 18, 20),
				('Fungi', 'MNO', 'Monocycline', 13, 15),
				('Fungi', 'NIT', 'Nitrofurantoin', 15, 16),
				('Fungi', 'NOR', 'Norfloxacin', 13, 16),
				('Fungi', 'OFX', 'Ofloxacin', 13, 15),
				('Fungi', 'OXA', 'Oxacillin', 18, 18),
				('Fungi', 'PEN', 'Penicillin G ', 29, 29),
				('Fungi', 'PIP', 'Piperacillin', 18, 20),
				('Fungi', 'RIF', 'Rifampin', 17, 19),
				('Fungi', 'SSS', 'Sulfonamides', 13, 16),
				('Fungi', 'SXT', 'Trimethoprime / Sulfamethoxazole ', 11, 15),
				('Fungi', 'TCC', 'Ticarcillin/Clavulanic acid', 15, 19),
				('Fungi', 'TCY', 'Tetracycline ', 12, 14),
				('Fungi', 'TEC', 'Teicoplanin', 11, 13),
				('Fungi', 'TIC', 'Ticarcillin', 15, 19),
				('Fungi', 'TOB', 'Tobramycin', 13, 14),
				('Fungi', 'VAN', 'Vancomycin', 15, 15),
				('Parasites', 'AMC', 'Amoxixillin / Clavulanic acid', 14, 17),
				('Parasites', 'AMK', 'Amikacin', 15, 16),
				('Parasites', 'AMP', 'Ampicillin', 14, 16),
				('Parasites', 'ATM', 'Aztreonam', 18, 20),
				('Parasites', 'CAZ', 'Ceftazidime', 18, 20),
				('Parasites', 'CEP', 'Cephalothin', 15, 17),
				('Parasites', 'CHL', 'Chloramphenicol', 13, 17),
				('Parasites', 'CIP', 'Ciprofloxin', 16, 20),
				('Parasites', 'CLI', 'Clindamycin', 15, 20),
				('Parasites', 'CRB', 'Carbenicillin', 20, 22),
				('Parasites', 'CTX', 'Cefotaxime', 23, 25),
				('Parasites', 'CXM', 'Cefuroxime', 15, 17),
				('Parasites', 'CZX', 'Ceftizoxime', 22, 24),
				('Parasites', 'DOX', 'Doxycycline', 11, 13),
				('Parasites', 'ERY', 'Erythromycin', 14, 22),
				('Parasites', 'FOX', 'Cefoxtin', 15, 17),
				('Parasites', 'GEN', 'Gentamicin', 13, 14),
				('Parasites', 'IPM', 'Imipenem', 20, 22),
				('Parasites', 'MAN', 'Cefamandole', 15, 17),
				('Parasites', 'MEZ', 'Mezlocillin', 18, 20),
				('Parasites', 'MNO', 'Monocycline', 13, 15),
				('Parasites', 'NIT', 'Nitrofurantoin', 15, 16),
				('Parasites', 'NOR', 'Norfloxacin', 13, 16),
				('Parasites', 'OFX', 'Ofloxacin', 13, 15),
				('Parasites', 'OXA', 'Oxacillin', 18, 18),
				('Parasites', 'PEN', 'Penicillin G ', 29, 29),
				('Parasites', 'PIP', 'Piperacillin', 18, 20),
				('Parasites', 'RIF', 'Rifampin', 17, 19),
				('Parasites', 'SSS', 'Sulfonamides', 13, 16),
				('Parasites', 'SXT', 'Trimethoprime / Sulfamethoxazole ', 11, 15),
				('Parasites', 'TCC', 'Ticarcillin/Clavulanic acid', 15, 19),
				('Parasites', 'TCY', 'Tetracycline ', 12, 14),
				('Parasites', 'TEC', 'Teicoplanin', 11, 13),
				('Parasites', 'TIC', 'Ticarcillin', 15, 19),
				('Parasites', 'TOB', 'Tobramycin', 13, 14),
				('Parasites', 'VAN', 'Vancomycin', 15, 15);
				";
    		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    		dbDelta($sql);
    		add_option('my_db_version', $my_products_db_version);
			}
	
}

register_activation_hook( __FILE__, 'SMART_AMR_Table_Check' );

?>