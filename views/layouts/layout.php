<html>
<head>
    <meta charset="UTF-8">
    <title>Pimcore fixtures tools</title>
</head>

<?php

$styles = [
    "/admin/misc/admin-css?extjs6=true",
    "/pimcore/static6/css/icons.css",
    "/pimcore/static6/js/lib/ext/classic/theme-triton/resources/theme-triton-all.css",
    "/pimcore/static6/css/admin.css"
];
foreach ($styles as $style) { ?>
    <link rel="stylesheet" type="text/css" href="<?= $style ?>?_dc=<?= \Pimcore\Version::$revision ?>"/>
<?php } ?>
<?php

$scriptLibs = [


    // library
    "lib/prototype-light.js",
    "lib/jquery.min.js",
    "lib/ext/ext-all.js",
    "lib/ext/classic/theme-triton/theme-triton.js",
];


?>

<?php foreach ($scriptLibs as $scriptUrl) { ?>
    <script type="text/javascript"
            src="/pimcore/static6/js/<?= $scriptUrl ?>?_dc=<?= \Pimcore\Version::$revision ?>"></script>
<?php } ?>
<body>
<?= $this->layout()->content ?>
</body>
</html>
