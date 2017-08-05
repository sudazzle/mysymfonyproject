<?php
namespace AppBundle\CustomClasses;

class Thumbnail
{
	public $thumbnail_width;
	public $path_to_thumbs_directory;
	public $uploadDirectory;
	public $message;
	public $status;		

	function setMessage($msg)
	{
		$this->message = $msg;
	}

	function setStatus($status)
	{
		$this->status = $status;
	}
	
	function setUploadDirectory($directoryName)  
	{
		$this->uploadDirectory = $directoryName;
	}

	function setThumbsize($thumbsize)
	{
		$this->thumbnail_width = $thumbsize;
	}
		
	function setThumbdir($thumbdir)
	{
		$this->path_to_thumbs_directory = $thumbdir;
	}
	function createThumbnail($filename) {		
		$fileparts = pathinfo($filename);
     	$extension = strtolower($fileparts['extension']);
		//echo $extension;
		switch($extension)
		{
			case "png":
				$im = imagecreatefrompng($this->uploadDirectory . $filename);
				break;	
			case "jpg":
			case "jpeg":
				$im = imagecreatefromjpeg($this->uploadDirectory . $filename);
				break;			
			default:
				break;				
		}
				 
		$ox = imagesx($im); // returns width of the image
		$oy = imagesy($im); // returns height of the image
		 
		$nx = $this->thumbnail_width; // width
		$ny = floor($oy * ($this->thumbnail_width / $ox)); //height
		 
		$nm = imagecreatetruecolor($nx, $ny);		
	
		if($extension == "png")
		{
			imagealphablending($nm, false);
			imagesavealpha($nm,true);
			$transparent = imagecolorallocatealpha($nm, 255, 255, 255, 127);
			imagefilledrectangle($nm, 0, 0, $nx, $ny, $transparent);
		}

		imagecopyresampled($nm, $im, 0, 0, 0, 0, $nx, $ny, $ox, $oy);
		if(!file_exists($this->path_to_thumbs_directory)) 
		{
		  	if(!mkdir($this->path_to_thumbs_directory)) 
			{
			   	return;
		  	}
		}
		   
		switch ($extension)
		{        
			case "jpg": 
			case "jpeg":
				if(imagejpeg($nm, $this->path_to_thumbs_directory . $filename))
				{
					$this->setMessage('File ' . $filename . ' uploaded successfully.' );
					$this->setStatus("success");
					return true; 
				}
				else
				{
					$this->setMessage('Error uploading file. Please Try Later.');
					$this->setStatus("error");
					return false;
				}
				break; 
			case "png": 
				if(imagepng($nm, $this->path_to_thumbs_directory . $filename, 0))
				{
					$this->setMessage('File ' . $filename . ' uploaded successfully.' );
					$this->setStatus("success");
					 return true;
				}
				else
				{
					$this->setMessage('Error uploading file. Please Try Later.');
					$this->setStatus("error");
					return false; 
				}
				break;
			default: break;					
		}		
    }
}