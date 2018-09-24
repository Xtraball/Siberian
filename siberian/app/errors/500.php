<!DOCTYPE html>
<html>
<head>
    <title>500 Internal Server Error</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <link href="500/style.css" media="screen" rel="stylesheet" type="text/css">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" id="favicon" type="image/x-icon" href="/favicon.ico">
</head>
<body class="general">

<div class="wrapper">
    <div class="top">
        <div class="header_content">
            <div class="left">
                <img src="500/logo.png" alt="Mobile Company" title="Mobile Company" width="151">
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <div>

        <div class="content application">
            <div class="application_content">
                <div class="area">

                    <h1>A Fatal Error Occurred</h1>
                    <p>For security reasons, errors messages are disabled.</p>
                    <?php if(!empty($_GET["log"]) && (strpos($_GET["log"],"<script>") === false)) : ?>
                    <p>Error log: <?php echo $_GET["log"] ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <div class="footer">
        <div class="footer_content">
            <div class="content copyright a-center">
            </div>
        </div>
    </div>

</div>
