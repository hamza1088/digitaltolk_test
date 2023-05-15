<?php

namespace DTApi\Repository;

use PHPUnit\Framework\TestCase;

/**
 * Class BookingRepository
 * @package DTApi\Repository
 */
class UserRepositoryTest extends TestCase
{
    public function testCreateOrUpdate()
    {
        $request = [
            'role' => 'translator',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret',
            //other required fields based on the function implementation
        ];

        $user = new User();
        $result = $user->createOrUpdate(null, $request);
        $this->assertNotFalse($result);

        $this->assertEquals('translator', $user->user_type);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        // Add assertions for other fields...
    }
}
