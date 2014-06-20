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
class TfImageFilterAutoCrop extends TfImageFilter
{

    var $bgColor;
    var $tolerance;

    /**
     * @param mixed $bgColor values : 'auto' (default), 'one' (only one color) or un color identifier from imagecolorat()
     * @param float $tolerance (between 0 and 1)
     */
    function __construct($bgColor = 'auto', $tolerance = null)
    {
        $this->bgColor = $bgColor;
        $this->tolerance = is_null($tolerance) ? TF_IMAGE_AUTOCROP_DEFAULT_TOLERANCE : (float)$tolerance;
    }

    function _getColors($img, $x, $y)
    {
        if (is_int($this->bgColor)) {
            $bgColor = $this->bgColor;
        } elseif ($this->bgColor == 'one') {
            $bgColor = imagecolorat($img, 0, 0);
        } else { // if($this->bgColor == 'auto')
            $bgColor = imagecolorat($img, $x, $y);
        }
        $colorMin = (1 - $this->tolerance) * $bgColor;
        $colorMax = (1 + $this->tolerance) * $bgColor;
        return array($colorMin, $colorMax);
    }

    function apply($img)
    {

        $w = imagesx($img);
        $h = imagesy($img);

        // top
        list($colorMin, $colorMax) = $this->_getColors($img, 0, 0);
        $y1 = 0;
        for ($j = 0; $j < $h; $j++) {
            for ($i = 0; $i < $w; $i++) {
                $c = imagecolorat($img, $i, $j);
                if ($c < $colorMin || $c > $colorMax) {
                    $y1 = $j;
                    break 2;
                }
            }
        }

        // right
        if ($y1 == 0) list($colorMin, $colorMax) = $this->_getColors($img, $w - 1, 0);
        $x2 = $w - 1;
        for ($i = $x2; $i > 0; $i--) {
            for ($j = $y1; $j < $h; $j++) {
                $c = imagecolorat($img, $i, $j);
                if ($c < $colorMin || $c > $colorMax) {
                    $x2 = $i;
                    break 2;
                }
            }
        }

        // bottom
        if ($x2 == $w - 1) list($colorMin, $colorMax) = $this->_getColors($img, $w - 1, $h - 1);
        $y2 = $h - 1;
        for ($j = $y2; $j > $y1; $j--) {
            for ($i = 0; $i < $x2; $i++) {
                $c = imagecolorat($img, $i, $j);
                if ($c < $colorMin || $c > $colorMax) {
                    $y2 = $j;
                    break 2;
                }
            }
        }

        // left
        if ($y2 == $h - 1) list($colorMin, $colorMax) = $this->_getColors($img, 0, $h - 1);
        $x1 = 0;
        for ($i = 0; $i < $x2; $i++) {
            for ($j = $y1; $j < $y2; $j++) {
                $c = imagecolorat($img, $i, $j);
                if ($c < $colorMin || $c > $colorMax) {
                    $x1 = $i;
                    break 2;
                }
            }
        }

        if ($y1 == 0 && $x2 == $w - 1 && $y2 == $h - 1 && $x1 == 0) {
            // nothing to do
            imagealphablending($img, false);
            imagesavealpha($img, true);
            return null;
        }

        $w2 = $x2 - $x1 + 1;
        $h2 = $y2 - $y1 + 1;

        $new = @imagecreatetruecolor($w2, $h2);
        imagesavealpha($new, true);
        imagealphablending($new, false);
        imagecopy($new, $img, 0, 0, $x1, $y1, $w2, $h2);
        return $new;
    }

    function isSupported()
    {
        return function_exists('imagecreatetruecolor');
    }
}
