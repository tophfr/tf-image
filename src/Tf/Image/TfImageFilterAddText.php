<?php
/**
 * @author Vincent Lemaire
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
class TfImageFilterAddText extends TfImageFilter
{

    var $size, $angle, $x, $y, $color, $fontfile, $width;
    private $text = array();

    /*
     * parametre :
     * @img 		correspond à l'image, elle est nécessaire pour imagecolorallocate (rendre la couleur obligatoire rendrait ce paramètre inutile)
     * @mixedtext 	peut être
     * 		- un text simple (utilisatble avec addFilterByString)
     * 		- un tableau de texts (un text => une ligne)
     * 		- une collection de TfImageFilterAddTextParameter (permet de gérer les retour automatique à la ligne => un obj, un paragraphe.)
     * @x			abcisse de l'origine des text
     * @y			ordonnée de l'origine des text
     * @size		taille du texte, ou taille par défaut des TfImageFilterAddTextParameter
     * @fontfile	font au format TTF et chemin absolu
     * @color		couleur du text (noir par défaut)
     * @angle		angle du text
     * @width		utilisé uniquement avec TfImageFilterAddTextParameter, largeur de la taille de dessin
     */
    function __construct($img, $mixedtext, $x, $y, $size = 10, $fontfile, $color = false, $angle = 0, $width = false)
    {
        if (is_array($mixedtext)) {
            $this->text = $mixedtext;
        } else {
            $this->text[] = $mixedtext;
        }
        $this->x = $x; //todo permettre %
        $this->y = $y; //todo permettre %
        $this->size = $size; //todo permettre %
        $this->fontfile = $fontfile;

        if ($color) {
            $this->color = $color;
        } else {
            $this->color = imagecolorallocate($img, 0, 0, 0);
        }
        $this->angle = $angle;

        $this->width = $width;
    }

    function apply($img)
    {

        foreach ($this->text as $mixed) {
            if (is_object($mixed)) {
                if ($this->width > 0) {
                    $mixed->setWidth($this->width);
                }
                if (!$mixed->size) {
                    $mixed->size = $this->size;
                }
                foreach ($mixed->text as $txt) {
                    $this->y += $mixed->size * 1.5;
                    imagettftext($img, $mixed->size, $this->angle, $this->x + $mixed->x, $this->y, $this->color, $this->fontfile, $txt);
                }
                $this->y += $mixed->spacer;
            } else {
                imagettftext($img, $this->size, $this->angle, $this->x, $this->y, $this->color, $this->fontfile, $mixed);
                $this->y += $this->size * 1.5;
            }
        }
        return $img;
    }

    function isSupported()
    {
        return function_exists('imagettftext') && file_exists($this->fontfile);
    }
}

class TfImageFilterAddTextParameter
{
    var $left, $spacer, $size;
    var $text = array();

    function TfImageFilterAddTextParameter($text, $left, $spacer = 0, $size = false)
    {
        $this->x = $left;
        $this->spacer = $spacer;
        $this->size = $size;
        $this->text = is_array($text) ? $text : array($text);
    }

    function setWidth($width)
    {
        $n = ($width - $this->x) / $this->size * 1.8; // TODO mieux calculer ?
        $res = array();
        foreach ($this->text as $txt) {
            $res = array_merge($res, explode('|', wordwrap($txt, $n, '|')));
        }
        $this->text = $res;
    }
}
