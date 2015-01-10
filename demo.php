<?php
require 'YTUserVideo.class.php';

$obj = new YTUserVideo('GOtriphk'); // 用户名称GOtriphk https://www.youtube.com/user/GOtriphk/videos
$videosInfo = $obj->getVideosInfo();

echo '<pre>';
print_r($videosInfo);
echo '</pre>';
?>