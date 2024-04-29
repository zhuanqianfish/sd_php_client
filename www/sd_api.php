<?php
/**
 *  auth zhuanqianfish
 *  date 2023-03-01
 *  sd_api.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

require_once("config.php");
require_once("helper.php");

//检查服务端状态
function serverstate($cid){
    $cid = $_REQUEST("cid");
    $rdomain =  $GLOBALS['config']['remote_url'];
    $authpass =   $GLOBALS['config']['password'];
    
    $remoteUrl = "{$rdomain}/app_id/";
    $imgServerUrl =  $GLOBALS['config']['SDServerUrl'];
    $remoteRes =  doCurlGetRequest( $remoteUrl, [], $authpass);
    $remoteRes = json_decode( $remoteRes, true);
    //halt($remoteUrl);
    if(isset($remoteRes['app_id'])){
       success('请求成功', $remoteRes);
    }else{
       success('请求失败');
    }
}



//异步获取图片
function  getimg(){
   success('请求成功', ['img'=>123456]);
}

//生成图片[图生图]
function generateImg()
{
    $sizeTag = $_POST["sizeTag"] ?? 23;
    $scale = $_POST["scale"] ?? 7;
    $steps = $_POST["steps"] ??  15;
    $seed = $_POST["seed"]?? null ;
    $prompt = $_POST["prompt"] ?? '';   
    $genType =  $_POST["gentype"]?? 0; //生成类型：0文生图 ， 1图生图
    $ckpic =  $_POST["ckpic"]?? '';     //参考图片base64
    $nprompt = $_POST["nprompt"]?? "" ;
    $sampler_name = $_POST["sampler_name"]?? "Euler a" ;
    $useModel = $_POST["model"]?? "2D" ;       //使用的模型
    
    $data = prepare($prompt, $nprompt, $sizeTag, $scale, $steps, $seed); //前置准备数据
    //halt($data);
    $apiName = 'txt2img';
    if($genType == 1){
        $apiName = 'img2img';
    }
        
    //用于存档生成数据
    $data = [
        'prompt'=>$prompt,
        'model_type'=>$useModel,
        'nprompt'=>$data['uc'] ,
        'seed'=>$seed ,
        'width'=>$data['width'] ,
        'height'=>$data['height'] ,
        'steps'=>6 ,
        'scale'=>1 ,
        'startstamp'=>time(),
        'status'=>0,
    ];
    ///////////////整理格式//////////////////////
    $newData['imgid'] =  time() ;   
    $newData['prompt'] = $data['prompt'];
    $newData['steps'] = $data['steps'];
    $newData['cfg_scale']  = $data['scale'];
    $newData['width'] = $data['width'];
    $newData['height'] = $data['height'];
    $newData['negative_prompt']  =    $nprompt;
    // $newData['sampler_name']  =  "DPM++ 2M Karras";        // $sampler_name;    Euler a ,  DPM++ 2M Karras,  DPM++ SDE Karras,  UniPC
    $newData['sampler_name']  =   $sampler_name;
    $newData['seed'] = $seed;
    $newData['eta']  =  0.67; //噪声乘数 - 控制在去噪扩散过程中添加到输入数据的噪声量。0表示无噪音，1.0表示更多噪音。eta对图像有微妙的、不可预测的影响，所以您需要尝试一下这如何影响您的项目。
    $newData['save_images']  = true;        //保存图片
    $newData['init_images'] =  [$ckpic] ;   
    $newData['resize_mode'] =  1 ;   
    
    if($useModel=='2D' ){ 
        // $newData['scale'] = 8; 
        //////////////START 高清修复///////////////////////////////
        $newData['enable_hr']  =  false;        //高清修复关闭
        // $newData['enable_hr']  =  $_POST['hiresfix');  /////aaaa             //高清修复开启
        $newData['denoising_strength']  =  0.35;      //重绘强度
        $newData['hr_second_pass_steps']  =  8;    //放大步数   8-12
        $hr_scale = $_POST['upscale'] ?? 1.5;
        if($hr_scale > 2){
            $hr_scale =2; 
        }
        $newData['hr_scale']  =  $hr_scale;       //放大倍数
        $newData['hr_upscaler']  =  "Latent";     //放大算法 ESRGAN_4x， ScuNET PSNR, ScuNET , Nearest, Latent, ,R-ESRGAN General 4xV3
        /////////////END 高清修复////////////////////////////////
        // $newData['restore_faces']  =  true;         //面部修复开启
        
        //测试过的2dng:
        // (nsfw),EasyNegative, (worst quality, low quality:1.4), (lip, nose, tooth, rouge, lipstick, eyeshadow:1.4), (jpeg artifacts:1.4), (bokeh, blurry, film grain, chromatic aberration, lens flare:1.0), (1boy, abs, muscular, rib:1.0), greyscale, monochrome, dusty sunbeams, trembling, motion lines, motion blur, emphasis lines, text, title, logo, signature, simple background, white background,
        $negative_prompt1 = "(nsfw),EasyNegative, (worst quality, low quality:1.4), (lip, nose, tooth, rouge, lipstick, eyeshadow:1.4), (jpeg artifacts:1.4), (bokeh, blurry, film grain, chromatic aberration, lens flare:1.0), (1boy, abs, muscular, rib:1.0), greyscale, monochrome, dusty sunbeams, trembling, motion lines, motion blur, emphasis lines, text, title, logo, signature, simple background, white background,";//实验性质
        $negative_prompt2 = "(nsfw),NSFW,EasyNegative, (worst quality, low quality:1.4), (zombie, sketch, interlocked fingers), (lip, nose, tooth, rouge, lipstick, eyeshadow:1.4), (jpeg artifacts:1.4), (bokeh, blurry, film grain, chromatic aberration, lens flare:1.0),(text, signature, logo, artist name:1) greyscale, monochrome, dusty sunbeams, trembling, motion lines, motion blur, emphasis lines,  simple background,"; //经过验证
        $negative_prompt3 = "(nsfw),easynegative,bad_prompt_version2-neg,"; //极简主义
        // $newData['negative_prompt']  = $negative_prompt3 . $data['uc'];
        // $newData['negative_prompt']  = $negative_prompt2;
        // $nprompt = $_POST["nprompt", "" );
        if( $nprompt == ""){
            $nprompt =  $negative_prompt3;
        }
        $newData['negative_prompt']  = $nprompt;/////aaaa  
    }
    
    $dataFinal = [];
    if($genType == 0){
        $dataFinal = $newData;
    }
    
    if($genType == 1){  //图生图模式
        //放大倍率
        $beilv = 2;
        $dataFinal['init_images'] = $newData['init_images'];
        $dataFinal['sampler_name'] = $newData['sampler_name'];
        $dataFinal['prompt'] = $newData['prompt'];
        $dataFinal['negative_prompt'] = $newData['negative_prompt'];
        $dataFinal['steps'] = 20;
        $dataFinal['cfg_scale'] = $newData['cfg_scale'];
        $dataFinal['width'] = $newData['width'] * $beilv;
        $dataFinal['height'] = $newData['height'] * $beilv;
        $dataFinal['save_images'] = $newData['save_images'];
        $dataFinal['sampler_index'] = $newData['sampler_name'];
        $dataFinal['denoising_strength'] = 0.45; //重绘幅度
    }
   
    $rdomain =  $GLOBALS['config']['SDServerUrl'];
    $authpass =  $GLOBALS['config']['password'];
    $remoteUrl = "{$rdomain}/sdapi/v1/{$apiName}";
  
    $remoteRes = doCurlPostRequest( $remoteUrl, $dataFinal, $authpass);
    $remoteRes = json_decode( $remoteRes, true);
    
//   dump( $remoteRes);
    if(isset($remoteRes['images'][0])){
       success('成功', ['img'=>$remoteRes['images'][0], 'imgid'=> $newData['imgid']] );  
    }else{
        error('失败', ['imgid'=>$newData['imgid'] ] );
    }
}



//准备参数,过滤关键词等
function  prepare($prompt , $nprompt="" ,$sizeTag=23, $scale=7, $steps=10, $seed=null){
        //小尺寸【高负载启用】
    $width = 384;
    $height = 640;
    if($sizeTag == 12){
        $width = 384;
        $height = 768;
    }
    if($sizeTag == 11){
        $width = 512;
        $height = 512;
    }
    
    if($sizeTag == 21){ //横屏
        $width = 640;
        $height = 384;
    }
    
    //end小尺寸
    
    //中等尺寸【】
    $width = 512;
    $height = 768;
    if($sizeTag == 12){  //全面屏
        $width = 384;
        $height = 768;
    }
    if($sizeTag == 11){ //方形
        $width = 512;
        $height = 512;
    }
    
    if($sizeTag == 21){ //横屏
        $width = 768;
        $height = 512;
    }
    //end 中等尺寸
    
        //大尺寸【SDXL模型启用】
    // $width = 832;
    // $height = 1216;
    // if($sizeTag == 12){  //全面屏
    //     $width = 832;
    //     $height = 1216;
    // }
    // if($sizeTag == 11){
    //     $width = 1024;
    //     $height = 1024;
    // }
    
    // if($sizeTag == 21){ //横屏
    //     $width = 1216;
    //     $height = 832;
    // }
        //end大尺寸【启用】
        
        //其他参数预处理 
    if($scale > 15) $scale = 15;
    if($scale < 1) $scale = 1;
    
    // if($steps > 15) $steps = 15;
    if($steps > 50) $steps = 50;
    if($steps < 1) $steps = 1;
    // $steps = 30;               //强制30步  比较闲的时候使用!!!!!!!!!!!!!!!!!!!!!!!!!!
    // if($steps > 20) $steps = 20;
    if($seed == "" || $seed <= 0 || $seed > 4294967295){ //seed最大值限制 2^32 -1
        $seed = null;
    }
    
    //这里需要预处理一下 剔除中文输入常见符号
    // $prompt = strtolower( $prompt) ;     //和lora模型冲突，取消
    //$prompt = substr(trim($prompt), 0, 500);
    $prompt = str_replace('，', ',', $prompt);  //
    $prompt = str_replace('（', '(', $prompt);
    $prompt = str_replace('）', ')', $prompt);
    $prompt = str_replace('{', '(', $prompt);
    $prompt = str_replace('}', ')', $prompt);
    $prompt =  fanyinative($prompt);        //翻译一下，支持中文
    // $prompt = $this->fanyi($prompt);        //百度翻译一下，支持中文
    
    //词汇黑名单
    $blakWords = ['nsfw','sex','nacked','naked','nakeness','nude','nudity','nipple', 'nipples','papilla', 'fuck',
            'bottomless','bare','no bra','no briefs','no underpants', 'thigh','pussy','vagina','breast','boobs','spread',
            'genitals','genital' ,'orgasm', 'cum','no clothes','topless','uncensored', 'shaved','areolas','no underwear','no panties','penis','blow job','spermatization','semen','climax','anus','organ','male','man','fundoshi']; 
    foreach(  $blakWords as $bstr ){
        $prompt = str_replace($bstr, '', $prompt);
    }
    
    //长度不够则追加保底词汇
    // $defult_height_result0 = ",masterpiece,{extremely detailed CG unity 8k wallpaper},best quality,Amazing,finely detail, cinematic lighting,detailed background,bright pupils, dynamic pose,dynamic angle,looking at viewer,detailed clothes";
    // $defult_height_result = ",masterpiece,{extremely detailed wallpaper}, best quality,Amazing,finely detail, detailed background,detailed clothes";
    // $defult_height_result = "";
    // if(strlen($prompt) < 20){
    //     $prompt .= $defult_height_result;  
    // }
    
    //$nprompt = substr(trim($nprompt),0, 500);
    $nprompt = str_replace('，', ',', $nprompt);
    // $nprompt = $this->fanyi($nprompt);       //nprompt不再翻译翻译
    // if($nprompt == ""){      //如果预处理阶段需要默认否定词时开启
    //     $nprompt= "lowres, bad anatomy,  bad-hands-5, text, error, missing fingers, extra digit, fewer digits, cropped, worst quality, low quality, normal quality, jpeg artifacts, signature, watermark, username, blurry, lowres,bad anatomy,bad hands,text,error,missing fingers,extra digit,fewer digits,cropped,worst quality,low quality,normal quality,jpeg artifacts,signature,watermark,username,blurry,missing arms,long neck,Humpbacked,";
    // }
    //$defult_uc= "easynegative,(nsfw), lowres, bad anatomy,  bad-hands-5, text error, missing fingers, extra digits, fewer digits, cropped, worst quality, low quality, standard quality, peg artifacts, signature, watermark, username, blurry";
    $defult_uc= "";
    $data = [
        "prompt"=> $prompt,
        "width"=> $width,
        "height"=> $height,
        "scale"=> $scale,
        // "sampler"=>"k_euler_ancestral",     //"k_euler_ancestral or ddim  k_euler",
        "steps"=> $steps,
        "seed"=> $seed,
        // "n_samples"=> 1,    
        // "ucPreset"=> 3,
        "uc"=> $defult_uc.  $nprompt
    ];
    return $data;
}


//本地翻译
function fanyinative($promptStr){
    $promptList = explode(',',  $promptStr);
    $tagList = [];          //加载列表
    $tagNameList = array_column($tagList, 'name');
    foreach($promptList  as &$tag){
        $found_key = array_search($tag, $tagNameList);
        if($found_key){
            $tag = $tagList[$found_key]['tag'];
        }
    }
    $res = implode(',', $promptList);
    return $res;
}


//lora模型列表 
function loralist(){
    $loralist = [];    //加载lora列表
    success('请求成功', ['list'=> $loralist ]);
}

$action = $_GET['a'];
$action();