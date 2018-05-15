<?php
/**
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
 *        new TfImageFilterResize($width, $height)
 *    or
 *        new TfImageFilterResize($size, $mode)
 * @param $mode is one of (TF_RESIZE_X, TF_RESIZE_Y, TF_RESIZE_MAX, TF_RESIZE_MIN)
 *    or
 *        new TfImageFilterResize($width, $height, $mode, $position)
 * @param $mode is one of (TF_RESIZE_FIT, TF_RESIZE_CROP)
 * @param $position ratio (default value : TF_CENTER => 0.5)
 */
class TfImageFilterResize extends TfImageFilter
{
    var $width;
    var $height;
    var $mode;
    var $positionH;
    var $positionV;
    var $dontResizeIfBiggerThanSource;
    var $dontCropIfSmaller;
    var $bgColor = 'auto';
    var $bgMirror = false;

    /**        new TfImageFilterResize($width, $height)
     * or    new TfImageFilterResize($size, $mode)
     * $mode : TF_RESIZE_X, TF_RESIZE_Y, TF_RESIZE_MAX, TF_RESIZE_MIN
     * or    new TfImageFilterResize($width, $height, $mode)
     * $mode : TF_RESIZE_FIT, TF_RESIZE_CROP
     */
    function __construct($width, $height = TF_RESIZE_MAX, $mode = null, $positionH = TF_CENTER, $positionV = null, $dontResizeIfBiggerThanSource = false)
    {
        if (TF_RESIZE_X == $height || TF_RESIZE_Y == $height || TF_RESIZE_MAX == $height || TF_RESIZE_MIN == $height) {
            $this->height = $width;
            $this->mode = $height;
        } else {
            $this->height = $height;
            $this->mode = $mode;
            $this->positionH = $positionH;
            $this->positionV = $positionV ? $positionV : $positionH;
        }
        $this->width = $width;
        $this->dontResizeIfBiggerThanSource = (bool)$dontResizeIfBiggerThanSource;
    }

    function setDontResizeIfBiggerThanSource($dontResizeIfBiggerThanSource)
    {
        $this->dontResizeIfBiggerThanSource = (bool)$dontResizeIfBiggerThanSource;
    }

    function setDontCropIfSmaller($dontCropIfSmaller)
    {
        switch ($dontCropIfSmaller) {
            case 'W':
            case 'w':
                $this->dontCropIfSmaller = 'w';
                break;
            case 'H':
            case 'h':
            case '1':
                $this->dontCropIfSmaller = 'h';
                break;
            default:
                $this->dontCropIfSmaller = null;
        }
    }

    function setBgColor($bgColor)
    {
        $this->bgColor = $bgColor;
    }

    function setBgMirror($bgMirror)
    {
        $this->bgMirror = (float)$bgMirror;
    }

    function apply($img)
    {

        $x1 = $y1 = $x2 = $y2 = 0;

        $w0 = $w1 = imagesx($img);
        $h0 = $h1 = imagesy($img);

        if ($this->width && substr($this->width, -1) == '%') {
            $w2 = $w1 * (substr($this->width, 0, strlen($this->width) - 1) / 100);
        } else {
            $w2 = $this->width;
        }
        if ($this->height && substr($this->height, -1) == '%') {
            $h2 = $h1 * (substr($this->height, 0, strlen($this->height) - 1) / 100);
        } else {
            $h2 = $this->height;
        }

        if (!$w2) {
            $w2 = $h2;
        } elseif (!$h2) {
            $h2 = $w2;
        }

        $w = $w2;
        $h = $h2;

        $mode = $this->mode;
        if ($mode == TF_RESIZE_CROP && $this->dontCropIfSmaller) {
            $r = $h2 && $w2 ? $h2 / $w2 : 1;
            if ($this->dontCropIfSmaller == 'h') {
                if ($h0 / $w0 > $r) {
                    $mode = TF_RESIZE_Y;
                }
            } else {
                if ($h0 / $w0 <= $r) {
                    $mode = TF_RESIZE_X;
                }
            }
        }

        if ($mode) {
            if (TF_RESIZE_CROP == $mode) {
                if ($w1 / $h1 > $w2 / $h2) {
                    $w1 = $h1 * $w2 / $h2;
                    $x1 = ($w0 - $w1) * $this->positionH;
                } else {
                    $h1 = $w1 * $h2 / $w2;
                    $y1 = ($h0 - $h1) * $this->positionV;
                }
            } elseif (TF_RESIZE_FIT == $mode) {
                if ($w1 / $h1 > $w2 / $h2) {
                    $h2 = round($w2 * $h1 / $w1);
                    $y2 = round(($h - $h2) * $this->positionV);
                } else {
                    $w2 = round($h2 * $w1 / $h1);
                    $x2 = round(($w - $w2) * $this->positionH);
                }
            } elseif (TF_RESIZE_X == $mode) {
                $h = $h2 = $w2 * $h1 / $w1;
            } elseif (TF_RESIZE_Y == $mode) {
                $w = $w2 = $h2 * $w1 / $h1;
            } elseif (TF_RESIZE_MAX == $mode) {
                if ($w1 / $h1 > $w2 / $h2) {
                    $w = $w2 = $h2 * $w1 / $h1;
                } else {
                    $h = $h2 = $w2 * $h1 / $w1;
                }
            } elseif (TF_RESIZE_MIN) {
                if ($w1 / $h1 > $w2 / $h2)
                    $h = $h2 = $w2 * $h1 / $w1;
                else
                    $w = $w2 = $h2 * $w1 / $h1;
            }
        }

        if ($x1 == 0 && $y1 == 0 && $x2 == 0 && $y2 == 0 && $w == $w1 && $w1 == $w2 && $w1 == $w0 && $h == $h1 && $h1 == $h2 && $h1 == $h0 || ($this->dontResizeIfBiggerThanSource && $w > $w0 && $h > $h0)) {
            imagealphablending($img, false);
            imagesavealpha($img, true);
            return null;
        }

        $new = imagecreatetruecolor($w, $h);
        imagealphablending($new, false);
        imagesavealpha($new, true);
        imageantialias($new, true);
        $transparent = imagecolorallocatealpha($new, 255, 255, 255, 127);
        imagefill($new, 0, 0, $transparent);
        imagecolortransparent($new, $transparent);

        if ($x2 > 0 || $y2 > 0 || $w2 < $w || $h2 < $h) {

            if ($this->bgColor) {
                if (preg_match('/^[0-9a-f]{6}$/', $this->bgColor)) {
                    $bg = imagecolorallocate($new
                        , hexdec(substr($this->bgColor, 0, 2))
                        , hexdec(substr($this->bgColor, 2, 2))
                        , hexdec(substr($this->bgColor, 4, 2))
                    );
                } else {
                    $bg = imagecolorat($img, 0, 0);
                }
                imagefill($new, 0, 0, $bg);
            }

            if ($this->bgMirror) {
                if ($w > $h) {
                    $w1p = ceil($w1 * $this->bgMirror);
                    if ($x2 > 0) {
                        imagecopyresampled($new, $img, 0, $y2, $x1 + $w1p - 1, $y1, $x2, $h2, -$w1p, $h1);
                    }
                    if ($x2 + $w2 < $w) {
                        imagecopyresampled($new, $img, $x2 + $w2 - 1, $y2, $x1 + $w1 - 1, $y1, $w - $x2 - $w2 + 2, $h2, -$w1p, $h1);
                    }
                } else {
                    $h1p = ceil($h1 * $this->bgMirror);
                    if ($y2>0) {
                        imagecopyresampled($new, $img, $x2, 0, $x1, $y1 + $h1p - 1, $w2, $y2, $w1, -$h1p);
                    }
                    if ($y2 + $w2 < $h) {
                        imagecopyresampled($new, $img, $x2, $y2 + $h2 - 1, $x1, $y1 + $h1 - 1, $w2, $h - $y2 - $h2 + 2, $w1, -$h1p);
                    }
                }
            }

        }

        imagecopyresampled($new, $img, $x2, $y2, $x1, $y1, $w2, $h2, $w1, $h1);

        return $new;
    }

    function isSupported()
    {
        return function_exists('imagecopyresampled');
    }

}
