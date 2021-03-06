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
                    console.log(upload)
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