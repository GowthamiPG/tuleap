<?php
/*
 * Copyright (c) STMicroelectronics, 2011. All Rights Reserved.
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once dirname(__FILE__).'/../include/GitDriver.class.php';


class GitDriverTest extends UnitTestCase {

    public function setUp() {
        $this->fixturesPath = dirname(__FILE__).'/_fixtures';
    }

    public function tearDown() {
        @unlink($this->fixturesPath.'/tmp/hooks/blah');
        @unlink($this->fixturesPath.'/tmp/config');
    }

    public function testActivateHook() {
        copy($this->fixturesPath.'/hooks/post-receive', $this->fixturesPath.'/tmp/hooks/blah');

        $driver = new GitDriver();
        $driver->activateHook('blah', $this->fixturesPath.'/tmp');

        $this->assertTrue(is_executable($this->fixturesPath.'/tmp/hooks/blah'));
    }

    public function testSetConfigSimple() {
        copy($this->fixturesPath.'/config', $this->fixturesPath.'/tmp/config');

        $driver = new GitDriver();
        $driver->setConfig($this->fixturesPath.'/tmp', 'hooks.showrev', 'abcd');

        $config = parse_ini_file($this->fixturesPath.'/tmp/config', true);
        $this->assertEqual($config['hooks']['showrev'], 'abcd');
    }

    public function testSetConfigComplex() {
        copy($this->fixturesPath.'/config', $this->fixturesPath.'/tmp/config');

        $val = "t=%s; git log --name-status --pretty='format:URL:    https://codendi.org/plugins/git/index.php/1750/view/290/?p=git.git&a=commitdiff&h=%%H%%nAuthor: %%an <%%ae>%%nDate:   %%aD%%n%%n%%s%%n%%b' \$t~1..\$t";

        $driver = new GitDriver();
        $driver->setConfig($this->fixturesPath.'/tmp', 'hooks.showrev', $val);

        $config = parse_ini_file($this->fixturesPath.'/tmp/config', true);
        $this->assertEqual($config['hooks']['showrev'], 't=%s; git log --name-status --pretty=\'format:URL:    https://codendi.org/plugins/git/index.php/1750/view/290/?p=git.git&a=commitdiff&h=%%H%%nAuthor: %%an <%%ae>%%nDate:   %%aD%%n%%n%%s%%n%%b\' $t~1..$t');
    }
}
?>