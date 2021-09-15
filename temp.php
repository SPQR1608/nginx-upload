
https://www.howtoforge.com/displaying-upload-progress-with-nginx-on-debian-wheezy?__cf_chl_captcha_tk__=10b40d5daa280a7e6199c64bc2020d0e788f3eab-1625215193-0-AfzifwGaS3BJNJ3YeWw4G9IBM1CAYG18rqS6Bb3L0mReQjEKsRWLt-vMrIth3p_4iEXLt8bqkVgFYUpEBzM5jHpPdFn3EBfSFp2WNNHpeAH45ftNJDtPzJpRqv8eh35egG9rkrjpci4CnAYNWWWa5XKlaOPrQLzAmcKnSk43KWB5ytZJKg_1pYfcA2jWXtbvfnOhF4VkTfCWGpDFJ-QXPnmaWZeREo3DCPqpKc3wadVMA7a_G60g36EF43WxISYQuN2foaiPJe65QUqene25dW1ruCG3ve3sQo4S-v3VzYIcCbFaUT1OofBshK31GlcRIUG70_6PJzniwbz7j1whSV6JlZo7cmfFLXQTMFBhd1k2EG65zGRp7aBoG0fQdyKLvj1v-UqGsoZxQfjky3t7nWygJhOC7PEVNJBap_O2sqH5cFLaeNmIAmatLOMbaXsJEhi-yWRwAsVljSQw8WDXv3lfDEz7vGbDKiwmLg38q0avpSZhaamJkEpu1Wqk-b9VWUsOh0U80XDi66NGrrMy4sEitdcxQ_fkIOlMGKx-GVt06ZU0BKcLGpaNWBBmHctmfK0MPBEg_aR-cL7t0ck09B0C1tmLQntEGK8rQzY6zBy0SXlq5PnRaCq5KtT8wu8TYN4Ya9mpLcgrS5E0831c_WbNxI8caFdlv0neltuXFbVeR-ITw4iy9iUq8eGlvBjnSQ
<?php
/*
server {
    listen 80;

    index index.php index.html;
    error_log  /var/log/nginx/error.log;
    access_log /var/log/nginx/access.log;
    root /var/www/app;

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }
    location / {
        try_files $uri $uri/ /index.php?$query_string;
        gzip_static on;
    }

    # Allow file uploads max 1024M for example
    client_max_body_size 2024M;
    upload_buffer_size 10M;

    # настройка nginx-upload модуля
    # сюда будут отправляться данные из POST форм
    location = /upload/share {

      # указываем бэкенд, который выполнится уже после загрузки данных
      # это может быть ваш PHP скрипт для управления файлами
      # и директорию, куда сохраняются загруженные файлы
      upload_pass /var/www/app;
      upload_store /var/www/files;

      # укажем, какие дополнительные данные передать бэкенду
      upload_set_form_field $upload_field_name.name "$upload_file_name";
      upload_set_form_field $upload_field_name.content_type "$upload_content_type";
      upload_set_form_field $upload_field_name.path "$upload_tmp_path";

      upload_aggregate_form_field "$upload_field_name.md5" "$upload_file_md5";
      upload_aggregate_form_field "$upload_field_name.size" "$upload_file_size";

      # в случае возникновения этих ошибок файлы будут удалены
      upload_cleanup 400 404 499 500-505;

      # урезаем скорость
      # это мне необходимо для долгой загрузки файлов
      # чтобы дебажить скрипт и успеть налюбоваться на процесс загрузки
      upload_limit_rate 10k;

      # включаем информирование для "upload" (см. в начале)
      track_uploads upload 1m;
    }

    # сюда приходят ajax-запросы со страницы
    location = /upload/status {

      # информируем их о процессе загрузки
      report_uploads upload;
    }
}
 */


if (!empty($_REQUEST) || !empty($_FILES)) {
    file_put_contents('request.json', json_encode($_REQUEST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
</head>
<body>
<form method="post" enctype="multipart/form-data" id="upload" onsubmit="progress();">
    <input type="hidden" id="count" value="1"/>
    <div id="multiple">
        <input type="file" name="file[]" multiple/><br>
    </div>
    <input type="submit">
    <a href="#" onclick="add();">add();</a>
</form>
<div id="status" style="display: none;">
    <table width="100%">
        <tr>
            <th></th>
            <th>загрузка</th>
            <th>осталось</th>
            <th>всего</th>
        </tr>
        <tr>
            <td>время:</td>
            <td id="elapsed">∞</td>
            <td id="remaining">∞</td>
            <td id="total">∞</td>
        </tr>
        <tr>
            <td>размер:</td>
            <td id="sent">0 b</td>
            <td id="offset">0 b</td>
            <td id="length">0 b</td>
        </tr>
        <tr>
            <td>скорость:</td>
            <td id="speed">n/a</td>
        </tr>
    </table>
    <div style="border: 1px solid #c0c0c0;">
        <div style="background: #c0c0c0; width: 0%; text-align: right;" id="bar">0%</div>
    </div>
    <a href="#" onclick="if (confirm('Вы точно хотите отменить загрузку?')) window.location = '/'" id="cancel">cancel_upload();</a>
</div>

<script>

    /*  form.addEventListener('submit', ev => {
          ev.preventDefault();
          console.log(123)
      })*/

    function add() {
        if (parseInt(document.getElementById('count').getAttribute('value')) < 8) {
            var input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('multiple', '');
            input.setAttribute('name', 'file[]');
            document.getElementById('multiple').appendChild(input);
            document.getElementById('multiple').appendChild(document.createElement('br'));
            document.getElementById('count').setAttribute('value', parseInt(document.getElementById('count').getAttribute('value')) + 1);
        } else {
            alert('Можно загрузить не более 8 файлов за раз.');
        }
    }

    function progress() {
        var ms = new Date().getTime() / 1000;
        rq = 0;
        id = "";
        for (i = 0; i < 32; i++) {
            id += Math.floor(Math.random() * 16).toString(16);
        }
        document.querySelector('#upload').action = "/upload/share?X-Progress-ID=" + id;
        document.getElementById('status').style.display = 'block'
        interval = window.setInterval(function () {
            fetch(id, ms);
        }, 1000);
        return true;
    }

    function fetch(id, ms) {
        var fetch = new XMLHttpRequest();
        fetch.open("GET", "/upload/status", 1);
        fetch.setRequestHeader("X-Progress-ID", id);
        fetch.onreadystatechange = function () {
            if (fetch.readyState == 4) {
                if (fetch.status == 200) {
                    var now = new Date().getTime() / 1000;
                    var upload = eval(fetch.responseText);
                    if (upload.state == 'uploading') {
                        var diff = upload.size - upload.received;
                        var rate = upload.received / upload.size;
                        var elapsed = now - ms;
                        var speed = upload.received - rq;
                        rq = upload.received;
                        var remaining = (upload.size - upload.received) / speed;
                        var uReceived = parseInt(upload.received) + ' bytes';
                        var uDiff = parseInt(diff) + ' bytes';
                        var tTotal = parseInt(elapsed + remaining) + ' secs';
                        var tElapsed = parseInt(elapsed) + ' secs';
                        var tRemaining = parseInt(remaining) + ' secs';
                        var percent = Math.round(100 * rate) + '%';
                        var uSpeed = speed + ' bytes/sec';
                        document.getElementById('length').firstChild.nodeValue = parseInt(upload.size) + ' bytes';
                        document.getElementById('sent').firstChild.nodeValue = uReceived;
                        document.getElementById('offset').firstChild.nodeValue = uDiff;
                        document.getElementById('total').firstChild.nodeValue = tTotal;
                        document.getElementById('elapsed').firstChild.nodeValue = tElapsed;
                        document.getElementById('remaining').firstChild.nodeValue = tRemaining;
                        document.getElementById('speed').firstChild.nodeValue = uSpeed;
                        document.getElementById('bar').firstChild.nodeValue = percent;
                        document.getElementById('bar').style.width = percent
                    } else {
                        window.clearTimeout(interval);
                    }
                }
            }
        }
        fetch.send(null);
    }
</script>

</body>
</html>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="de">
<head>
    <title>nginx Upload Progress</title>
    <style>
        body {
            font-family: arial;
            font-size: 11px;
        }
        #statbar_box {
            width: 500px;
            height: 20px;
            background-color: #FFF;
            position: relative;
            text-align: center;
            margin-bottom: 12px;
            display: none;
            border-radius: 4px;
        }
        #statbar_bar {
            position: absolute;
            height: 20px;
            top: 0px;
            background-color: darkred;
            text-align: center;
            z-index: 100;
            border-radius: 4px;
        }
        #status {
            margin-bottom: 12px;
            text-align: center;
            width: 500px;
        }
        iframe {
            width: 500px;
            height: 30px;
            margin-bottom: 12px;
            position: absolute;
            top: -300px;
        }
        #footer {
            text-align: center;
            width: 500px;
            background: #ddd;
            padding: 5px;
            border-radius: 4px;
        }
        #base {
            width: 500px;
            padding: 20px;
            background: #EFEFEF;
            border-radius: 4px;
            position: relative;
            margin: auto;
        }
    </style>
    <script src="http://code.jquery.com/jquery-1.8.3.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        (function ($) {
            var interv;
            var test = 1;
            var statbarWidth;
            $(document).ready(function () {
                statbarWidth = $("#statbar_box").width();
                $("#upload").submit(function () {
                    /* generate random progress-id */
                    uuid = "";
                    for (i = 0; i < 32; i++) {
                        uuid += Math.floor(Math.random() * 16).toString(16);
                    }
                    /* patch the form-action tag to include the progress-id */
                    $("#upload").attr("action", "/upload/share?X-Progress-ID=" + uuid);
                    $("#statbar_bar").css("width", "1px");
                    $("#statbar_box").fadeIn(500);
                    $(this).disabled = false;
                    interv = setInterval(function () {
                        progress(uuid)
                    }, 800);
                });
            });
            var firststart = true;
            function progress(uuid) {
                $.getJSON("/upload/status", {"X-Progress-ID": uuid}, function (data) {
                    console.log(data);
                    if (data.state == 'done') {
                        clearInterval(interv);
                        $("#status").html('100%');
                        $("#statbar_bar").animate({"width": statbarWidth + "px"}).animate({"width": "1px"}, 400, function () {
                            $("#statbar_box").fadeOut(500);
                            $("#status").html('0%');
                        });
                    }
                    var prozent = Math.round((data.received * 100) / data.size);
                    prozent = !prozent?0:prozent;
                    var pixel = Math.round((prozent * statbarWidth) / 100);
                    $("#status").html(prozent + '%');
                    firststart = false;
                    $("#statbar_bar").animate({"width": pixel + "px"});

                });
            }
        })(jQuery);
    </script>
</head>
<body>
<div id="base">
    <?php
    print '- Max. Postsize: ' . ini_get('post_max_size');
    print '<br>';
    print '- Max. Uploadsize: ' . ini_get('upload_max_filesize');
    ?>
    <div id="status">0%</div>
    <div id="statbar_box">
        <div id="statbar_bar"></div>
    </div>
    <iframe src="/upload/" name="hidden_upload" frameborder="0"></iframe>
    <form id="upload" action="/upload/" target="hidden_upload" method="post" enctype="multipart/form-data">
        <div id="footer">
            <input type="hidden" name="MAX_FILE_SIZE" value="30000000"  />
            <input type="file" name="uploader[]"/>
            <input type="file" name="uploader[]"/>
            <input type="submit"/>
        </div>
    </form>
</div>
</body>
</html>