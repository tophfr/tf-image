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
class TfImageFilterRotate extends TfImageFilter
{

    var $angle, $bgd_color;

    function __construct($angle = 90, $bgd_color = 'ffffff')
    {
        $this->angle = $angle;
        $this->bgd_color = (int)base_convert($bgd_color, 16, 10);
    }

    function apply($img)
    {

        if (function_exists('imagerotate')) {
            return imagerotate($img, $this->angle, $this->bgd_color);
        }

        $x = imagesx($img);
        $y = imagesy($img);

        switch ($this->simpleAngle()) {

            case 90 :
                $new = imagecreatetruecolor($y, $x);
                for ($i = 0; $i < $x; $i++) for ($j = 0; $j < $y; $j++) {
                    imagesetpixel($new, $y - $j - 1, $i, imagecolorat($img, $i, $j));
                }
                return $new;

            case 180 :
                $new = imagecreatetruecolor($x, $y);
                for ($i = 0; $i < $x; $i++) for ($j = 0; $j < $y; $j++) {
                    imagesetpixel($new, $x - $i - 1, $y - $j, imagecolorat($img, $i, $j));
                }
                return $new;

            case 270 :
                $new = imagecreatetruecolor($y, $x);
                for ($i = 0; $i < $x; $i++) for ($j = 0; $j < $y; $j++) {
                    imagesetpixel($new, $j, $x - $i - 1, imagecolorat($img, $i, $j));
                }
                return $new;

            default :
                return $img;
        }
    }

    function simpleAngle()
    {
        $angle = $this->angle % 360;
        if ($angle < 0) $angle += 360;
        foreach (array(90, 180, 270) as $x) {
            if ($angle >= $x - 45 && $angle < $x + 45) return $x;
        }
        return 0;
    }

    function isSupported()
    {
        return function_exists('imagerotate') || function_exists('imagecolorat') && function_exists('imagesetpixel');
    }
}
