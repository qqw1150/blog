<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/12/19
 * Time: 17:47
 */

namespace app\libraries;


class CommonUtil
{
    public static function generateNickName()
    {
        $nicknames = array("心跳依旧持续", "爱我还是他", "-情债╮請结账°", "北极以北。", "命系于他", "花与叶永难相见", "半透明的墙ゝ", "温柔宠溺 霸道索爱", "不见不散", "┄—━╋独特", "纯纯年代’双八年华〓", "3分清醒7分醉℃", "童言无忌！", "乱花飞絮", "°Distance （距离）", "古靈▓精怪☆", "约定一生.﹌‖", "不见不念〆", "那些年，做的脑残梦。", "___两个人的真情", "黄昏再美终要黑夜", "我想拥抱你@", "伊韵雪琳", "１生只有你", "笑对人生ㄟ", "乱世求生存", "真心真意过一生", "久敷衍", "シ假的太真灬", "激情灬战队", "择一城终老，遇一人白首！", "世界经典", "許╮一世安逸。", "起个破名太费劲啦", "、笑叹词穷", "人情薄如纸", "只为尔沉醉╮", "一个眼神的距离", "独有的温柔", "WIFI抵不过电池", "會飛ｄê魚", "与你共我", "-又是一年的睡觉期", "仅有的余味", "涐用生m1ng守护", "心中有曲自然嗨", "forever love", "一圈一圈贴我的心", "如若初心", "守护那一段爱情", "生气别把爱带走", "想沵好好过む", "你的眼泪让我内疚∮", "自作自受。", "分手后的思念是犯贱", "亡命进行曲——作业", "Mo from .（莫离）", "经典　1906▍", "伴我呼吸直至停止", "唐伯虎点蚊香", "假冒伪劣无人选", "岁月无声", "欣賞ヽ失戀", "无悔的无怨", "爱笑的人怎敢哭", "love you 1314", "明月清风", "自由如风", "浅笑心柔", "洪荒少女~", "余生请你指教", "像雾像雨又像风", "醒着做梦", "天佑爱人", "风继续吹", "凡人多烦事", "痴心错付", "累世情深", "谁能温暖我的心", "难入怹", "ζ凉风习卷人心", "你困住我，年深月久", "丝丝记忆﹏", "独孤求败◇", "酒醉三分醒", "路一直都在", "有舍有得才是美", "有梦就不怕痛", "时光不染,回忆不淡", "萌系大白(●—●)", "】幸福壹直存在【", "①生只爱你①人", "┉西海情謌┈", "不要迷戀哥，嫂子會生氣", "ヾ幸福的感情メ」", "ヾ淺色年華", "画皮易，画心难", "小妞让爷(づ￣3￣)づ╭个", "坐在坟前调戏鬼i", "^劳资终有一天会炸学校*", "谁喷了榴莲味的香水", "扛起拖把扫天下", "半聋半哑半糊涂", "五行缺钱", "我的意中人是个盖世英雄", "风向决定发型i", "没有翅膀却想飞上天空", "别说谁变了你拦得住时间么", "第一初恋", "温柔宠溺 霸道索爱", "ベ断桥烟雨ミ旧人殇", "信仰改不了信念", "ゞ 正在缓冲99%", "ミ安锦流年っ", "诠释 ゛ 一 种信仰 。", "丶Summer℡ 念", "★ ﹑奘闇 ＊°", "シ假的太真灬", "ぃ 流年┈━═☆", "ζ、三分愛，七分醒。", "眉梢゛那片情ゝ", "╰︶墨兮〤", "安於現狀°づ", "_ 忏 悔╮", "ㄑ 流年.4ツ水 . ", "〆 ﹏为 你 伏笔。", "能力就是实力.", "事在人为.", "成绩单是一份微凉的遗书", "⊙﹏⊙", "忄青深⌒缘淺", "Οo茪輝歲冄оΟ");
        return $nicknames[mt_rand(0, count($nicknames) - 1)];
    }

    /**
     * 格式化大小
     * @param int $byte 字节
     * @return int|float
     */
    public static function getSizeFormat(int $byte)
    {
        $k = pow(2, 10);
        $m = pow(2, 20);
        $g = pow(2, 30);
        $t = pow(2, 40);

        $res = $byte;

        if ($byte > $k) {
            $res = round($byte / $k, 2) . 'K';
        } else if ($byte > $m) {
            $res = round($byte / $m, 2) . 'M';
        } else if ($byte > $g) {
            $res = round($byte / $g, 2) . 'G';
        } else if ($byte > $t) {
            $res = round($byte / $t, 2) . 'T';
        }

        return $res;
    }

    public static function imageCompress(string $image, float $bi)
    {
        if (!file_exists($image)) {
            throw new \Exception('文件不存在');
        }

        $pathinfo = pathinfo($image);
        $extension = $pathinfo['extension'];
        $exArr = ['jpg', 'png', 'gif', 'jpeg'];
        if (!in_array($extension, $exArr)) {
            throw new \Exception('文件格式不支持,支持格式:' . implode(',', $exArr));
        }

        $size = getimagesize($image);
        switch ($extension) {
            case 'png':
                $im = @imagecreatefrompng($image);
                break;
            case 'gif':
                $im = @imagecreatefromgif($image);
                break;
            default:
                $im = @imagecreatefromjpeg($image);
        }
        $width = $size[0];
        $height = $size[1];
        $newwidth = intval($width * $bi);
        $newheight = intval($height * $bi);
        $thumb = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresized($thumb, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);


        switch ($extension) {
            case 'png':
                imagepng($thumb, $image);
                break;
            case 'gif':
                imagegif($thumb, $image);
                break;
            default:
                imagejpeg($thumb, $image);
        }

        imagedestroy($thumb);
        imagedestroy($im);
    }
}