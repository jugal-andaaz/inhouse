<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>FedEx Label</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
@media screen {
    html, body { background: #fff; }
    img { display: block; width: 100%; height: auto; image-rendering: -webkit-optimize-contrast; image-rendering: crisp-edges; }
}

@media print {
    @page { size: 4in 6in; margin: 0; }
    html, body { width: 4in; height: 6in; overflow: hidden; background: #fff; }
    img { display: block; width: 4in; height: 6in; object-fit: contain; }
}
</style>
</head>
<body>
<img src="{{ $url }}" id="lbl">
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

    document.getElementById('lbl').addEventListener('load', function () {
        window.addEventListener('afterprint', notifyOpener, { once: true });
        window.print();
    });
    
    document.getElementById('lbl').addEventListener('error', notifyOpener);
}());
</script>
</body>
</html>
