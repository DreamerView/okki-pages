<?php

header('Content-Type: image/svg+xml');

$fullname = trim($_GET['fullname'] ?? 'Folio');
$size = (int)($_GET['size'] ?? 100);
$names = explode(' ', $fullname);
$initials = strtoupper(mb_substr($names[0], 0, 1, 'UTF-8')) . (isset($names[1]) ? strtoupper(mb_substr($names[1], 0, 1, 'UTF-8')) : '');

echo "<?xml version='1.0' encoding='UTF-8'?><svg xmlns='http://www.w3.org/2000/svg' width='$size' height='$size' viewBox='0 0 $size $size'><circle cx='50%' cy='50%' r='" . ($size / 2) . "' fill='black'/><text x='50%' y='50%' font-size='" . ($size / 2.5) . "' text-anchor='middle' dominant-baseline='central' fill='white' font-family='Arial'>$initials</text></svg>";
?>