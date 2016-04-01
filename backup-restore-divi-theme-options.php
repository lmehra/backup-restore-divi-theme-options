<?php
/**
 * Plugin Name: Backup/Restore Divi Theme Options
 * Description: Backup & Restore your Divi Theme Options.
 * Theme URI: https://github.com/SiteSpace/backup-restore-divi-theme-options
 * Author: Divi Space
 * Author URI: http://divispace.com
 * Version: 1.0.2
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Tags: divi, theme options, theme settings, divi theme options, divi options, divi theme settings, divi settings
 * Text Domain: backup-restore-divi-theme-options
 */


class backup_restore_divi_theme_options {

	function backup_restore_divi_theme_options() {  // constructor to be called automatically when object of the class is created
		add_action('admin_menu', array(&$this, 'admin_menu')); // add plugin options in the dashboard admin menu
	}
	function admin_menu() {

		$page = add_submenu_page('tools.php', 'Backup/Restore Theme Options', 'Backup/Restore Theme Options', 'manage_options', 'backup-restore-divi-theme-options', array(&$this, 'options_page')); // Creates the submenu inside the tools options in the dashboard

		add_action("load-{$page}", array(&$this, 'import_export'));  // loads the above created page

		add_submenu_page( 'et_divi_options',__( 'Backup/Restore Theme Options', 'Divi' ), __( 'Backup/Restore Theme Options', 'Divi' ), 'manage_options', 'tools.php?page=backup-restore-divi-theme-options', 'backup-restore-divi-theme-options' );  // adds the submenu inside the divi option in the dashboard

	}
	function import_export() {
		if (isset($_GET['action']) && ($_GET['action'] == 'download')) {
			header("Cache-Control: public, must-revalidate"); // used to stop the document to be cached
			header("Pragma: hack");  // used to stop the document to be cached
			header("Content-Type: text/plain");  // specifies the content type
			header('Content-Disposition: attachment; filename="divi-theme-options-'.date("dMy").'.dat"');  // defines the contents of the file to be downloaded
			echo serialize($this->_get_options());
			die();
		}
		if (isset($_POST['upload']) && check_admin_referer('shapeSpace_restoreOptions', 'shapeSpace_restoreOptions')) { // upload the backup file
			if ($_FILES["file"]["error"] > 0) { // if no file
				// error
			} else {
				$options = unserialize(file_get_contents($_FILES["file"]["tmp_name"])); // to get the contents of the file
				if ($options) {
					foreach ($options as $option) {
						update_option($option->option_name, unserialize($option->option_value));  // update the option/value pair to restore the backup
					}
				}
			}
			wp_redirect(admin_url('tools.php?page=backup-restore-divi-theme-options'));  // redirect after completion
			exit;
		}
	}

	//defines the structure of the page
	function options_page() { ?>

		<div class="wrap"><!-- wrapper start-->
			<?php screen_icon(); ?><!-- display screen icon -->
			<h2>Backup/Restore Theme Options</h2> <!-- heading -->
			<form action="" method="POST" enctype="multipart/form-data"> <!-- form start -->
				<style>#backup-restore-divi-theme-options td { display: block; margin-bottom: 20px; }</style> <!-- styling of the page -->
				<table id="backup-restore-divi-theme-options">
					<tr>
						<td>
							<h3>Backup/Export</h3>
							<p>Here are the stored settings for the current theme:</p>
							<p><textarea disabled class="widefat code" rows="20" cols="100" onclick="this.select()"><?php echo serialize($this->_get_options()); ?></textarea></p>  <!-- option name and option value in the serialized form -->
							<p><a href="?page=backup-restore-divi-theme-options&action=download" class="button-secondary">Download as file</a></p> <!-- redirect to the same page with action download -->
						</td>
						<td>
							<h3>Restore/Import</h3>
							<p><label class="description" for="upload">Restore a previous backup</label></p>
							<p><input type="file" name="file" /> <input type="submit" name="upload" id="upload" class="button-primary" value="Upload file" /></p><!-- input field for the file to upload -->
							<?php if (function_exists('wp_nonce_field')) wp_nonce_field('shapeSpace_restoreOptions', 'shapeSpace_restoreOptions'); ?><!-- check if nonce exist or not -->
						</td>
						<td>
							<h3>Upload Image</h3>
							<p><label class="description">Upload an image</label></p>
							<p><input type="file" name="fileToUpload" id="fileToUpload" />
							<input type="submit" name="upload_button" id="upload_button" class="button-primary" value="Upload Image" /></p>
						</td>
					</tr>
				</table>
			</form>
		</div>
		<?php
		if(isset($_POST['upload_button'])){
			$file = basename($_FILES["fileToUpload"]["name"]);
			// File upload
			$arr = explode(".", $file);
			if($arr[1]=='jpg' || $arr[1]=='jpeg' || $arr[1]=='png' || $arr[1]=='bmp' || $arr[1]=='gif'){
				$upload = wp_upload_bits($_FILES["fileToUpload"]["name"], null, file_get_contents($_FILES["fileToUpload"]["tmp_name"]));
			}else{
				$message = "Only PDF, PPT and DOC/DOCX files are allowed to upload.";
				echo "<script type='text/javascript'>alert('$message');</script>";
			}
		}
	}
	function _display_options() {
		$options = unserialize($this->_get_options()); //get PHP values from option name and option value from database 
	}
	function _get_options() {
		global $wpdb;  // global variable used to connect with the db
		return $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name = 'et_divi'"); // edit 'shapeSpace_options' to match theme options, query to get the data from database
	}
}
new backup_restore_divi_theme_options();  // create the new object of the class backup_restore_divi_theme_options
?>
