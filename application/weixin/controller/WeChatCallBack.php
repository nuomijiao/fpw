<?php
/**
 * Created by Sweet Jiao
 * User: Sweet Jiao
 * Date: 2018/5/3 0003
 * Time: 11:06
 */

namespace app\weixin\controller;


use think\Request;

class WeChatCallBack extends BaseController
{
    private $token;

    public function __construct()
    {
        $this->token = config('weixin.token');
    }

    public function index()
    {
        $request = Request::instance();
        $param = $request->param();
        if (!isset($param['echostr'])) {
            $this->logger("R \r\n".'cuowu');
            $callback = $this->responseMsg();
            echo $callback;
        } else {
            $echoStr = $this->valid($param);
            echo $echoStr;
        }
    }

    //验证签名
    private function valid($param)
    {
        $echoStr = $param['echostr'];
        $signature = $param['signature'];
        $timestamp = $param['timestamp'];
        $nonce = $param['nonce'];
        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return $echoStr;
            exit;
        }
    }


    private function responseMsg()
    {

//        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postStr = file_get_contents("php://input");
        if (!empty($postStr)) {
            $this->logger("R \r\n".$postStr);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
            switch ($RX_TYPE) {
                case 'event':

                    break;
                case 'text':
                    $result = $this->receiveText($postObj);
                    break;
                case 'image':

                    break;
                case 'location':
                    $result = $this->receiveLocation($postObj);
                    break;
                case 'voice':

                    break;
                case 'link':

                    break;
                default:
                    $result = "unknow msg type:". $RX_TYPE;
                    break;
            }
            $this->logger("T \r\n".$result);
            return $result;
        } else {
            return '';
            exit;
        }
    }

    private function receiveText($object)
    {
        $keyword = trim($object->Content);
        if (strstr($keyword, "文本")) {
            $content = "这是个文本消息";
        } else if (strstr($keyword, "表情")) {
            $content = "中国国旗：".$this->utf8_bytes(0x1F1E8).$this->utf8_bytes(0x1F1F3)."\n".
                "美国国旗".$this->utf8_bytes(0x1F1FA).$this->utf8_bytes(0x1F1F8)."\n".
                "男女牵手".$this->utf8_bytes(0x1F46B)."\n".
                "仙人掌".$this->utf8_bytes(0x1F335)."\n".
                "电话机".$this->utf8_bytes(0x260E)."\n".
                "药丸".$this->utf8_bytes(0x1F48A);
        } else if (strstr($keyword, "单图文")) {
            $content = array();
            $content[] = array("Title" => "", "Description" => "", "PicUrl" => "", "Url" => "");
        } else if (strstr($keyword, "图文") || strstr($keyword, "多图文")) {
            $content = array();
            $content[] = array("Title" => "", "Description" => "", "PicUrl" => "", "Url" => "");
        }
        else {
            $content = date("Y-m-d H:i:s",time())."\nOpenID:".$object->FromUserName."\n技术支持 糯米蛟";
        }

        if (is_array($content)) {

        } else {
            $result = $this->transmitText($object, $content);
        }
        return $result;

    }

    private function receiveLocation($object)
    {
        $content = "你发送的位置，经度为：".$object->Location_Y."；纬度为".$object->Location_X."；缩放级别为：".$object->Scale."；位置为：".$object->Label;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    private function transmitText($object, $content)
    {
        if (!isset($content) || empty($content)) {
            return "";
        }
        $xmlTpl = "<xml>
                   <ToUserName><![CDATA[%s]]></ToUserName>
                   <FromUserName><![CDATA[%s]]></FromUserName>
                   <CreateTime>%s</CreateTime>
                   <MsgType><![CDATA[text]]></MsgType>
                   <Content><![CDATA[%s]]></Content>
                   </xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

    //添加日志
    private function logger($log_content)
    {
        $max_size = 100000;
        $log_filename = LOG_PATH. "log.xml";
        if (file_exists($log_filename) && (abs(filesize($log_filename)) > $max_size)) {
            unlink($log_filename);
        }
        file_put_contents($log_filename, date("Y-m-d H:i:s")." ".$log_content."\r\n", FILE_APPEND);
    }

    //ASCII转码， 回复表情
    private function utf8_bytes($cp)
    {
        if ($cp > 0x10000) {
            # 4 bytes
            return chr(0xF0 | (($cp & 0x1C0000) >> 18)).
                chr(0x80 | (($cp & 0x3F000) >> 12)).
                chr(0x80 | (($cp & 0xFC0) >> 6)).
                chr(0x80 | ($cp & 0x3F));
        } else if ($cp > 0x800) {
            # 3 bytes
            return chr(0xE0 | (($cp & 0xF000) >> 12)).
                chr(0x80 | (($cp & 0xFC0) >> 6)).
                chr(0x80 | ($cp & 0x3F));
        } else if ($cp > 0x80) {
            # 2 bytes
            return chr(0xC0 | (($cp & 0x7C0) >> 6)).
                chr(0x80 | ($cp & 0x3F));
        } else {
            # 1 bytes
            return chr($cp);
        }
    }
}