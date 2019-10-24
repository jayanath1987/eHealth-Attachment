<?php
echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>";
echo "\n<html xmlns='http://www.w3.org/1999/xhtml'>";
echo "\n<head>";
echo "\n    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>";
echo "\n   <title>".$this->config->item('title')."</title>";
echo "\n   <link rel='icon' type='image/ico' href='". base_url()."images/mds-icon.png'>";
echo "\n   <link rel='shortcut icon' href='". base_url()."images/mds-icon.png'>";


echo "\n    <script type='text/javascript' src='".base_url()."/js/jquery.js' ></script>";
echo "\n    <script type='text/javascript' src='".base_url()."/js/jquery.cookie.js' ></script>";
echo "\n    <script type='text/javascript' src='".base_url()."/js/jquery.imgareaselect.pack.js' ></script>";
echo "\n    <script type='text/javascript' src='".base_url()."/js/script.js' ></script>";

echo "\n    <link href='". base_url()."/css/image_crop/imgareaselect-animated.css' rel='stylesheet' type='text/css' />";

echo "\n</head>";
?>
	<style>
	a, a h1{
		font-family: Georgia, "Times New Roman", Times, serif;
		font-size: 1.2em;
		color: #645348;
		font-style: italic;
		text-decoration: none;
		font-weight: 100;
		padding: 10px;
	}
	body{
		font: 12px Arial,Tahoma,Helvetica,FreeSans,sans-serif;
		text-transform: inherit;
		color: #582A00;
		background: #E7EDEE;
		width: 100%;
		margin: 0;
		padding: 0;
	}
	.wrap{
		width: 700px;
		margin: 10px auto;
		padding: 10px 15px;
		background: white;
		border: 2px solid #DBDBDB;
		-webkit-border-radius: 5px;
		-moz-border-radius: 5px;
		border-radius: 5px;
		text-align: center;
		overflow: hidden;
	}
	img#uploadPreview{
		border: 0;
		border-radius: 3px;
		-webkit-box-shadow: 0px 2px 7px 0px rgba(0, 0, 0, .27);
		box-shadow: 0px 2px 7px 0px rgba(0, 0, 0, .27);
		margin-bottom: 30px;
		overflow: hidden;
	}
	input[type="submit"]{
		border-radius: 10px;
		background-color: #61B3DE;
		border: 0;
		color: white;
		font-weight: bold;
		font-style: italic;
		padding: 6px 15px 5px;
		cursor: pointer;
	}
	</style>
</head>
<body>
   
    <div class="wrap">
	<!-- image preview area-->
	<img id="uploadPreview" style="display:none;"/>
	<h1>Upload patient portrait</h1>
	<!-- image uploading form -->
	<form action="<?php echo site_url("attach/save_portrait"); ?>" method="post" enctype="multipart/form-data">
         
            
            <script type="text/javascript" src="<?php echo base_url();?>js/cam/webcam.min.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/cam/script.js"></script>
<div id="my_camera"></div>
		<input type=button  id="snapshot" class="btn btn-success" value="Take Snapshot" onClick="take_snapshot()">
                <b>OR</b>
		<input id="uploadImage" type="file" accept="image/jpeg" name="image" />
		<input id="submit" type="submit" value="Upload">

		<!-- hidden inputs -->
		<input type="hidden" id="x" name="x" />
		<input type="hidden" id="y" name="y" />
		<input type="hidden" id="w" name="w" />
		<input type="hidden" id="h" name="h" />
		<input type="hidden" id="PID" name="PID" value="<?php echo $PID ?>"/>
	</form>
        <div id="results" class="well" ></div>
        
</div><!--wrap-->
<div class="container" id="Saved">
    <span id="loading"></span><img id="uploaded" src=""/>
</div>

</body>
</html>

<!-- Configure a few settings and attach camera -->
	<script language="JavaScript">
		Webcam.set({
			// live preview size
			width: 320,
			height: 240,
			
			// device capture size
			dest_width: 320,
			dest_height: 240,
			
			// final cropped size
			crop_width: 240,
			crop_height: 240,
			
			// format and quality
			image_format: 'jpeg',
			jpeg_quality: 90
		});
		Webcam.attach( '#my_camera' );

function take_snapshot() {
			// take snapshot and get image data
			Webcam.snap( function(data_uri) {
				// display results in page
				document.getElementById('results').innerHTML = 
					'<h2>Here is your image:</h2>' + 
					'<img id="base64image" src="'+data_uri+'"/>' +
                                        '<button onclick="SaveSnap();">Save Snap</button>';
                              			} );
                $("#submit").hide();
                $("#uploadImage").hide();
		}
                
function SaveSnap(){
    document.getElementById("loading").innerHTML="Saving, please wait...";
    var file =  document.getElementById("base64image").src;
    var pid=<?php echo $PID ?>;
    var formdata = new FormData();
    var x;
    var y;
    var w;
    var h;
    formdata.append("base64image", file);
    formdata.append("PID", pid);
    formdata.append("x", x);
    formdata.append("y", y);
    formdata.append("w", w);
    formdata.append("h", h);
    var ajax = new XMLHttpRequest();
    ajax.addEventListener("load", function(event) { uploadcomplete(event);}, false);
    ajax.open("POST", "<?php echo base_url(); ?>index.php/attach/save_snap",true);
    ajax.send(formdata);
    window.location = "<?php echo base_url(); ?>index.php/patient/view/"+pid;
        
}

function uploadcomplete(event){
    document.getElementById("loading").innerHTML="";
    var image_return=event.target.responseText;
    var showup=document.getElementById("uploaded").src=image_return;
}


	</script>



