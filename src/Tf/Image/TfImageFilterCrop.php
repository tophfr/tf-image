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
class TfImageFilterCrop extends TfImageFilter
{

    var $mode, $x, $y, $width, $height;

    /**
     * Usage:
     *    TfImageFilterCrop($coordX, $coordY, $width, $height)
     *    or
     *    TfImageFilterCrop($position, $width, $height)
     *    or
     *    TfImageFilterCrop($position, $ratio)
     *
     *    $position: float between 0 and 1 / ex: 0=left or top, 0.5=center, 1=right or bottom
     *    $ratio: width/height
     */
    function __construct($x = -1, $y = -1, $width = -1, $height = -1)
    {
        if ($x >= 0 && $y >= 0 && $width > 0 && $height > 0) {
            $this->mode = -1;
            $this->x = $x;
            $this->y = $y;
            $this->width = $width;
            $this->height = $height;
        } else {
            $this->mode = $x;
            if ($y > 0 && $width > 0) {
                $this->width = $y;
                $this->height = $width;
                $this->x = $this->width / $this->height;
            } else {
                $this->x = ($y > 0) ? $y : 1;
            }
        }
    }

    function apply($img)
    {
        if ($this->mode >= 0) {
            $w1 = imagesx($img);
            $h1 = imagesy($img);
            $r1 = $w1 / $h1;
            $r = $this->x;

            if ($r > $r1) {
                // on colle les x
                $x = 0;
                $w = $w1;
                $h = $w1 / $r;
                $y = ($h1 - $h) * $this->mode;
            } else {
                // on colle les y
                $y = 0;
                $h = $h1;
                $w = $h1 * $r;
                $x = ($w1 - $w) * $this->mode;
            }

            if (!$this->width) $this->width = $w;
            if (!$this->height) $this->height = $h;

        } else {
            $x = $this->x;
            $y = $this->y;
            $w = $this->width;
            $h = $this->height;
        }

        if ($x == 0 && $y == 0 && $this->width == $w && $this->height == $h && $this->mode >= 0) {
            return null;
        }

        $new = @imagecreatetruecolor($this->width, $this->height);
        imagealphablending($new, false);
        imagesavealpha($new, true);

        if (function_exists('imagecopyresampled')) $function = 'imagecopyresampled';
        else $function = 'imagecopyresized';
        $function($new, $img, 0, 0, $x, $y, $this->width, $this->height, $w, $h);
        return $new;
    }

    function isSupported()
    {
        return function_exists('imagecopyresized') || function_exists('imagecopyresampled');
    }
}
