<?php
// Call From Get DVD List Menu Link
function nt_fn_fetch_dvd_list()
{
	if(!current_user_can('manage_options')){
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}//end if user is allowed.
	
	
	global $rottenTomatoes;
	if(isset($_POST['movie_id'])) {
			
	// Movie ID Loop 		
    foreach($_POST['movie_id'] as $mId) {
		
		$movie_id = $mId;
		
		try {
			$result = $rottenTomatoes->getMovieInfo($movie_id);
			$search_results = json_decode(json_encode($result),false);
			
			if (!isset($search_results->title))
			{
				return false;
			}
			
			// Create post object
			$movie_post = array(
				'post_title'    => $search_results->title,
				'post_content'  => $search_results->synopsis,
				'post_status'   => 'publish',
				'post_author'   => 1,
				'post_category' => array(),
				'post_type' 	  => 'movies'
			);
			
			$exist_data = get_page_by_title($search_results->title, 'OBJECT', 'movies');
			if ($exist_data)
			{
				$movie_post['ID'] = $exist_data->ID;
				wp_update_post($movie_post);
				$post_id = $exist_data->ID;
			}
			else
			{
				// Insert the post into the database
				$post_id = wp_insert_post($movie_post, null );
			}
			// Upload poster
			$image_url = str_replace("_tmb","_ori",$search_results->posters->original);
			$upload_dir = wp_upload_dir();
			$image_data = file_get_contents($image_url);
			$filename = basename($image_url);
			if(wp_mkdir_p($upload_dir['path']))
			{
				$file = $upload_dir['path'] . '/' . $filename;
			}
			else
			{
				$file = $upload_dir['basedir'] . '/' . $filename;
			}
			file_put_contents($file, $image_data);
			
			$wp_filetype = wp_check_filetype($filename);
			
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title' => sanitize_file_name($filename),
				'post_content' => '',
				'post_status' => 'inherit'
			);
			
			$attach_id = wp_insert_attachment( $attachment, $file, $post_id );
			
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
			
			wp_update_attachment_metadata( $attach_id, $attach_data );
			
			set_post_thumbnail( $post_id, $attach_id );
			
			// Create genres
			$terms = array();
			if(isset($search_results->genres)){
				foreach($search_results->genres as $term):
					$t_exists = term_exists( $term, 'genres' );
					if($t_exists==""):
						$t = wp_insert_term( $term, 'genres' );
						$terms[] = $t['term_id'];
					else:
						$terms[] = $t_exists['term_id'];
					endif;
				endforeach;
				
				//add genres
				wp_set_post_terms( $post_id, $terms, 'genres' );
			}
			
			// Create cast
			$castterms = array();
			if(isset($search_results->genres)){
				foreach($search_results->abridged_cast as $castterm):
					$t_exists = term_exists( $castterm->name, 'cast' );
					if($t_exists==""):
						$t = wp_insert_term( $castterm->name, 'cast');
						$castterms[] = $t['term_id'];
					else:
						$castterms[] = $t_exists['term_id'];
					endif;
				endforeach;
				
				//add cast
				wp_set_post_terms( $post_id, $castterms, 'cast' );
			}
			
			$director = array();
			foreach(json_decode(json_encode($search_results->abridged_directors), true) as $direct)
			{
				$director[] = $direct['name'];
			}
			
			update_post_meta( $post_id, 'directed_by', implode(",",$director));
			//add release data
			update_post_meta( $post_id, 'year', $search_results->year);
			update_post_meta( $post_id, 'release_date', $search_results->release_dates->theater);
			update_post_meta( $post_id, 'on_dvd', $search_results->release_dates->dvd);
			update_post_meta( $post_id, 'critics_rating', $search_results->ratings->critics_rating);
			update_post_meta( $post_id, 'critics_score', $search_results->ratings->critics_score);
			update_post_meta( $post_id, 'audience_rating', $search_results->ratings->audience_rating);
			update_post_meta( $post_id, 'audience_score', $search_results->ratings->audience_score);
			update_post_meta( $post_id, 'studio', $search_results->studio);
			update_post_meta( $post_id, 'url', $search_results->links->alternate);
			$msg = "Updated Successfully";
		} catch (Exception $e) {
			$msg  = "Update Failed"; 
		}
		
		
	} // End mID Foreach Loop
	
	} // End POST If Condition
    if (isset( $msg) && !empty($msg)):
    	echo '<div class="updated fade"><p>' . __($msg) . '</p></div>';			
    endif;
?>

<div class="wrap">
  <h2>Get DVD List</h2>
  <form method="POST" id="frmMovies" name="frmMovies">
    <div class="movie_container" style="margin-top:10px;">
      <div class="tablenav top">
        <div class="alignleft actions bulkactions">
          <select name="movie_type" id="movie_type">
            <option value="top_rentals">Top Rentals</option>
            <option value="current_releases">Current Release DVDs</option>
            <option value="new_releases">New Release DVDs</option>
            <option value="upcoming">Upcoming DVDs</option>
          </select>
          <input type="button" name="get_movies" id="get_movies" class="button" value="GET DVDs" />
        </div>
        <div class="alignright actions bulkactions">
          <input type="button" value="Import" class="button-primary action" id="import" name="import" />
        </div>
        <div class="alignleft tablenav-pages one-page"><span class="displaying-num"></span>&nbsp;</div>
      </div>
      <table id="tbl_movie_list" class="wp-list-table widefat fixed pages">
        <thead>
          <tr>
            <th style="" class="manage-column column-cb check-column" id="cb" scope="col"><label for="cb-select-all-1" class="screen-reader-text">Select All</label>
              <input type="checkbox" id="cb-select-all-1"></th>
            <th scope="col">Thumbnail</th>
            <th scope="col">Title</th>
            <th scope="col">Year</th>
            <th scope="col">In Theater</th>
            <th scope="col">Ratings</th>
          </tr>
        </thead>
        <tbody id="the-list">
        </tbody>
      </table>
    </div>
    <div class="loader" style="margin:10% auto;display:none;" align="center"><img src="<?php echo plugins_url( 'images/loading.gif', dirname(__FILE__) ) ?>" /></div>
  </form>
</div>
<?php
if (isset($_POST['exnet_sb'])) {
	if (isset($_POST['movie_type'])) {
		
		//getData($_POST['movie_type']);
	} //isset($_POST['movie_type'])
} //isset($_POST['exnet_sb'])
?>
<?php global $RTApiKey;?>
<script type="text/javascript">
// construct the uri with our apikey
jQuery(document).ready(function() {	
	var apikey = "<?php echo $RTApiKey;?>";
	var baseUrl = "http://api.rottentomatoes.com/api/public/v1.0/lists/dvds/";
		// Get Movies
		jQuery("#get_movies").click(function(e) {
			jQuery("#tbl_movie_list #the-list").html('');				
			var mType = jQuery("#movie_type").val();
			var moviesUrl = baseUrl + mType+'.json?limit=50&apikey=' + apikey;
			// send off the query
			jQuery.jsonp({
				url: moviesUrl,
				callbackParameter: "callback",
				success: searchCallback,
				error:function(){
					jQuery("#tbl_movie_list #the-list").html('<tr class="no-items"><td colspan="6" class="colspanchange">Account Inactive.</td></tr>');
				},
				beforeSend: function( xhr ) {
					jQuery(".loader").show();
				},
				complete: function (XMLHttpRequest, textStatus) {
					jQuery(".loader").hide(); //this was fired
				}
			});
		});
		
		// Import Movies
		jQuery("#import").click(function(e) {			
			jQuery("#frmMovies").submit();
			return true;
		});
		
		jQuery(document).on("click",".import",function(e) {
			jQuery("input[type=checkbox]:input[value="+jQuery(this).attr("rel")+"]").attr("checked","true");
			jQuery("#import").trigger("click");
		});
	});
	 
// Get Movies callback for when we get back the results
function searchCallback(data) {
	if(data.error)
	{
		jQuery("#tbl_movie_list #the-list").html('<tr class="no-items"><td colspan="6" class="colspanchange">'+data.error+'</td></tr>');
	}
	else
	{
		var tbldata = "";
		var movies = data.movies;
		jQuery(".displaying-num").html(data.movies.length+" item(s)");
		jQuery.each(movies, function(index, movie) {
			var cls ="alternate";
			if(index%2)
			{
				cls = "";
			}
			tbldata += '<tr class="'+cls+'"><th class="check-column" scope="row"><input type="checkbox" value="'+movie.id+'" name="movie_id[]" id="cb-select-2"></th><td><img src="' + movie.posters.thumbnail + '" /></td><td>'+movie.title+'<div class="row-actions"> | <span class="import"><a title="Import this Movie" href="javascript:void(0);" class="import" rel="'+movie.id+'">Import</a></span></div></td><td>'+movie.year+'</td><td>'+movie.release_dates.theater+'</td><td>'+movie.ratings.critics_score+'%</td></tr>';
		});
	}
	jQuery("#tbl_movie_list #the-list").html(tbldata);
}
</script>

<?php
}
?>
