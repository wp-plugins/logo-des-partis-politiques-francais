<?php
/*
Plugin Name: Logos des partis politiques fran&ccedil;ais
Plugin URI: http://ecolosites.eelv.fr/articles-evenement-logosppf/
Description: Widget qui affiche les logos et fait un lien vers les principaux partis politiques fran&ccedil;ais
Version: 1.0.0
Author: bastho // EÉLV
Author URI: http://ecolosites.eelv.fr/
License: CC BY-NC
Text Domain: logosppf
Domain Path: /languages
*/

function no_use(){
	__('Widget qui affiche les logos et fait un lien vers les principaux partis politiques fran&ccedil;ais','logosppf');
	__('Logos des partis politiques fran&ccedil;ais','logosppf');
}


add_action('wp_enqueue_scripts', 'logosppf_scripts');
function logosppf_scripts(){
	wp_enqueue_style('logosppf', plugins_url('/logosppf.css', __FILE__), false, null);
}

class logosppf_widget extends WP_Widget {
   public static $sizes;
   public static $logospath;
   public static $resizedpath;
   public static $exts;
   function logosppf_widget() {
  	  parent::WP_Widget(false, __( 'Logo parti', 'logosppf' ),array('description'=>__( 'Affiche les logos des principaux partis politiques fran&ccedil;ais', 'logosppf' )));
   	  self::$sizes=array(
   	  	'small'=>array(35,35),
   	  	'medium'=>array(75,75),
   	  	'large'=>array(125,125),
	  );
	  self::$logospath=plugin_dir_path(__FILE__).'logos/';
	  self::$resizedpath=plugin_dir_path(__FILE__).'logos/resized/';
	  self::$exts=array('gif','jpg','png');
   }
   function get($file){
   	  if($file!='' && is_file($file) && in_array(substr($file,-3),self::$exts)){
   	  	$filename=basename($file);
		return array(
			'abspath'=>plugin_dir_path(__FILE__).'/logos/'.$filename,
			'path'=>plugins_url('/logos/'.$filename, __FILE__),
			'file'=>$filename,
			'url'=>substr($filename,strpos($filename,'_')+1,-4),
			'name'=>substr($filename,0,strpos($filename,'_')),
		);
	}
	  return false;
   }
   function resize($file,$width='',$height=''){
	 $original_path = self::$logospath.$file;
   	 $resizedname = $file.'-'.$width.'-'.$height.'.'.substr($original_path,-3);
	 $resizedfile_path = self::$resizedpath.$resizedname;
	 $resizedfile_url = plugins_url('/logos/resized/'.$resizedname, __FILE__);
	 if(!is_file($original_path)){
   	  	return false;
   	  }
   	 $size = getimagesize($original_path);
	 $function = 'image'.str_replace('jpg','jpeg',self::$exts[$size[2]-1]);
	 $func = 'imagecreatefrom'.str_replace('jpg','jpeg',self::$exts[$size[2]-1]);
   	  
	  if(!is_file($resizedfile_path)){

		 
		  
		  if($size[2]==1){ $src = imagecreatefromgif($original_path); }
		  elseif($size[2]==2){ $src = imagecreatefromjpeg($original_path); }
		  elseif($size[2]==3){ $src = imagecreatefrompng($original_path); }
		  $neww=$size[0];
		  $newh=$size[1];
		  $ratio = 1;
		  if(!empty($width)){ 
			$jeveuxW = $width;
			if($neww > $width){
				$ratio = $neww/$width;
			}
		  }
		  if(!empty($height)){ 
			$jeveuxH = $height;
			if($newh > $height){
				$ratio = $newh/$height;
			}
		  }
		  $neww /= $ratio;      
		  $newh /= $ratio;
		  if($neww > $width){
			$ratio = $neww/$width;
			$neww /= $ratio;      
		    $newh /= $ratio;
		  }
		  
		  $des = imagecreatetruecolor ($neww, $newh) ;
		  if($des && $src){  
			imagecopyresampled( $des, $src, 0, 0, 0, 0, $neww, $newh, $size[0], $size[1]);
			if(!$function($des,$resizedfile_path)){
				return false;
			}
		  }
	  }
	  $color_tran=array('red'=>255,'green'=>255,'blue'=>255);
	  $size = getimagesize($resizedfile_path);
	  if(function_exists($func)){
			$im =$func($resizedfile_path);
			if($im){
				$color_index = imagecolorat($im, 1, 1);
				$color_tran = imagecolorsforindex($im, $color_index);
			}
	}
	return array('url'=>$resizedfile_url,'color'=>$color_tran,'size'=>$size);
	  
   }
   function liste(){
	   	$logos=array();
		if(is_dir(self::$logospath)){
			$files = scandir(self::$logospath);	
			foreach($files as $file){
				if(false !== $logo = self::get(self::$logospath.$file)){
					$logos[]=$logo;
				}
			}		
		}
		return $logos;
   }
   function widget($args, $instance) {
       extract( $args );
	   $parti = (isset($instance['parti']) && !empty($instance['parti'])) ? $instance['parti'] : '';
	   if(false !== $parti = self::get($parti)){
		       $size = (isset($instance['size']) && !empty($instance['size'])) ? $instance['size'] : 'medium';
		       $title = (isset($instance['title']) && !empty($instance['title'])) ? $instance['title'] : '';
			   
		       $width = (isset($instance['width']) && !empty($instance['width'])) ? $instance['width'] : self::$sizes[$size][0];
		       $height = (isset($instance['height']) && !empty($instance['height'])) ? $instance['height'] : self::$sizes[$size][1];
		       $link = (isset($instance['link']) && !empty($instance['link'])) ? $instance['link'] : 'http://'.$parti['url'];
		       	
				if(false !== $img = self::resize($parti['file'],$width,$height)){
					echo $args['before_widget'];
					if(!empty($title)){
						echo $args['before_title'];
						echo $title;
						echo $args['after_title'];
					}					
					echo '
					<a href="'.$link.'" target="_blank" class="lppf '.$size.'" style="'.((isset($instance['width']) && !empty($instance['width']))?'width:'.$width.'px;':'').''.((isset($instance['height']) && !empty($instance['height']))?'height:'.$height.'px;':'').'background:rgb('.$img['color']['red'].','.$img['color']['green'].','.$img['color']['blue'].')">
					<img src="'.$img['url'].'" alt="logo '.$parti['name'].'" style="margin:'.round(($height-$img['size'][1])/2).'px auto;"/>
					</a>';
					echo $args['after_widget'];
				}
	
		}		    
   }
   
   function update($new_instance, $old_instance) {
       return $new_instance;
   }

   function form($instance) {
    
     /* The Widget Title Itself */
		$title = (isset($instance['title']) && !empty($instance['title'])) ? $instance['title'] : '';

	/* The movement */
		$parti = (isset($instance['parti']) && !empty($instance['parti'])) ? $instance['parti'] : '';
	    
	  /* The size */
	  	$size = (isset($instance['size']) && !empty($instance['size'])) ? $instance['size'] : 'medium';
	  	$width = (isset($instance['width']) && !empty($instance['width'])) ? $instance['width'] : '';
	  	$height = (isset($instance['height']) && !empty($instance['height'])) ? $instance['height'] : '';
		
	
	  /* The link */
	  	$link = (isset($instance['link']) && !empty($instance['link'])) ? $instance['link'] : '';
		       
		$placeholdlink='';

      
       ?>
       <input type="hidden" id="<?php echo $this->get_field_id('title'); ?>-title" value="<?php echo $title; ?>">
       <p>
       <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Titre','logosppf'); ?>
       <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
       </label>
       </p>       
     
       <p>
       	<label for="<?php echo $this->get_field_id('parti'); ?>"><?php _e('Parti','logosppf'); ?>
       	<select  id="<?php echo $this->get_field_id('parti'); ?>" name="<?php echo $this->get_field_name('parti'); ?>">
       		<option value=''> </option>
       <?php 
	   	$logos = self::liste();
		foreach($logos as $logo){ ?>
       	<option value="<?=$logo['abspath']?>" <?php if($logo['abspath']==$parti){
       		 echo'selected';
			 $placeholdlink=$logo['url'];
		} ?>><?=$logo['name']?></option>
       <?php  }  ?>
       </select>
       </label>
       </p>
       
       <p>
       <label for="<?php echo $this->get_field_id('link'); ?>"><?php _e('Lien personnalis&eacute;','logosppf'); ?>
       <input class="widefat" id="<?php echo $this->get_field_id('link'); ?>" placeholder="http://<?php echo $placeholdlink; ?>" name="<?php echo $this->get_field_name('link'); ?>" type="text" value="<?php echo $link; ?>" />
       </label>
       </p>  
       
       <p>
       	<label for="<?php echo $this->get_field_id('size'); ?>"><?php _e('Taille','logosppf'); ?>
       	<select  id="<?php echo $this->get_field_id('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>">
       		<option value=''> </option>
       <?php 
	   	foreach(self::$sizes as $siz=>$dim){ ?>
       	<option value="<?=$siz?>" <?php if($siz==$size){ echo'selected';} ?>><?=$siz?> (<?=$dim[0]?>x<?=$dim[1]?>)</option>
       <?php  }  ?>
       </select>
       </label>
       </p>
       
       <p>
       	<?php _e('Boite personnalis&eacute;e :','logosppf'); ?>
       </p>
       <p>
       	<label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Largeur','logosppf'); ?>
       <input id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="number" value="<?php echo $width; ?>" />
       px
       </label>
       <label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Hauteur','logosppf'); ?>
       <input id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="number" value="<?php echo $height; ?>" />
       px
       </label>
       </p>
       
       <?php
   }

}

function register_logosppf(){
	register_widget('logosppf_widget');	
}

add_action('widgets_init', 'register_logosppf');