<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/5/13
 * Time: 11:37
 */

namespace App\Lib;


class ImageCut
{

    private $config = [
        'file' => null,
        'width' => 10,
        'height' => 10,
        'type' => 1,//1矩形,2圆形
        'save_path' => __DIR__,
    ];

    private $fun = 'imagepng';

    private $targetResource = null;
    private $sourceResource = null;

    private $info = null;

    public function __construct($config)
    {
        if($config)
        $this->config = $config;
    }

    private function open()
    {
        if (is_file($this->config['file']))
            $this->info = getimagesize($this->config['file']);
    }

    /**
     * 图片裁剪
     */
    private function cutImage()
    {
        $this->open();
        //目标真彩色图像
        $this->targetResource = imagecreatetruecolor($this->config['width'], $this->config['height']);
        $file = $this->config['file'];
        //这一句一定要有
        imagesavealpha($this->targetResource, true);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($this->targetResource, 255, 255, 255, 127);
        imagefill($this->targetResource, 0, 0, $bg);
        //源图像
        switch ($this->info[2]) {
            case 1:
                $this->sourceResource = imagecreatefromgif($file);
                break;
            case 2:
                $this->sourceResource = imagecreatefromjpeg($file);
                break;
            case 3:
                $this->sourceResource = imagecreatefrompng($file);
                break;
            default:
                throw new \Exception('不支持的类型');
        }
        //目标图大小是固定的，为了保持清晰度，需要智能地，重新定义拷贝源图的范围

        $target_w = $this->config['width'];
        $target_h = $this->config['height'];
        $_source_w = $this->info[0];
        $_source_h = $this->info[1];

        if ($target_w > $target_h) {
            $percent = $target_w / $_source_w;
        } else {
            $percent = $target_h / $_source_h;
        }
        //按比例压缩，获得清晰源tmp
        $t_w = $_source_w * $percent;
        $t_h = $_source_h * $percent;
        $tmpResource = imagecreatetruecolor($t_w, $t_h);
        //按比例拷贝图片，获得清晰tmp图
        imagecopyresampled($tmpResource, $this->sourceResource, 0, 0, 0, 0, $t_w, $t_h, $_source_w, $_source_h);
        if ($this->config['type'] == 2) {
            //圆形，基于tmp图为源图，进行二次拷贝,获得目标图
            $w = $target_w;
            $h = $this->config['height'];
            $r = $w / 2; //圆半径
            for ($x = 0; $x < $w; $x++) {
                for ($y = 0; $y < $h; $y++) {
                    $rgbColor = imagecolorat($tmpResource, $x, $y);
                    if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) <= ($r * $r))) {
                        imagesetpixel($this->targetResource, $x, $y, $rgbColor);
                    }
                }
            }
        } else {
            //矩形，基于tmp图为源图，进行二次拷贝,获得目标图
            imagecopyresized($this->targetResource, $tmpResource, 0, 0, 0, 0, $this->config['width'], $this->config['height'], $t_w, $t_h);
        }
        imagedestroy($tmpResource);

        $type = image_type_to_extension($this->info[2], false);

        $this->fun = 'image' . $type;
    }


    private function destroy()
    {
        imagedestroy($this->sourceResource);
        imagedestroy($this->targetResource);
    }

    public function save($save_name = '')
    {
        $this->cutImage();

        $fun = $this->fun;

        if (!is_dir($this->config['save_path'])) {
            mkdir($this->config['save_path']);
        }

        $sourceExt = strrchr($this->config['file'], ".");

        $ext = strtolower($sourceExt);

        if ($save_name) {
            $save_path = $this->config['save_path'] . DIRECTORY_SEPARATOR . $save_name;
        } else {
            $save_name = uniqid() . $ext;
            $save_path = $this->config['save_path'] . DIRECTORY_SEPARATOR . $save_name;
        }

        $fun($this->targetResource, $save_path);

        $this->destroy();

        return $save_name;
    }

    public function show()
    {
        $this->cutImage();
        header('Content-Type: ' . $this->info['mime']);
        $fun = $this->fun;
        $fun($this->targetResource);
        $this->destroy();
    }


    public  function info(){
        return $this->info;
    }
}
