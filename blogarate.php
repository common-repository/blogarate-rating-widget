<?php

	/*
	Plugin Name: Blogarate User Style Rating Widget
	Plugin URI: http://www.blogarate.com/
	Description: Plugin that allows your readers to rate your posts 
	Version: 0.4.4
	Author URI: Blogarate Team
	*/
	
	
	function blogarate($content){
		$tag_arr = get_the_category();
		$tag = array();
		if(!empty($tag_arr)){
			while (list($key, $val) = each($tag_arr)) {
			array_push($tag,"'".trim($val->name)."'");
			}
		}
		$text = get_the_content();
		$text = str_replace("\n", "", $text);
		$text = str_replace("\r", "", $text);
		$text = substr($text,0,200);
		echo "<p><script type=\"text/javascript\">
				jQuery.blogarate_data['".get_permalink()."'] = new Object();
				jQuery.blogarate_data['".get_permalink()."'].postid ='".get_the_id()."';
				jQuery.blogarate_data['".get_permalink()."'].author = '".htmlspecialchars(trim(get_the_author()))."';
				jQuery.blogarate_data['".get_permalink()."'].title = '".htmlspecialchars(trim(get_the_title()))."';
				jQuery.blogarate_data['".get_permalink()."'].tags = [".htmlspecialchars(implode(",",$tag))."];
				jQuery.blogarate_data['".get_permalink()."'].content = \"".htmlspecialchars($text)."\";
				</script></p>";
		return $content."<div id=\"".get_permalink()."\" class=\"blogarate_rr wrap\"></div>";
	}
	
	function mt_add_pages() {
	    add_options_page('Blogarate', 'Blogarate', 8, __FILE__,'blogarate_admin_page');
    	}

	function blogarate_admin_page() {
		$directory_array = explode('/', dirname(__FILE__));
		$basedir = array_pop($directory_array);
		$blogarate_path = ABSPATH.PLUGINDIR."/$basedir/";
		wp_enqueue_script('jquery',ABSPATH.PLUGINDIR."/$basedir/jquery-1.2.6.min.js");
		$url = "/".PLUGINDIR."/$basedir/";
		$img = get_option('siteurl')."/".PLUGINDIR."/$basedir/";
		($_POST["blogarate_avatar"] != "")?update_option('blogarate_avatar', $_POST["blogarate_avatar"]):null;
		if($_POST["Submit"] == "Save Changes"){
			update_option('blogarate_usw', $_POST["usw"]);			
			foreach($_FILES as $key => $files){
				if($_FILES[$key]["size"] != 0){
					$fileinfo = pathinfo($_FILES[$key]["name"]);
					switch (strtolower($fileinfo["extension"])){
						case "jpg":
						case "jpeg":
						case "gif":
						case "png":
							if(function_exists("getimagesize")){
								$size = getimagesize($_FILES[$key]['tmp_name']);
								if($size[0] <= 100 && $size[1] <= 100){
									update_option('blogarate_iconwidth',$size[0]);
									update_option('blogarate_iconheight',$size[1]);
									$uploadvalid = true;	
								}else{
									$uploadvalid = false;
								}
								
							}else{
								if($_POST["icon_width"] != "" && $_POST["icon_height"] != "" && $_POST["icon_height"] <= 100 && $_POST["icon_width"] <= 100){
									update_option('blogarate_iconwidth',$_POST["icon_width"]);
									update_option('blogarate_iconheight',$_POST["icon_height"]);
									$uploadvalid = true;
								}else{
									$uploadvalid = false;
								}
							}
							if($uploadvalid){
								foreach(glob($blogarate_path.$key.".*") as $oldfile){
									unlink($oldfile);
								}								
								$filemessage .= (move_uploaded_file($_FILES[$key]['tmp_name'], $blogarate_path.$key.".".strtolower($fileinfo["extension"])))?"<b>$key Icon uploaded successfully</b><BR>":"<b>Error: Failed to upload $key Icon.</b>";
								update_option('blogarate_usw', "on");
								update_option('blogarate_'.$key,$key.".".strtolower($fileinfo["extension"]));
							}else{
								update_option('blogarate_usw', "");
								$filemessage .= "<b>Error: Invalid width and height for $key icon.</b><BR>";
							}
						break;
						default:
							$filemessage .= "<b>File type not supported for $key icon.</b><BR>";
						break;
					};
				};
			};
		
		}else if($_POST["Submit"] == "Reset to default"){
			update_option('blogarate_usw', "");
			update_option('blogarate_avatar', "0");
		}
		$template_path = $blogarate_path."setting_form.html";
		if(file_exists($template_path)){
			$str = @file_get_contents($template_path);
			$str = (get_option("blogarate_usw") == "on")?str_replace("%%USWCHECK%%","checked=\"checked\"",$str):str_replace("%%USWCHECK%%","",$str);
			$str = (function_exists("getimagesize"))?str_replace("<!-NOGDLIBARARY->","",$str):str_replace("<!-NOGDLIBARARY->",'<p><h4>Width: <input type="text" maxlength="3" size="3" name="icon_width" value="" id="icon"> Height: <input type="text" maxlength="3" size="3" name="icon_height" value="" id="icon"></h4>',$str);
			$str = str_replace("<!-filemessage->","$filemessage",$str);
			if(get_option("blogarate_avatar") == "1"){
				$str = str_replace("%%YESAVATAR%%","checked=\"checked\"",$str);
				$str = str_replace("%%NOAVATAR%%","",$str);
			}else{
				$str = str_replace("%%YESAVATAR%%","",$str);
				$str = str_replace("%%NOAVATAR%%","checked=\"checked\"",$str);
			}
			$str = (get_option("blogarate_Empty") != "")?str_replace("<!-IMGEMPTY->",'<img src="'.$img.get_option("blogarate_Empty").'" width="'.get_option('blogarate_iconwidth').'" height="'.get_option('blogarate_iconheight').'">',$str):$str;
			$str = (get_option("blogarate_Full") != "")?str_replace("<!-IMGFULL->",'<img src="'.$img.get_option("blogarate_Full").'" width="'.get_option('blogarate_iconwidth').'" height="'.get_option('blogarate_iconheight').'">',$str):$str;
			echo $str;
		}
		
	}
	
	function blogarate_header(){
		$directory_array = explode('/', dirname(__FILE__));
		$basedir = array_pop($directory_array);
		
		$url = get_option('siteurl')."/".PLUGINDIR."/$basedir/";
		$urlscript = "http://static.blogarate.com/wordpress/";
		$blogarate_path = ABSPATH.PLUGINDIR."/$basedir/";
		if(!file_exists($blogarate_path.get_option("blogarate_empty"))){
			update_option("blogarate_empty","");
			update_option("blogarate_usw","");
		}
		if(!file_exists($blogarate_path.get_option("blogarate_full"))){
			update_option("blogarate_full","");
			update_option("blogarate_usw","");
		}
		
		if(get_option("blogarate_usw") == "on"){
			if(get_option("blogarate_iconwidth") > 0 && get_option("blogarate_iconwidth") > 0 && get_option("blogarate_empty") != "" && get_option("blogarate_full") != ""){
				$style = "<style>.blogarate_icon_full {background:url(\"".$url.get_option("blogarate_full")."\");}.blogarate_icon_empty{background:url(\"".$url.get_option("blogarate_empty")."\");}.blogarate_dimension{width: ".get_option("blogarate_iconwidth")."px;height: ".get_option("blogarate_iconheight")."px;}</style>";
				echo $style;
			}
		};
		echo '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>';
		echo (get_option('blogarate_avatar'))?'<script type="text/javascript">jQuery.blogarate_data_avatar = true;</script>':'<script type="text/javascript">jQuery.blogarate_data_avatar = false;</script>';
		echo '<script type="text/javascript" src="'.$urlscript.'jquery.blogarate.js"></script>';
	}
	
	
	add_filter('the_content', 'blogarate', 10);
	add_action('admin_menu', 'mt_add_pages');
	add_action('wp_head', 'blogarate_header');
?>
