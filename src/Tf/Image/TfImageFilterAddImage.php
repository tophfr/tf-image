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
class TfImageFilterAddImage extends TfImageFilter
{

    var $img, $x, $y, $pct;

    function __construct($img, $x = 0, $y = 0, $pct = 100)
    {
        $this->img = $img;
        $this->x = str_replace('|', '', $x);
        $this->y = str_replace('|', '', $y);
        $this->pct = $pct;
    }

    function apply($img)
    {

// TODO: prevoir la possibilité d'écrire "50%-20"

        if (is_object($this->img)) {
            $img1 = $this->img->make();
        } elseif (is_resource($this->img)) {
            $img1 = $this->img;
        } else {
            $tf = new TfImage($this->img);
            if ($tf->errno > 0) {
                return null;
            }
            $this->img = $tf;
            $img1 = $this->img->img;
        }
//			imagesavealpha($img1, true);
//			imagealphablending($img1, false);
        imagecolortransparent($img1, imagecolorat($img1, 0, 0));
        $w = imagesx($img1);
        $h = imagesy($img1);

        if (strpos($this->x, '%') !== false) {
            $wO = imagesx($img);
            $wP = intval($this->x) / 100;
            $this->x = ($wO * $wP) - $w / 2;
        } else
            $this->x = (int)$this->x;

        if (strpos($this->y, '%') !== false) {
            $hO = imagesy($img);
            $hP = intval($this->y) / 100;
            $this->y = ($hO * $hP) - $w / 2;
        } else
            $this->y = (int)$this->y;

        if (function_exists('imagecopymerge')) {
            imagecopymerge($img, $img1, $this->x, $this->y, 0, 0, $w, $h, $this->pct);
        } else {
            imagecopy($img, $img1, $this->x, $this->y, 0, 0, $w, $h);
        }
        return $img;
    }

    function isSupported()
    {
        return function_exists('imagecopy') || function_exists('imagecopymerge');
    }

    function free()
    {
        if (is_object($this->img)) {
            $this->img->free();
        } elseif (is_resource($this->img)) {
            @imagedestroy($this->img);
        }
        $this->img = null;
    }
}
