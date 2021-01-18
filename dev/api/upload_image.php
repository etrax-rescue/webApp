<?php
function resizeImage($resourceType,$image_width,$image_height,$resizeWidth) {
	$resizeHeight = ($image_height * $resizeWidth)/$image_width;
    $imageLayer = imagecreatetruecolor($resizeWidth,$resizeHeight);
	imagecopyresampled($imageLayer,$resourceType,0,0,0,0,$resizeWidth,$resizeHeight, $image_width,$image_height);
	echo 'resized';
	return $imageLayer;
}
if(is_array($_FILES)) {
	if(isset($_POST['base64img'])){
		$base64string = str_replace('data:image/png;base64,', '', $_POST['base64img']);
		$fileName = base64_decode($base64string);
		$uploadImageType = IMAGETYPE_JPEG;
		$base64size = explode(',',$_POST['base64size']);
		$sourceImageWidth = $base64size[0];
		$sourceImageHeight = $base64size[1];
		$fileExt = 'jpg';
		$resizeFileName = $_POST['name'] ? $_POST['name'] : 'poi_'.time();
	}else{
		$fileName = $_FILES['upload_image']['tmp_name'];
		$sourceProperties = getimagesize($fileName);
		$resizeFileName = $_POST['name'] ? $_POST['name'] : $fileName;
		$fileExt = pathinfo($_FILES['upload_image']['name'], PATHINFO_EXTENSION);
		$uploadImageType = $sourceProperties[2];
		$sourceImageWidth = $sourceProperties[0];
		$sourceImageHeight = $sourceProperties[1];
	}
	$uploadPath = '../../../secure/data/'.$_POST["eid"].'/';
	if(!is_dir($uploadPath)){
		mkdir($uploadPath, 0777);
	}
	switch ($uploadImageType) {
		case IMAGETYPE_JPEG:
			if(isset($_POST['base64img'])){
				$resourceType = imagecreatefromstring($fileName);
			}else{
				$resourceType = imagecreatefromjpeg($fileName);
			} 
			$imageLayer = resizeImage($resourceType,$sourceImageWidth,$sourceImageHeight,300);
			imagejpeg($imageLayer,$uploadPath.$resizeFileName.'.jpg');
			$imageLayer = resizeImage($resourceType,$sourceImageWidth,$sourceImageHeight,800);
			imagejpeg($imageLayer,$uploadPath.$resizeFileName.'_big.jpg');
			break;
		case IMAGETYPE_PNG:
			if(isset($_POST['base64img'])){
				$resourceType = imagecreatefromstring($fileName);
			}else{
				$resourceType = imagecreatefromjpeg($fileName);
			}  
			$imageLayer = resizeImage($resourceType,$sourceImageWidth,$sourceImageHeight,300);
			imagepng($imageLayer,$uploadPath.$resizeFileName.'.png');
			$imageLayer = resizeImage($resourceType,$sourceImageWidth,$sourceImageHeight,800);
			imagepng($imageLayer,$uploadPath.$resizeFileName.'_big.png');
			break;
		default:
			$imageProcess = 0;
			break;
	}
	imagedestroy($imageLayer);
	$imageProcess = 1;
	if($imageProcess == 1){
		echo 'works';
	}else{
		echo 'failed';
	}
	$imageProcess = 0;
}
?>