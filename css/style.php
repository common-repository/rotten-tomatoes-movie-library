<?php 
$data =  get_option( 'my_option_name' );
?>
<style type="text/css">
.bx-wrapper{background-color:#<?php echo $data['bg_color'];?>;}
.bx-wrapper .tslider blockquote{color:#<?php echo $data['font_color'];?>;}
.bx-wrapper .tslider blockquote footer{color:#<?php echo $data['footer_font_color'];?>;}
.bx-wrapper .tslider blockquote footer a{color:#<?php echo $data['footer_link_color'];?>;}
<?php 
if(isset($data['rnd_image'])){?>
.tslider .ts_thumb{
	border-radius:8em !important;
	-moz-border-radius:8em !important;
	-webkit-border-radius:8em !important;
}
<?php }?>
</style>