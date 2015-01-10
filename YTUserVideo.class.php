<?php
/** 获取Youtube某个User所有Video信息
*   Date:   2015-01-08
*   Author: fdipzone
*   Ver:    1.0
*
*   Func:
*   public  getVideosInfo 获取用户所有视频信息
*   private getVideoNum   获取用户视频数量
*   private getVideoInfo  获取视频信息
*   private getContent    视频简介整理
*   private unescape      unicode转中文
*/

class YTUserVideo{ // class start

    private $_user = ''; // 用户名称


    /** 初始化
    * @param String $user 用户名称
    */
    public function __construct($user=''){
        if($user!=''){
            $this->_user = $user;
        }else{
            throw new Exception("user is empty", 1);
        }
    }


    /** 获取user所有视频信息
    * @return Array
    */
    public function getVideosInfo(){

        $info = array();

        // 获取视频数量
        $videonum = $this->getVideoNum();

        // 获取视频信息
        for($i=1; $i<=$videonum; $i++){
            $videoInfo = $this->getVideoInfo($i);
            array_push($info, $videoInfo);
        }

        return $info;

    }


    /** 获取用户视频数量
    * @return int
    */
    private function getVideoNum(){
        $videos = simplexml_load_file('http://gdata.youtube.com/feeds/base/users/'.$this->_user.'/uploads?max-results=1&start-index=1');
        $videonum = $videos->children('openSearch', true)->totalResults;
        return $videonum;
    }


    /** 获取视频信息
    * @param  String $index 视频的序号
    * @return Array
    */
    private function getVideoInfo($index){

        // 获取视频id及简介
        $video = simplexml_load_file('http://gdata.youtube.com/feeds/base/users/'.$this->_user.'/uploads?max-results=1&start-index='.$index);
        $videoId = str_replace('http://gdata.youtube.com/feeds/base/videos/', '', (string)($video->entry->id));
        $videoContent = $this->getContent($video->entry->content);

        // 根据视频id获取视频信息
        $content = file_get_contents('http://youtube.com/get_video_info?video_id='.$videoId);
        parse_str($content, $ytarr);

        $info = array();

        $info['id'] = $videoId;
        $info['thumb_photo'] = $ytarr['thumbnail_url'];       // 缩略图
        $info['middle_photo'] = $ytarr['iurlmq'];             // 中图
        $info['big_photo'] = $ytarr['iurl'];                  // 大图
        $info['title'] = $ytarr['title'];                     // 标题
        $info['content'] = $videoContent;                     // 简介
        $info['publish_date'] = $ytarr['timestamp'];          // 发布时间
        $info['length_seconds'] = $ytarr['length_seconds'];   // 视频长度(s)
        $info['view_count'] = $ytarr['view_count'];           // 观看次数
        $info['avg_rating'] = $ytarr['avg_rating'];           // 平均评分
        $info['embed'] = '//www.youtube.com/embed/'.$videoId; // Embed

        return $info;

    }


    /** 获取视频简介
    * @param  String $content 内容
    * @return String
    */
    private function getContent($content){
        preg_match('/<span>(.*?)<\/span>/is', $content, $matches);
        return $this->unescape($matches[1]);
    }


    /* unicode 转 中文
    * @param  String $str unicode 字符串
    * @return String
    */
    private function unescape($str) {
        $str = rawurldecode($str);
        preg_match_all("/(?:%u.{4})|&#x.{4};|&#\d+;|.+/U",$str,$r);
        $ar = $r[0];

        foreach($ar as $k=>$v) {
            if(substr($v,0,2) == "%u"){
                $ar[$k] = iconv("UCS-2BE","UTF-8",pack("H4",substr($v,-4)));
            }elseif(substr($v,0,3) == "&#x"){
                $ar[$k] = iconv("UCS-2BE","UTF-8",pack("H4",substr($v,3,-1)));
            }elseif(substr($v,0,2) == "&#") {
                $ar[$k] = iconv("UCS-2BE","UTF-8",pack("n",substr($v,2,-1)));
            }
        }
        return join("",$ar);
    }

} // class end

?>