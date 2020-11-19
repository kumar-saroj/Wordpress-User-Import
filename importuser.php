<?php
add_action("admin_menu", "plugin_menu");
function plugin_menu()
{
		add_submenu_page(
		'users.php',
		__( 'Import User', 'menu-test' ),
		__( 'Import User', 'menu-test' ),
		'manage_options',
		'import-user',
		'import_page'
	);
}

function import_page()
{
global $wpdb;

// Table name
$tablename = $wpdb->prefix."users";

if(isset($_POST['butimport'])){

  // File extension
  $extension = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);

  // If file extension is 'csv'
  if(!empty($_FILES['import_file']['name']) && $extension == 'csv'){

    $totalInserted = 0;

    // Open file in read mode
    $csvFile = fopen($_FILES['import_file']['tmp_name'], 'r');

    fgetcsv($csvFile); // Skipping header row

    // Read file
    while(($csvData = fgetcsv($csvFile)) !== FALSE){
      $csvData = array_map("utf8_encode", $csvData);

      // Row column length
      $dataLen = count($csvData);

      // Skip row if length != 4
      if( !($dataLen == 2) ) continue;

      // Assign value to variables
      $name = trim($csvData[0]);
      $email = trim($csvData[1]);

      // Check record already exists or not
      $cntSQL = "SELECT count(*) as count FROM {$tablename} where user_email='".$email."'";
      $record = $wpdb->get_results($cntSQL, OBJECT);

      if($record[0]->count==0){

        // Check if variable is empty or not
        if(!empty($name) && !empty($email)) {
		$random_password = wp_generate_password(8,false);
          // Insert Record
          $user_data = array(
                'user_login' => $email,
                'user_pass' => $random_password,
                'user_email' => $email,
                'first_name' => $name,
                'role' => 'member'
            );
			$user_id = wp_insert_user( $user_data ) ;

          if($user_id > 0){
            $totalInserted++;
          }
        }

      }

    }
    echo "<h3 style='color: green;'>Total record Inserted : ".$totalInserted."</h3>";


  }else{
    echo "<h3 style='color: red;'>Invalid Extension</h3>";
  }

}
$blogusers = get_users( [ 'role__in' => [ 'member' ] ] );
	?>
	<h2>IMPORT USERS</h2>

<!-- Form -->

<div style="margin-bottom: 20px; border: 3px dashed rgba(102, 102, 102, 0.31);padding: 20px;">
<form method='post' action='<?= $_SERVER['REQUEST_URI']; ?>' enctype='multipart/form-data'>
  <input type="file" name="import_file" >
  <input type="submit" name="butimport" value="Import User" class="button button-primary"><span style="color:#f00; padding-left:10px; font-size: 13px;">(UPLOAD ONLY .csv FILE)</span> <a style="float:right;" href="users.php" class="button btn-primary">View All Users</a>
</form>

</div>
<div style="margin-bottom:10px;">
	<img src="<?php bloginfo('template_directory'); ?>/assets/img/format.png" alt="Format" title="Format" />
</div>
<table class="wp-list-table widefat fixed striped table-view-list posts" style="display:none">
	<thead>
		<tr>
			<th>Name</th>
			<th>Email</th>
			<th>Role</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($blogusers as $user){  //var_dump($user); ?>
		<tr>
			<td><?php echo $user->display_name; ?></td>
			<td><?php echo $user->user_email; ?></td>
			<td><?php echo $user->roles[0]; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>

	<?php
}

