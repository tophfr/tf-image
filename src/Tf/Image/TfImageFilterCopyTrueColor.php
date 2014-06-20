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
class TfImageFilterCopyTrueColor extends TfImageFilter
{

    function apply(&$img)
    {
        $width = imagesx($img);
        $height = imagesy($img);
        $new = imagecreatetruecolor($width, $height);
        imagecopy($new, $img, 0, 0, 0, 0, $width, $height);
        return $new;
    }

    function isSupported()
    {
        return function_exists('imagecopy');
    }
}
