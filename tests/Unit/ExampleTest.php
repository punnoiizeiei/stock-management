<?php

namespace Tests\Unit;

use App\Models\Ink;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_ink_stock_status_methods(): void
    {
        $ink = new Ink([
            'current_stock' => 1,
            'min_stock' => 2,
        ]);

        $this->assertTrue($ink->isLowStock());
        $this->assertFalse($ink->isOutOfStock());
        $this->assertEquals('low', $ink->stock_status);
        $this->assertEquals('ใกล้หมด', $ink->status_label);

        $emptyInk = new Ink([
            'current_stock' => 0,
            'min_stock' => 2,
        ]);

        $this->assertTrue($emptyInk->isOutOfStock());
        $this->assertFalse($emptyInk->isLowStock());
        $this->assertEquals('out', $emptyInk->stock_status);
        $this->assertEquals('หมดสต็อก', $emptyInk->status_label);
    }

    public function test_user_role_methods(): void
    {
        $admin = new User(['role' => 'admin']);
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isUser());
        $this->assertEquals('ผู้ดูแลระบบ (Admin)', $admin->role_label);

        $user = new User(['role' => 'user']);
        $this->assertTrue($user->isUser());
        $this->assertFalse($user->isAdmin());
        $this->assertEquals('ผู้ใช้งานทั่วไป (User)', $user->role_label);
    }
}
