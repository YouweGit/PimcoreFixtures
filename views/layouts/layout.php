<html>
<head>
    <meta charset="UTF-8">
    <title>Pimcore fixtures tools</title>
</head>

<?php
$styles = array(
    "/pimcore/static/css/admin.css",
    "/admin/misc/admin-css",
    "/pimcore/static/css/icons.css",
    "/pimcore/static/js/lib/ext/resources/css/ext-all.css",
    "/pimcore/static/js/lib/ext/resources/css/xtheme-gray.css",
);
foreach ($styles as $style) { ?>
    <link rel="stylesheet" type="text/css" href="<?= $style ?>?_dc=<?= \Pimcore\Version::$revision ?>"/>
<?php } ?>
<?php

// SCRIPT LIBRARIES
$scriptExtAdapter = "lib/ext/adapter/jquery/ext-jquery-adapter.js";
$scriptExt = "lib/ext/ext-all.js";
if (PIMCORE_DEVMODE) {
    $scriptExtAdapter = "lib/ext/adapter/jquery/ext-jquery-adapter-debug.js";
    $scriptExt = "lib/ext/ext-all-debug.js";
}

$scriptLibs = array(

    // library
    "lib/prototype-light.js",
    "lib/jquery.min.js",
    $scriptExtAdapter,
    $scriptExt,
    "lib/ext-plugins/Notification/Ext.ux.Notification.js",
    // locale
    "lib/ext/locale/ext-lang-en.js",
);


?>

<?php foreach ($scriptLibs as $scriptUrl) { ?>
    <script type="text/javascript" src="/pimcore/static/js/<?= $scriptUrl ?>?_dc=<?= \Pimcore\Version::$revision ?>"></script>
<?php } ?>
<script type="text/javascript" src="/plugins/PimcoreFixtures/static/settings.js"></script>
<body>
    <?= $this->layout()->content ?>
</body>
</html>