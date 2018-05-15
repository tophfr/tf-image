<?php
/*
 * @author toph <toph@toph.fr>
 *
 * This file is a part of the TfLib and ExploTf Project.
 *
 * TfLib and ExploTf are the legal property of its developers, whose names
 * may be too numerous to list here. Please refer to the COPYRIGHT file
 * distributed with this source distribution.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * Class TfImage
 */
class TfImage
{

    /**
     * @var resource
     */
    var $img;
    /**
     * @var
     */
    /**
     * @var
     */
    /**
     * @var
     */
    var $sourceFile, $sourceMode, $unChanged;
    /**
     * @var array
     */
    var $filters;
    /**
     * @var int
     */
    var $errno;
    /**
     * @var null
     */
    var $logCallBack = null;

    /**
     * TfImage constructor.
     * @param null $mixed
     * @param null $height
     * @param null $bgColor
     * @param bool $alpha
     *
     * new TfImage()
     * new TfImage(string $filePath)
     * new TfImage(resource $gdResource)
     * new TfImage(int $width, int $height, string (hexa) $bgColor=null, bool $alpha=false)
     * new TfImage(string binaryData, 'binary')
     */
    function __construct($mixed = null, $height = null, $bgColor = null, $alpha = false)
    {
        $this->filters = array();
        $this->img = null;
        $this->errno = 0;
        if ($mixed) {
            if ($height) {
                if ($height == 'binary') {
                    $this->img = imagecreatefromstring($mixed);
                } elseif (!is_numeric($mixed) || !is_numeric($height)) {
                    $this->errno = TF_IMAGE_ERROR_BAD_ARGUMENT;
                } else {
                    $this->img = @imagecreatetruecolor($mixed, $height);
                    if ($alpha) {
                        imagealphablending($this->img, false);
                        imagesavealpha($this->img, true);
                    }
                    if ($bgColor && preg_match('/^[0-9a-f]{6}$/i', $bgColor)) {
                        $bg = imagecolorallocate($this->img
                            , hexdec(substr($bgColor, 0, 2))
                            , hexdec(substr($bgColor, 2, 2))
                            , hexdec(substr($bgColor, 4, 2))
                        );
                        imagefill($this->img, 0, 0, $bg);
                    }
                }
            } elseif (is_resource($mixed) && 'gd' == get_resource_type($mixed)) {
                $this->img = $mixed;
            } elseif (is_string($mixed)) {
                $this->loadFile($mixed);
            } else {
                $this->img = imagecreatefromstring($mixed);
            }
        }
    }

    /**
     * @param $file
     * @return bool
     */
    function loadFile($file)
    {
        $this->sourceFile = $file;
        if (!$file) {
            $this->errno = TF_IMAGE_ERROR_BAD_ARGUMENT;
            return false;
        }
        if (!preg_match('/^http:\/\//', $file)) {
            if (!file_exists($file)) {
                $this->errno = TF_IMAGE_ERROR_FILE_NOT_FOUND;
                return false;
            }
            if (!is_readable($file)) {
                $this->errno = TF_IMAGE_ERROR_FILE_UNREADABLE;
                return false;
            }
        }
        list($width, $height, $this->sourceMode) = @getimagesize($file);
        $function = false;
        switch ($this->sourceMode) {
            case IMAGETYPE_GIF :
                $function = 'imagecreatefromgif';
                break;
            case IMAGETYPE_JPEG :
                $function = 'imagecreatefromjpeg';
                break;
            case IMAGETYPE_PNG :
                $function = 'imagecreatefrompng';
                break;
            case IMAGETYPE_WBMP :
                $function = 'imagecreatefromwbmp';
                break;
            case IMAGETYPE_XBM :
                $function = 'imagecreatefromxbm';
                break;
            //default : $function = 'imagecreatefromgd2' ;
        }
        if (!$function || !function_exists($function)) {
            $this->errno = TF_IMAGE_ERROR_TYPE_NOT_SUPPORTED;
            return false;
        }
        $this->img = $function($file);
        $this->unChanged = true;
        if (!$this->img) {
            $this->errno = TF_IMAGE_ERROR_CREATION_FAILED;
            return false;
        }
        return true;
    }

    /**
     * @param $file
     * @return bool
     */
    function loadFileGD($file)
    {
        $this->sourceFile = $file;
        if (!$file) {
            $this->errno = TF_IMAGE_ERROR_BAD_ARGUMENT;
            return false;
        }
        if (!file_exists($file)) {
            $this->errno = TF_IMAGE_ERROR_FILE_NOT_FOUND;
            return false;
        }
        if (!is_readable($file)) {
            $this->errno = TF_IMAGE_ERROR_FILE_UNREADABLE;
            return false;
        }
        $this->img = @imagecreatefromgd($file);
        $this->unChanged = true;
        $this->sourceMode = null;
        return true;
    }

    /**
     * @param $file
     * @return bool
     */
    function loadFileGD2($file)
    {
        $this->sourceFile = $file;
        if (!$file) {
            $this->errno = TF_IMAGE_ERROR_BAD_ARGUMENT;
            return false;
        }
        if (!file_exists($file)) {
            $this->errno = TF_IMAGE_ERROR_FILE_NOT_FOUND;
            return false;
        }
        if (!is_readable($file)) {
            $this->errno = TF_IMAGE_ERROR_FILE_UNREADABLE;
            return false;
        }
        $this->img = @imagecreatefromgd2($file);
        $this->unChanged = true;
        $this->sourceMode = null;
        return true;
    }

    /**
     * @param $filter
     * @return bool
     */
    function addFilter($filter)
    {
        if (!is_subclass_of($filter, 'TfImageFilter')) {
            $this->errno = TF_IMAGE_ERROR_BAD_ARGUMENT;
            return false;
        }
        if (!$filter->isSupported()) {
            $this->errno = TF_IMAGE_ERROR_FILTER_NOT_SUPPORTED;
            return false;
        }
        $this->filters[] = $filter;
        return true;
    }

    /**
     * @param $i
     */
    function removeFilterAt($i)
    {
        unset($this->filters[$i]);
    }

    /**
     * @param bool $free
     */
    function removeFilters($free = false)
    {
        if ($free) foreach ($this->filters as $filter) $filter->free();
        $this->filters = array();
    }

    /**
     * @return resource
     */
    function make()
    {
        $img = $this->img;
        foreach ($this->filters as $filter) if ($filter) {
            $tmp = $filter->apply($img);
            if ($tmp) {
                $this->unChanged = false;
                if ($img != $tmp && $img != $this->img) {
                    imagedestroy($img); // free memory
                }
                $img = $tmp;
            }
        }
        return $img;
    }

    /**
     * @param string $file
     * @return bool|int
     */
    function storeFileGD($file = '')
    {
        return $this->_storeFile('imagegd', $file);
    }

    /**
     * @param string $file
     * @return bool|int
     */
    function storeFileGD2($file = '')
    {
        return $this->_storeFile('imagegd2', $file);
    }

    /**
     * @param null $file
     * @param null $type
     * @param int $quality
     * @param bool $copyIfUnChanged
     * @return bool|int
     */
    function storeFile($file = null, $type = null, $quality = TF_IMAGE_DEFAULT_QUALITY, $copyIfUnChanged = true)
    {

        if (!$type) {
            if (preg_match('/\.png$/i', $file)) {
                $type = IMAGETYPE_PNG;
            } elseif (preg_match('/\.gif$/i', $file)) {
                $type = IMAGETYPE_GIF;
            } elseif (preg_match('/\.jpg$/i', $file)) {
                $type = IMAGETYPE_JPEG;
            } else {
                $type = IMAGETYPE_JPEG;
            }
        }

        switch ($type) {
            case IMAGETYPE_JPEG :
                $function = 'imagejpeg';
                break;
            case IMAGETYPE_PNG :
                $function = 'imagepng';
                break;
            case IMAGETYPE_WBMP :
                $function = 'imagewbmp';
                break;
            case IMAGETYPE_XBM :
                $function = 'imagexbm';
                break;
            case IMAGETYPE_GIF :
                $function = 'imagegif';
                break;
            default :
                $function = '';
        }
        return $this->_storeFile($function, $file, $quality, $copyIfUnChanged && $type == $this->sourceMode);
    }

    /**
     * @param $function
     * @param null $file
     * @param int $quality
     * @param bool $copyIfUnChanged
     * @return bool|int
     */
    function _storeFile($function, $file = null, $quality = TF_IMAGE_DEFAULT_QUALITY, $copyIfUnChanged = false)
    {
        if (!$function || !function_exists($function)) {
            $this->errno = TF_IMAGE_ERROR_TYPE_NOT_SUPPORTED;
            return false;
        }
        $img = $this->make();
        if ($this->unChanged && $copyIfUnChanged && $this->sourceFile) {
            if ($file) {
                $bool = copy($this->sourceFile, $file);
            } else {
                $bool = readfile($this->sourceFile);
            }
        } elseif ($function == 'imagejpeg') {
            $bool = $function($img, $file, $quality);
        } elseif ($function == 'imagepng') {
            $quality = 9 - round(9 * $quality / 100);
            $bool = $function($img, $file, $quality);
        } elseif ($file) {
            $bool = $function($img, $file);
        } else {
            $bool = $function($img);
        }
        if (!$bool) {
            $this->errno = TF_IMAGE_ERROR_STORE_FAILED;
        }
        if ($img != $this->img) {
            imagedestroy($img);
        }
        return $bool;
    }

    /**
     * @param int $type
     * @param int $quality
     * @return bool|int
     */
    function out($type = IMAGETYPE_JPEG, $quality = TF_IMAGE_DEFAULT_QUALITY)
    {
        return $this->storeFile(null, $type, $quality);
    }

    /**
     * @return int
     */
    function getOriginalWidth()
    {
        return imagesx($this->img);
    }

    /**
     * @return int
     */
    function getOriginalHeight()
    {
        return imagesy($this->img);
    }

    /**
     *
     */
    function free()
    {
        $this->removeFilters(true);
        if ($this->img && is_resource($this->img)) {
            @imagedestroy($this->img);
        }
        $this->img = null;
    }

    /**
     * @param $logCallBack
     */
    function setLogCallBack($logCallBack)
    {
        $this->logCallBack = $logCallBack;
    }

    /**
     * @param $message
     */
    function log($message)
    {
        if ($this->logCallBack) {
            call_user_func($this->logCallBack, $message);
        }
    }

    /**
     *
     */
    function __destruct()
    {
        $this->free();
    }

    /**
     * @param $stringFilters mixed (string or array)
     */
    function addFiltersByString($stringFilters)
    {
        if (is_array($stringFilters)) {
            $stringFilters = implode(',', $stringFilters);
        }
        $modes = preg_split('/(?<!\\\\),/', $stringFilters);
        foreach ($modes as $m) {
            $options = preg_split('/(?<!\\\\):/', $m);
            switch (strtolower($options[0])) {

                case 'rsz':
                case 'resize':
                    /*	'[mode:]width:height:vertical align:horizontal align:dontResizeIfBiggerThanSource:dontCropIfSmaller:bgColor'
                        default values :
                            - mode : x if w and not h, y if h and not w, null otherwise
                            - height : =width
                            - vertical align : 0.5 (center)
                            - horizontal align : =vertical align
                            - dontResizeIfBiggerThanSource : 0
                            - dontCropIfSmaller : 'h': by height, 'w' or 1: by width
                            - bgColor : ffffff or auto
                            - bgMirror : 1
                    */
                    $i = 1;
                    if (is_numeric($options[$i]) || !$options[$i]) {
                        // mode non précisé
                        $w = (int)$options[$i];
                        $i++;
                        $h = isset($options[$i]) ? (int)$options[$i] : 0;
                        if ($w && !$h) {
                            $mode = TF_RESIZE_X;
                            $h = $w;
                        } elseif (!$w && $h) {
                            $mode = TF_RESIZE_Y;
                            $w = $h;
                        } else {
                            $mode = null;
                        }
                    } else {
                        $mode = $options[$i];
                        $i++;
                        $w = isset($options[$i]) ? (int)$options[$i] : 0;
                        $i++;
                        $h = (isset($options[$i]) && $options[$i]) ? (int)$options[$i] : $w;
                    }
                    $i++;
                    $positionH = isset($options[$i]) && $options[$i] !== '' ? (float)$options[$i] : TF_CENTER;
                    $i++;
                    $positionV = isset($options[$i]) && $options[$i] !== '' ? (float)$options[$i] : $positionH;
                    $i++;
                    $dontResizeIfBiggerThanSource = isset($options[$i]) ? (bool)$options[$i] : false;
                    $i++;
                    $dontCropIfSmaller = isset($options[$i]) && $options[$i] !== '' ? $options[$i] : false;
                    $i++;
                    $bgColor = isset($options[$i]) ? $options[$i] : false;
                    $i++;
                    $bgMirror = isset($options[$i]) ? $options[$i] : false;
                    if ($w && $h) {
                        $filter = new TfImageFilterResize($w, $h, $mode, $positionH, $positionV, $dontResizeIfBiggerThanSource);
                        if ($dontCropIfSmaller !== false) $filter->setDontCropIfSmaller($dontCropIfSmaller);
                        if ($bgColor !== false) $filter->setBgColor($bgColor);
                        if ($bgMirror !== false) $filter->setBgMirror($bgMirror);
                        $this->addFilter($filter);
                    } else {
                        $this->log("Missing dimensions in '$m'");
                    }
                    break;

                case 'crp':
                case 'crop':
                    /*	'x:y:width:height'
                     */
                    $i = 1;
                    $x = (float)$options[$i];
                    $i++;
                    $y = (isset($options[$i]) && is_numeric($options[$i])) ? (float)$options[$i] : -1;
                    $i++;
                    $w = (isset($options[$i]) && is_numeric($options[$i]) && $options[$i]) ? (float)$options[$i] : -1;
                    $i++;
                    $h = (isset($options[$i]) && is_numeric($options[$i]) && $options[$i]) ? (float)$options[$i] : -1;
                    $this->addFilter(new TfImageFilterCrop($x, $y, $w, $h));
                    break;

                case 'autocrop':
                    $bgColor = isset($options[1]) && $options[1] ? $options[1] : 'auto';
                    $tolerance = isset($options[2]) && is_numeric($options[2]) ? (float)$options[2] : 0;
                    $this->addFilter(new TfImageFilterAutoCrop($bgColor, $tolerance));
                    break;

                case 'add':
                case 'addimg':
                case 'addimage':
                    if (isset($options[1]) && $options[1]) {
                        $i = strpos($options[1], '|');
                        if ($i && $i > 0) {
                            $image = new TfImage();
                            if (!$image->loadFile(substr($options[1], 0, $i))) {
                                //TODO: erreur
                                break;
                            }
                            $image->addFiltersByString(stripslashes(substr($options[1], $i + 1)));
                        } else {
                            $image = $options[1];
                            if (!file_exists($image)) {
                                //TODO: erreur
                                break;
                            }
                        }
                        $opacity = isset($options[2]) && $options[2] >= 0 ? (int)$options[2] : 100;
                        $x = isset($options[3]) && $options[3] > 0 ? $options[3] : 0;
                        $y = isset($options[4]) && $options[4] > 0 ? $options[4] : 0;
                        $this->addFilter(new TfImageFilterAddImage($image, $x, $y, $opacity));
                    }
                    break;

                case 'round' :
                case 'roundcorners' :
                    $radius = isset($options[1]) && $options[1] ? (int)$options[1] : 20;
                    $color = isset($options[2]) && $options[2] ? $options[2] : 'ffffff';
                    $topLeft = isset($options[3]) && $options[3] !== '' ? (bool)$options[3] : true;
                    $topRight = isset($options[4]) && $options[4] !== '' ? (bool)$options[4] : true;
                    $bottomLeft = isset($options[5]) && $options[5] !== '' ? (bool)$options[5] : true;
                    $bottomRight = isset($options[6]) && $options[6] !== '' ? (bool)$options[6] : true;
                    $limitAuto = isset($options[7]) && $options[7] !== '' ? (bool)$options[7] : true;
                    $this->addFilter(new TfImageFilterRoundCorners($radius, $color, $topLeft, $topRight, $bottomLeft, $bottomRight, $limitAuto));
                    break;

                case 'gray' :
                    $ncolors = isset($options[1]) && $options[1] ? (int)$options[1] : 256;
                    $this->addFilter(new TfImageFilterGrayscale($ncolors));
                    break;

                case 'bg' :
                case 'bgcolor' :
                case 'background' :
                case 'backgroundcolor' :
                    $red = isset($options[1]) && $options[1] ? (int)$options[1] : 255;
                    $green = isset($options[2]) && $options[2] ? (int)$options[2] : 255;
                    $blue = isset($options[3]) && $options[3] ? (int)$options[3] : 255;
                    $this->addFilter(new TfImageFilterBgColor($red, $green, $blue));
                    break;

                case 'rotate':
                    $angle = isset($options[1]) && $options[1] ? (int)$options[1] : 90;
                    $bgd_color = isset($options[2]) && $options[2] ? hexdec($options[2]) : 0xffffff;
                    $this->addFilter(new TfImageFilterRotate($angle, $bgd_color));
                    break;

                case 'truecolor' :
                    $this->addFilter(new TfImageFilterCopyTrueColor());
                    break;

                default:
                    $this->log("Unknown filter in '$m'");
            }
        }
    }

}
