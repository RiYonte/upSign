<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>学生端 - 上上签</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0">
    <link href="statics/css/bootstrap.min.css" rel="stylesheet">
    <link href="statics/css/jquery.fileupload.css" rel="stylesheet">
    <link href="statics/css/oneui.min.css" id="css-main" rel="stylesheet">
    <link href="statics/css/style.css" rel="stylesheet">
    <link href="statics/js/plugins/sweetalert2/sweetalert2.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="block">
        <div class="block-content block-content-full text-center bg-image" style="background-image: url('statics/img/photo1.jpg');">
          <span class="btn fileinput-button">
            <img class="img-avatar img-avatar96 img-avatar-thumb" src="statics/img/avatar.jpg" id="avatar" alt="自拍头像">
            <input id="fileupload" type="file" name="file[]" accept="image/*;capture=camera" multiple>
          <h3 class="h1 font-w400 text-white">上传自拍</h3>
          <div class="font-w300 text-white">请用系统自带相机拍照上传</div>
          </span>
        </div>
        <div class="block-content block-content-full">
            <div class="progress active" id="progress" style="display: none;">
              <div class="progress-bar progress-bar-primary progress-bar-striped"></div>
            </div>
            <div class="form-horizontal">
                <div class="form-group">
                    <div class="col-md-6">
                        <label for="number">学号</label>
                        <input class="form-control" type="text" id="number" name="number" placeholder="请输入你的学号...">
                    </div>
                    <div class="col-md-6">
                        <label for="name">姓名</label>
                        <input class="form-control" type="text" id="name" name="name" placeholder="请输入你的姓名...">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-6">
                        <label for="lat">纬度</label>
                        <input class="form-control" type="text" id="lat" name="lat" disabled>
                    </div>
                    <div class="col-xs-6">
                        <label for="long">经度</label>
                        <input class="form-control" type="text" id="long" name="long" disabled>
                    </div>
                </div>
                <input type="hidden" id="file_name" />
                <div class="form-group">
                    <div class="col-xs-12">
                        <button class="btn btn-lg btn-block btn-success" type="submit" id="Signin" disabled>开始签到</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer id="page-footer" class="content-mini content-mini-full font-s12 bg-gray-lighter clearfix">
        <div class="pull-right">
            Crafted with <i class="fa fa-heart text-city"></i> by <a class="font-w600" href="https://sangsir.com" target="_blank">SangSir</a>
        </div>
        <div class="pull-left">
            <a class="font-w600" href="https://sangsir.com" target="_blank">上上签</a> &copy; 2017 - 沐码团队
        </div>
    </footer>
</div>
    <script src="statics/js/oneui.min.js"></script>
    <script src="statics/js/core/jquery.ui.widget.js"></script>
    <script src="statics/js/core/jquery.iframe-transport.js"></script>
    <script src="statics/js/core/jquery.fileupload.js"></script>
    <script src="statics/js/plugins/sweetalert2/es6-promise.auto.min.js"></script>
    <script src="statics/js/plugins/sweetalert2/sweetalert2.min.js"></script>
    <script src="statics/js/plugins/exif.js"></script>
    <script>
$(function () {
    function geo_success(position) {
        $('#lat').val(position.coords.latitude);
        $('#long').val(position.coords.longitude);
    }
    function geo_error(error) {
        switch (error.code) {
            case error.TIMEOUT:
            swal('出现错误', '获取地理位置超时，请检查GPS是否开启！', 'error');
            break;
            case error.PERMISSION_DENIED:
            swal('出现错误', '请允许浏览器进行地理定位，或使用系统浏览器进行签到！', 'error');
            break;
        };
    }
    if (!!navigator.geolocation) {
        navigator.geolocation.watchPosition(geo_success, geo_error, {
            enableHighAccuracy: true,
            maximumAge: 30000,
            timeout: 27000
        });
    }else{
        swal('出现错误', '浏览器不支持Geo Location API，请使用最新版浏览器！', 'error');
    }
    function checkExif(data) {
        var gettime = EXIF.getTag(data, 'DateTime');
        if(!gettime){ return false; }
        var datetime = gettime.split(/:| /); //年[0],月[1],日[2],时[3],分[4]
        var nowtime = new Date();
        var year = nowtime.getFullYear();
        var month = nowtime.getMonth()+1<10?'0'+(nowtime.getMonth()+1):nowtime.getMonth()+1;
        var date = nowtime.getDate();
        var hours = nowtime.getHours();
        var minutes = nowtime.getMinutes();
        if (datetime[0] !== year && datetime[1] !== Boolean(month) && datetime[2] !== date && datetime[3] !== hours && 0>=Math.abs(minutes-datetime[4])<1) { return false; }
    }
    $('#fileupload').fileupload({
        url: 'api.php',
        dataType: 'json',
        formData: {
            action: "identifyFace"
        },
        add: function (e, data) {
            EXIF.getData(data.files[0], function() {
                if(checkExif(this) === false){
                    swal('出现错误', '请上传系统相机即时拍照后的照片！', 'error');
                }else{
                    $("#progress").css('display','block');
                    data.submit();
                }
            });
        },
        done: function (e, data) {
            if(data.result.status == '0'){
                $('#file_name').val(data.result.file_name);
                $('#Signin').removeAttr("disabled");
                $("#progress").css('display','none');
                $("#avatar").attr('src','uploads/' + data.result.file_name);
                swal('上传成功', '请填写学号与姓名进行签到！', 'success');
            }else{
                swal('网络错误', '请刷新页面后进行重试！', 'error');
            }
        },
        progressall: function (e, data) {
             var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .progress-bar').css(
                'width',
                 progress + '%'
            );
        }
    });
    $("#Signin").click(function() {
        $.ajax({
            type: "post",
            url: "api.php",
            dataType: "json",
            data: {
                action: "Signin",
                number: $("#number").val(),
                name: $("#name").val(),
                file_name: $("#file_name").val(),
                lat: $("#lat").val(),
                long: $("#long").val()
            },
            success: function(data){
                if (data.status == 0){
                    swal('签到成功', '当前人脸评分为：' + data.scores, 'success');
                }else{
                    swal('签到失败', '请检查学号姓名与自拍是否为本人！', 'error');
                }
            },
            error: function(){
                swal('网络错误', '请刷新页面后进行重试！', 'error');
            }
        });
    });
});
    </script>
</body>
</html>