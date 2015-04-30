<?php
	session_start();

	class Gallery {

		private static $database;

		public function initializeDB() {
			# New mysqli Object for database communication
			self::$database = new mysqli("localhost", "tardissh_sdew", "3701!", "tardissh_dewey");

			# Kill the page is there was a problem with the database connection
			if ( self::$database->connect_error ):
				die( "Connection Error! Error: " . $this->database->connect_error );
			endif;
		}

		private static $imageTypes = array(
			'image/jpeg' => "jpeg",
			'image/gif' => "gif",
			'image/png' => "png",
		);

		// static function initializeDB() {
		// 	self::initializeDB();
		// }

		static function login($user,$password) {
			self::initializeDB();
		
			$query = "
				SELECT
					user,
					password
				FROM
					gallery_users
				WHERE
					user=? AND password=?
			";

			if ( $loginStatus = self::$database->prepare($quer) ):
				$loginStatus->bind_param(
					'ss',
					$user,$password
				);

				$loginStatus->execute();

				$loginStatus->store_results();

				if ( $loginStatus->num_rows == 1 ):
					$_SESSION["login"] = true;

					header("location: gallery.php");

					$loginStatus->close();
				else:

					$_SESSION["login"] = false;

					header("location: index.php?error");

					$loginStatus->close();
				endif;
			else:
				echo '<p>Problem preparing your database query.</p>';
			endif;
		}

		static function logout() {
			unset($_SESSION["login"]);

			header("location: index.php");
		}

		static function filetypeCheck($filetype) {
			if ( array_key_exists($filetype, self::$imageTypes) ):
				return true;
			else:
				return false;
			endif;
		}

		static function newGalleryImage($image,$caption) {
			self::initializeDB();

			$insert_query = "
				INSERT INTO
					gallery_images
					(caption, extension)
				VALUES
					(?,?)
			";

			$file_ext = self::$imageTypes[$image['type']];

			if ( $newImage = self::$database->prepare($insert_query) ):
				$newImage->bind_param(
					'ss',
					$caption, $file_ext
				);

				$newImage->execute();

				$imageID = self::$database->insert_id;
				echo $imageID;

				$filename = $imageID.".".$file_ext;
				
				copy($image['tmp_name'],"gallery_images/".$filename);

				//Get the Name Suffix on basis of the mime type
				$function_suffix = strtoupper($file_ext);
				//Build Function name for ImageCreateFromSUFFIX
				$function_to_read = 'ImageCreateFrom' . $function_suffix;
				//Build Function name for ImageSUFFIX
				$function_to_write = 'Image' . $function_suffix;

				//Get uploaded image dimensions
				$size = GetImageSize("gallery_images/" . $filename);
					if($size[0] > $size[1]):
						//Thumbnail size formula for wide images
						$thumbnail_width = 300;
						$thumbnail_height = (int)(300 * $size[1] / $size[0]);
						echo "Landscape";
					else:
						//Thumbnail size formula for wide images
						$thumbnail_width = (int)(300 * $size[0] / $size[1]);
						$thumbnail_height = 300;
						echo "Portrait";
					endif;

				$source_handle = $function_to_read("gallery_images/" .$filename);
				if ($source_handle):
					//Let's create a blank image for the thumbnail
					$destination_handle = 
						ImageCreateTrueColor($thumbnail_width, $thumbnail_height);

					//Now we resize it 
					ImageCopyResampled($destination_handle, $source_handle,
						0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $size[0], $size[1]);
				endif;

				// Let's save the thumbnail
				$function_to_write($destination_handle, "gallery_images/tb_" . $filename);

				header("location: gallery.php");

				$newImage->close();
				endif;
			}

			static function galleryDisplay() {
				self::initializeDB();

				$query = "
					SELECT
						id, caption, extension
					FROM
						gallery_images
					ORDER BY
						id ASC
				";

				if ( $galleryImages = self::$database->prepare($query) ):
					$galleryImages->execute();
					$galleryImages->store_result();
					$galleryImages->bind_result($id,$caption,$extension);

						if ( $galleryImages->num_rows == 0 ):
							echo "<p class='error'>No images currently in the gallery.</p>";
						else:
							while( $galleryImages->fetch()):
								echo "
									<div class='image hvr-float-shadow'>
											<div class='upperContent'>
												<a href='fullimage.php?id=$id&extension=$extension'>
													<img src='gallery_images/tb_$id.$extension' alt='$caption' /> 
												</a>
											</div>
											<div class='lowerContent'>
												<form class='caption' action='gallery.php' method='post'>
													<input type='hidden' name='ID' value='$id' />
													<input class='inputBox' type='text' name='caption' value='$caption'>
												</form>
												<form class='delete' action='gallery.php' method ='post'>
													<input type='hidden' name='ID' value='$id' />
													<input type='hidden' name='fileName' value='$id.$extension' />
													<input type='image' src='trash2.png' title='Delete Picture' name='Delete' alt='submit' height='50' >
												</form>
											</div>
									</div>
										
								";
							endwhile;
						endif;
					endif;
				}

					# Edit an existing Caption
			public function editCaption($id, $caption) {
				self::initializeDB();
				echo "In function of edit";
				echo $caption;
				$update_query = "
					UPDATE
						gallery_images
					SET
						caption=?
					WHERE
						id=?
					LIMIT 1
				";
				
				if ( $Caption_update = self::$database->prepare($update_query) ):
					$Caption_update->bind_param(
						'si',
						$caption, $id
					);
					
					$Caption_update->execute();
					
					$Caption_update->close();

				endif;
			}


			# Delete an existing Item from the database
			public function removeImage($id, $fileName) {
				self::initializeDB();

				$delete_query = "
					DELETE FROM
						gallery_images
					WHERE 
						id=?
					LIMIT 1
				";
				
				unlink("gallery_images/".$fileName);
				unlink("gallery_images/tb_".$fileName);

				if ( $ImageRemoval = self::$database->prepare($delete_query) ):
					$ImageRemoval->bind_param(
						'i',
						$id
					);
					
					$ImageRemoval->execute();
					
					$ImageRemoval->close();

				endif;
			}
	}
?>