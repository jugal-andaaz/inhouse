<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>FedEx Invoice</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
html, body { width: 100%; height: 100vh; overflow: hidden; background: #fff; }
iframe { display: block; width: 100%; height: 100vh; border: none; }
</style>
</head>
<body>
<iframe src="{{ $url }}" id="frm"></iframe>
<script>
(function () {
    var notified = false;
    function notifyOpener() {
        if (notified) return;
        notified = true;
        try {
            var cb = new URLSearchParams(location.search).get('cb');
            if (cb && window.opener && typeof window.opener[cb] === 'function') {
                window.opener[cb]();
            }
        } catch (e) {}
    }

    var frame = document.getElementById('frm');

    frame.addEventListener('load', function () {
        setTimeout(function () {
            window.addEventListener('afterprint', notifyOpener, { once: true });
            window.print();
        }, 600);
    });
    setTimeout(function () {
        if (notified) return;
        window.addEventListener('afterprint', notifyOpener, { once: true });
        window.print();
    }, 3500);

    setTimeout(notifyOpener, 30000);
}());
</script>
</body>
</html>
