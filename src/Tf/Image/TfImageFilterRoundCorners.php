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
class TfImageFilterRoundCorners extends TfImageFilter
{

    var $radius, $bgd_color;
    var $topLeft = true, $topRight, $bottomLeft, $bottomRight;

    function __construct($radius = 20, $bgd_color = 'ffffff', $topLeft = true, $topRight = true, $bottomRight = true, $bottomLeft = true, $limitAuto = true)
    {
        $this->radius = $radius;
        $this->bgd_color = $bgd_color;
        $this->topLeft = $topLeft;
        $this->topRight = $topRight;
        $this->bottomRight = $bottomRight;
        $this->bottomLeft = $bottomLeft;
        $this->limitAuto = $limitAuto;
    }

    function apply($img)
    {

        if (!$this->radius || !$this->topLeft && !$this->topRight && !$this->bottomRight && !$this->bottomLeft) {
            return null;
        }

        $w = imagesx($img);
        $h = imagesy($img);

        if ($this->limitAuto) {
            $radiusW = min($this->radius, intval($w / 2));
            $radiusH = min($this->radius, intval($h / 2));
            $decalageW = min(intval($radiusW / 3), 9);
            $decalageH = min(intval($radiusH / 3), 9);
            $decalage = min($decalageW, $decalageH);
        } else {
            $radiusW = $radiusH = $this->radius;
            $decalageW = $decalageH = $decalage = min(intval($this->radius / 3), 9);
        }

        $corner_images = array();
        for ($i = $decalage; $i >= 0; $i--) {
            $radiusW2 = $radiusW - $decalageW + $i;
            $radiusH2 = $radiusH - $decalageH + $i;
            $corner_image = imagecreatetruecolor($radiusW2 * 2, $radiusH2 * 2);
            $clear_colour =
                imagecolorallocate($corner_image
                    , 255 - hexdec(substr($this->bgd_color, 0, 2))
                    , 255 - hexdec(substr($this->bgd_color, 2, 2))
                    , 255 - hexdec(substr($this->bgd_color, 4, 2))
                );
            $solid_colour =
                imagecolorallocate($corner_image
                    , hexdec(substr($this->bgd_color, 0, 2))
                    , hexdec(substr($this->bgd_color, 2, 2))
                    , hexdec(substr($this->bgd_color, 4, 2))
                );
            imagecolortransparent($corner_image, $clear_colour);
            imagefill($corner_image, 0, 0, $solid_colour);
            imagefilledellipse($corner_image, $radiusW2, $radiusH2, $radiusW2 * 2, $radiusH2 * 2, $clear_colour);
            $corner_images[] = array('i' => $i, 'corner_image' => $corner_image, 'radiusW' => $radiusW2, 'radiusH' => $radiusH2);
        }

        imagesavealpha($img, true);

        if ($this->topLeft)
            foreach ($corner_images as $x) {
                imagecopymerge($img, $x['corner_image'], 0, 0, 0, 0, $x['radiusW'], $x['radiusH'], 100 - $x['i'] * 10);
            }
        if ($this->topRight)
            foreach ($corner_images as $x) {
                imagecopymerge($img, $x['corner_image'], $w - $x['radiusW'], 0, $x['radiusW'], 0, $x['radiusW'], $x['radiusH'], 100 - $x['i'] * 10);
            }
        if ($this->bottomRight)
            foreach ($corner_images as $x) {
                imagecopymerge($img, $x['corner_image'], $w - $x['radiusW'], $h - $x['radiusH'], $x['radiusW'], $x['radiusH'], $x['radiusW'], $x['radiusH'], 100 - $x['i'] * 10);
            }
        if ($this->bottomLeft)
            foreach ($corner_images as $x) {
                imagecopymerge($img, $x['corner_image'], 0, $h - $x['radiusH'], 0, $x['radiusH'], $x['radiusW'], $x['radiusH'], 100 - $x['i'] * 10);
            }

        foreach ($corner_images as $x) {
            imagedestroy($x['corner_image']);
        }

        return $img;
    }

    function isSupported()
    {
        return function_exists('imagefilledellipse');
    }
}
