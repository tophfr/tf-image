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

if (!defined('IMAGETYPE_GIF')) define('IMAGETYPE_GIF', 1);
if (!defined('IMAGETYPE_JPEG')) define('IMAGETYPE_JPEG', 2);
if (!defined('IMAGETYPE_PNG')) define('IMAGETYPE_PNG', 3);
if (!defined('IMAGETYPE_WBMP')) define('IMAGETYPE_WBMP', 15);
if (!defined('IMAGETYPE_XBM')) define('IMAGETYPE_XBM', 16);

define('TF_IMAGE_ERROR_BAD_ARGUMENT', 1);
define('TF_IMAGE_ERROR_FILE_NOT_FOUND', 2);
define('TF_IMAGE_ERROR_FILE_UNREADABLE', 3);
define('TF_IMAGE_ERROR_TYPE_NOT_SUPPORTED', 4);
define('TF_IMAGE_ERROR_CREATION_FAILED', 5);
define('TF_IMAGE_ERROR_STORE_FAILED', 6);
define('TF_IMAGE_ERROR_FILTER_NOT_SUPPORTED', 7);

if (!defined('TF_IMAGE_DEFAULT_QUALITY')) define('TF_IMAGE_DEFAULT_QUALITY', 85);

if (!defined('TF_IMAGE_AUTOCROP_DEFAULT_TOLERANCE')) define('TF_IMAGE_AUTOCROP_DEFAULT_TOLERANCE', 0);

define('TF_CENTER', 0.5);
define('TF_MIDDLE', 0.5);
define('TF_LEFT', 0);
define('TF_RIGHT', 1);
define('TF_TOP', 0);
define('TF_BOTTOM', 1);

define('TF_RESIZE_X', 'x');
define('TF_RESIZE_Y', 'y');
define('TF_RESIZE_MAX', 'max');
define('TF_RESIZE_MIN', 'min');
define('TF_RESIZE_FIT', 'fit');
define('TF_RESIZE_CROP', 'crp');
