<?php
class Image {
    function resizer_main($image, $w, $h,$oid='',$namenew=''){
        global $use_imagecreatetruecolor, $use_imagecopyresampled, $IMG_ROOT, $JPG_QUALITY, $HTTP_POST_FILES,$config,$settings_profile,$config;
        $imageini=$image;
        $image_name = $HTTP_POST_FILES [$image]["name"];
        $image = $HTTP_POST_FILES [$image]["tmp_name"];

        if(trim($image) == "" || trim($image) =="none") return false;

        if (!extension_loaded ("gd") or $imageini=='input_banner' or $imageini=='input_logo') {
            $file_name = $this->make_filename($oid);
            if (!copy ( $image, $IMG_ROOT."$file_name" ))
                return false;
        }
        else{
            $arr_img = $this->image_from_upload($image);
            if( $arr_img["w"] != $w && $arr_img["h"] != $h){
                $wh        = $this->get_sizes($arr_img["w"], $arr_img["h"], $w, $h);
                $img_res = $this->img_get_resized(
                    $arr_img["img"],
                    $arr_img["w"],$arr_img["h"],
                    $wh["w"],$wh["h"],
                    $use_imagecreatetruecolor,
                    $use_imagecopyresampled);
            } else {
                $img_res = $arr_img["img"];
                $wh        = $this->get_sizes($arr_img["w"], $arr_img["h"], $w, $h);
            }
            $file_name = $this->make_filename($oid,$namenew);

            if ($config['add_image_text_'] and ($config['add_image_text_color_to_thumb'] or $wh["w"]>$settings_profile['thumbnail_width'])){

                $this->imagestringbox($img_res, $config['add_image_text_font'], $config['add_image_text_width_position'],$config['add_image_text_height_position'], $config['add_image_text_string'], $config['add_image_text_color'],$config['add_image_text_position'],$wh);
            }
            ImageJPEG($img_res,$IMG_ROOT."$file_name", $JPG_QUALITY);
        }
        return "$file_name";
    }


    function image_from_upload($uploaded_file){
        global $lang;
        $img_sz =  getimagesize( $uploaded_file );
        switch( $img_sz[2] ){
            case 1:
                $img_type = "GIF";
                $img = ImageCreateFromGif($uploaded_file);
                break;
            case 2:
                $img = ImageCreateFromJpeg($uploaded_file);
                $img_type = "JPG";
                break;
            case 3:
                $img = ImageCreateFromPng($uploaded_file);
                $img_type = "PNG";
                break;
            case 4:
                $img = ImageCreateFromSwf($uploaded_file);
                $img_type = "SWF";
                break;
            default: die("<br><font color=\"red\"><b>{$lang['Sorrythisimagetypeisnotsupportedyet']}</b></font><br>");
        }//case
        return array("img"=>$img, "w"=>$img_sz[0], "h"=>$img_sz[1], "type"=>$img_sz[2], "html"=>$img_sz[3]);

    }


    function get_sizes($src_w, $src_h, $dst_w,$dst_h ){
        global $lang;
        if ($src_w<$dst_w){
            $mlt_w=1;
        }else{
            $mlt_w = $dst_w / $src_w;
        }
        if ($src_h<$dst_h){
            $mlt_h=1;
        }else{
            $mlt_h = $dst_h / $src_h;
        }
        $mlt = $mlt_w < $mlt_h ? $mlt_w:$mlt_h;
        if($dst_w == "*") $mlt = $mlt_h;
        if($dst_h == "*") $mlt = $mlt_w;
        if($dst_w == "*" && $dst_h == "*") $mlt=1;
        $img_new_w =  round($src_w * $mlt);
        $img_new_h =  round($src_h * $mlt);
        return array("w" => $img_new_w, "h" => $img_new_h, "mlt_w"=>$mlt_w, "mlt_h"=>$mlt_h,  "mlt"=>$mlt);
    }

    function img_get_resized($img_original,$img_w,$img_h,$img_new_w,$img_new_h,$use_imagecreatetruecolor=false, $use_imagecopyresampled=false){
        global $lang;
        if( $use_imagecreatetruecolor && function_exists("imagecreatetruecolor")){
            $img_resized = imagecreatetruecolor($img_new_w,$img_new_h) or die("<br><font color=\"red\"><b>{$lang['Failedtocreatedestinationimage']}</b></font><br>");
        } else {
            $img_resized = imagecreate($img_new_w,$img_new_h) or die("<br><font color=\"red\"><b>{$lang['Failedtocreatedestinationimage']}.</b></font><br>");
        }
        if($use_imagecopyresampled && function_exists("imagecopyresampled")){
            imagecopyresampled($img_resized, $img_original, 0, 0, 0, 0,$img_new_w, $img_new_h, $img_w,$img_h) or die("<br><font color=\"red\"><b>Failed to resize @ ImageCopyResampled()</b></font><br>");
        }else{
            imagecopyresized($img_resized, $img_original, 0, 0, 0, 0,$img_new_w, $img_new_h, $img_w,$img_h) or die("<br><font color=\"red\"><b>Failed to resize @ ImageCopyResized()</b></font><br>");
        }
        return $img_resized;
    }

    function make_filename($oid,$name=''){

        global $_REQUEST,$IMG_ROOT,$Global_Class;



        //srand((double)microtime() * 999000000);
        $screenshot_db = @md5(microtime());
        $strlen1=strlen($screenshot_db);

        $strlen2=strlen($oid);
        $screenshot_db=substr($screenshot_db,0,32-$strlen2);
        $file_name = $oid.$screenshot_db . ".jpg";
        if ($name<>'' and !file_exists($IMG_ROOT.$name.".jpg")){
            return $name.".jpg";
        }elseif ($name<>'' and !file_exists($IMG_ROOT.$name."-1.jpg")){
            return $name."-1.jpg";
        }elseif ($name<>'' and !file_exists($IMG_ROOT.$name."-2.jpg")){
            return $name."-2.jpg";
        }elseif ($name<>'' and !file_exists($IMG_ROOT.$name."-3.jpg")){
            return $name."-3.jpg";
        }elseif ($name<>'' and !file_exists($IMG_ROOT.$name."-4.jpg")){
            return $name."-4.jpg";
        }elseif ($name<>'' and !file_exists($IMG_ROOT.$name."-5.jpg")){
            return $name."-5.jpg";
        }elseif ($name<>'' and !file_exists($IMG_ROOT.$name."-6.jpg")){
            return $name."-6.jpg";
        }elseif ($name<>'' and !file_exists($IMG_ROOT.$name."-7.jpg")){
            return $name."-7.jpg";
        }elseif ($name<>'' and !file_exists($IMG_ROOT.$name."-8.jpg")){
            return $name."-8.jpg";
        }elseif ($name<>'' and !file_exists($IMG_ROOT.$name."-9.jpg")){
            return $name."-9.jpg";
        }elseif ($name<>'' and !file_exists($IMG_ROOT.$name."-0.jpg")){
            return $name."-0.jpg";
        }else
            if (!file_exists($IMG_ROOT.$file_name)){
                return $file_name;
            }else{
                $screenshot_db = @md5(microtime());
                $strlen1=strlen($screenshot_db);
                if ($oid==''){
                    $oid=$_REQUEST['oid'];
                }
                $strlen2=strlen($oid);
                $screenshot_db=substr($screenshot_db,0,32-$strlen2);
                $file_name = $oid.$screenshot_db . ".jpg";
                if (!file_exists($IMG_ROOT.$file_name)){
                    return $file_name;
                }else{
                    $screenshot_db = @md5(microtime());
                    $strlen1=strlen($screenshot_db);
                    if ($oid==''){
                        $oid=$_REQUEST['oid'];
                    }
                    $strlen2=strlen($oid);
                    $screenshot_db=substr($screenshot_db,0,32-$strlen2);
                    $file_name = $oid.$screenshot_db . ".jpg";
                    return $file_name;
                }
            }
    }

    function imagestringbox($image, $font, $left, $top, $text, $color,$pos,$wh) {
        $color1=explode("|",$color);
        switch ($pos){

            case 1:
                $ss = imagefontwidth ($font);
                $ss1=$ss*strlen($text);

                $left=$wh['w']-$ss1-$left;
                $ss=imagefontheight ($font);
                $top=$wh['h']-$ss-$top;
                break;
            case 2:
                $ss = imagefontwidth ($font);
                $ss1=$ss*strlen($text);

                $left=$wh['w']-$ss1-$left;

                break;
            case 3:

                $ss=imagefontheight ($font);
                $top=$wh['h']-$ss-$top;
                break;

        }

        $color=imagecolorallocate($image, $color1[0], $color1[1], $color1[2]);
        imagestring($image, $font, $left, $top, $text, $color);

        return $image;
    }

}
?>