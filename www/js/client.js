var clickFlag = false
var clientId = null
var api_domain = ''
//init
function clientInit(cid,api_domain){
    clientId = cid
    api_domain = api_domain
}

function generateImg(){
   if(clickFlag){
        alert('生成中请稍后');
        return false;
   }
   clickFlag = true;
   $("#btnGenerateImg").html("生成中...")
    var data = {
        cid: clientId,
        prompt : $("#prompt").val(),
        nprompt : $("#nprompt").val(),
        steps : $("#steps").val(),
        scale : $("#scale").val(),
        seed : $("#seed").val(),
        sizeTag : $("#sizeTag").val(),
        hiresfix : $("#hiresfix").val() == 1? true : false,
        upscale: $("#upscale").val() ,
        sampler: $("#sampler").val()
    }
    $("#picture").attr("src", "./img/loading.gif" )
    $.ajax({
        url: api_domain + "./sd_api.php?a=generateImg",
        type:"POST",
        headers: {
            "Accept" : "application/json; charset=utf-8",
            "Content-Type": "application/json; charset=utf-8",
            // 'Authorization': 'Basic aihelper:123456'
        },
        data: JSON.stringify(data) ,
        async:true,
        success: function(res){
            console.log(res)
            if(res.code == 1){
                $("#picture").attr("src", "data:image/png;base64,"+res.data.img )
                saveLastDataLocal()
            }else{
                alert(res.msg)
                $("#picture").attr("src", "/assets/Error.png" )
            }
            clickFlag = false
            $("#btnGenerateImg").html("生成")
        },  
    })
   
}

function getServerState(){
    return;
    $.ajax({
        url: api_domain + "/api/Mini/serverstate",
        data:{cid:"{$cid}"},
        method:"GET",
        success:function(res){
            console.log('success',res)
            if(res.code == 1 && res.data != null){
                $("#serverState").html("服务器连接正常")
                $("#serverState").css('color',"green")
            }else{
                $("#serverState").html("服务器连接失败")
                $("#serverState").css('color',"red")
            }
        },
        error:function(res){
            console.log('error',res)
            $("#serverState").html("线路异常")
            $("#serverState").css('color',"red")
        }
    })
}

//请求本地上次生成数据
function loadLastDataLocal(){
    var lastData = localStorage.getItem('lastData');
    if(lastData != null){
        lastData = JSON.parse(lastData);
        $("#prompt").val(lastData.prompt)
        $("#nprompt").val(lastData.nprompt)
        $("#steps").val(lastData.steps)
        $("#scale").val(lastData.scale)
        $("#seed").val(lastData.seed)
        $("#sizeTag").val(lastData.sizeTag)
        $("#hiresfix").val(lastData.hiresfix)
        $("#upscale").val(lastData.upscale) 
        $("#sampler").val(lastData.sampler)
        console.log('load last data from local', lastData)
    }

}

//保存上次生成的数据到本地
function saveLastDataLocal(){
    var lastData = {
        prompt: $("#prompt").val(),
        nprompt: $("#nprompt").val(),
        steps: $("#steps").val(),
        scale: $("#scale").val(),
        seed: $("#seed").val(),
        sizeTag: $("#sizeTag").val(),
        hiresfix: $("#hiresfix").val(),
        upscale: $("#upscale").val(), 
        sampler: $("#sampler").val()
    }
    localStorage.setItem('lastData',JSON.stringify(lastData))
}

setInterval("getServerState()", 2000)   