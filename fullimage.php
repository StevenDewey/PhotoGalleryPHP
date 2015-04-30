<!doctype html>
<html>
<head>
	<title>Full Image</title>
</head>
<body>

	<?php
		$id = $_GET['id'];
		$extension = $_GET['extension'];
		// $caption = $_GET['caption']
		echo "
			<img src='gallery_images/$id.$extension' />
		";
	?>

</body>
</html>