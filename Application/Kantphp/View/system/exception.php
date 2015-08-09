<!DOCTYPE html>
<html>
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
        <title>Application Exception</title>
        <style type="text/css">
            *{ padding: 0; margin: 0; }
            html{ overflow-y: scroll; }
            body{ background: #fff; color: #333; font-size: 16px; }
            .error{ padding: 24px 48px; }
            h1{ font-size: 32px; line-height: 48px; }
            .error .content{ padding-top: 10px}
            .error .info{ margin-bottom: 12px; }
            .error .info .title{ margin-bottom: 3px; }
            .error .info .title h3{ color: #000; font-weight: 700; font-size: 16px; }
            .error .info .text{ line-height: 24px; }
            .copyright{ padding: 12px 48px; color: #999; }
            .copyright a{ color: #000; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class="error">
            <h1><?php echo strip_tags($error['message']); ?></h1>
            <div class="content">
                <?php if (isset($error['file'])) { ?>
                    <div class="info">
                        <div class="title">
                            <h3>Line</h3>
                        </div>
                        <div class="text">
                            <p>FILE: <?php echo $error['file']; ?> &#12288;LINE: <?php echo $error['line']; ?></p>
                        </div>
                    </div>
                <?php } ?>
                <?php if (isset($error['trace'])) { ?>
                    <div class="info">
                        <div class="title">
                            <h3>TRACE</h3>
                        </div>
                        <div class="text">
                            <p><?php echo nl2br($error['trace']); ?></p>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="copyright">
            <p><a title="Kantphp Framework" href="http://www.kantphp.com">Kantphp Framework 2.0</a></p>
        </div>
    </body>
</html>
