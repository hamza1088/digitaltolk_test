<?php

namespace DTApi\Helpers;

use PHPUnit\Framework\TestCase;

class TeHelper extends TestCase
{
    public function testWillExpireAt()
    {
        $dueTime = '2023-05-16 10:00:00';
        $createdAt = '2023-05-15 14:30:00';

        $expectedResult = '2023-05-16 10:00:00';

        $result = Helper::willExpireAt($dueTime, $createdAt);

        $this->assertEquals($expectedResult, $result);
    }
}
