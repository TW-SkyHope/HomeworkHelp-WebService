<?php
function isMobileDevice() {
    return preg_match("/(android|webos|avantgo|iphone|ipad|ipod|blackberry|iemobile|bolt|boost|cricket|docomo|fone|hiptop|mini|opera mini|kitkat|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

$isMobile = isMobileDevice();

if (!file_exists(__DIR__ . '/db.php')) {
    header("Location: install.php");
} else if ($isMobile) {
    header("Location: phone.php");
} else {
    header("Location: windows.php");
}
exit;
?>