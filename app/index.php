<?php
include 'upload/index.php';
?>

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
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
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
                    }, 3000);
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
            <input type="file" name="uploader[]" multiple/>
            <input type="submit"/>
        </div>
    </form>
</div>
</body>
</html>