<?php
/**
 * Copyright (c) Enalean, 2018. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tuleap\Docman\Item;

use Docman_EmbeddedFile;
use Docman_Empty;
use Docman_File;
use Docman_Folder;
use Docman_Item;
use Docman_Link;
use Docman_Wiki;

interface ItemVisitor
{
    public function visitFolder(Docman_Folder $item, array $params = []);

    public function visitWiki(Docman_Wiki $item, array $params = []);

    public function visitLink(Docman_Link $item, array $params = []);

    public function visitFile(Docman_File $item, array $params = []);

    public function visitEmbeddedFile(Docman_EmbeddedFile $item, array $params = []);

    public function visitEmpty(Docman_Empty $item, array $params = []);

    public function visitItem(Docman_Item $item, array $params = []);
}
