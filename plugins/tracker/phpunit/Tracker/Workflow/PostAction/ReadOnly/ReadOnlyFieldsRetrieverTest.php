<?php
/**
 * Copyright (c) Enalean, 2019-Present. All Rights Reserved.
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

declare(strict_types=1);

namespace Tuleap\Tracker\Workflow\PostAction\ReadOnly;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

final class ReadOnlyFieldsRetrieverTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var Mockery\MockInterface */
    private $read_only_dao;
    /** @var ReadOnlyFieldsRetriever */
    private $read_only_fields_retriever;

    protected function setUp(): void
    {
        $this->read_only_dao              = Mockery::mock(ReadOnlyDao::class);
        $this->read_only_fields_retriever = new ReadOnlyFieldsRetriever($this->read_only_dao);
    }

    public function testGetReadOnlyFieldsReturnsASinglePostAction()
    {
        $this->read_only_dao->shouldReceive('searchByTransitionId')->andReturn(
            [
                ['postaction_id' => 72, 'field_id' => 331],
                ['postaction_id' => 72, 'field_id' => 651],
                ['postaction_id' => 72, 'field_id' => 987]
            ]
        );

        $transition           = Mockery::mock(\Transition::class)->shouldReceive(['getId' => 97])->getMock();
        $expected_post_action = new ReadOnlyFields($transition, 72, [331, 651, 987]);

        $result = $this->read_only_fields_retriever->getReadOnlyFields($transition);
        $this->assertEquals($expected_post_action, $result);
    }

    public function testGetReadOnlyFieldsThrowsWhenNoPostAction()
    {
        $this->read_only_dao->shouldReceive('searchByTransitionId')->andReturn([]);

        $transition = Mockery::mock(\Transition::class)->shouldReceive(['getId' => 97])->getMock();

        $this->expectException(NoReadOnlyFieldsPostActionException::class);
        $this->read_only_fields_retriever->getReadOnlyFields($transition);
    }
}
