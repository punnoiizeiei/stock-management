<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Ink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InkStockFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Department $dept;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dept = Department::create([
            'name' => 'แผนกไอทีทดสอบ',
            'code' => 'IT-TEST',
        ]);

        $this->admin = User::create([
            'name' => 'แอดมินทดสอบ',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'department_id' => $this->dept->id,
        ]);
    }

    public function test_guest_can_access_dashboard_without_login(): void
    {
        Ink::create([
            'code' => 'DASH-01',
            'name' => 'Epson 003 Black',
            'brand' => 'Epson',
            'model' => '003',
            'color' => 'Black',
            'color_code' => '#111827',
            'type' => 'Bottle',
            'unit' => 'ขวด',
            'current_stock' => 10,
            'min_stock' => 2,
        ]);

        Ink::create([
            'code' => 'DASH-02',
            'name' => 'Canon 790 Yellow',
            'brand' => 'Canon',
            'model' => '790',
            'color' => 'Yellow',
            'color_code' => '#facc15',
            'type' => 'Bottle',
            'unit' => 'ขวด',
            'current_stock' => 1,
            'min_stock' => 3,
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('ระบบจัดการสต็อกและเบิก-รับหมึก');
        $response->assertSee('พนักงานทุกคนสามารถตรวจสอบสต็อกและขอเบิกหมึก');
        $response->assertSee('003');
        $response->assertSee('790');
    }

    public function test_guest_can_view_inks_catalog(): void
    {
        Ink::create([
            'code' => 'TEST-INK-99',
            'name' => 'Epson 003 Test Model',
            'brand' => 'Epson',
            'model' => '003',
            'color' => 'Black',
            'color_code' => '#111827',
            'type' => 'Ink Bottle',
            'unit' => 'ขวด',
            'current_stock' => 10,
            'min_stock' => 2,
        ]);

        $response = $this->get(route('inks.index'));

        $response->assertStatus(200);
        $response->assertSee('Epson 003 Test Model');
    }

    public function test_guest_can_disburse_stock_out_without_login(): void
    {
        $ink = Ink::create([
            'code' => 'INK-GUEST-01',
            'name' => 'HP LaserJet 85A',
            'brand' => 'HP',
            'model' => '85A',
            'color' => 'Black',
            'color_code' => '#111827',
            'type' => 'Toner Cartridge',
            'unit' => 'ตลับ',
            'current_stock' => 5,
            'min_stock' => 2,
        ]);

        // A regular employee with no login submits requisition
        $response = $this->post(route('transactions.stock_out.store'), [
            'ink_id' => $ink->id,
            'quantity' => 2,
            'person_name' => 'นายสมชาย พนักงานทั่วไป',
            'department_id' => $this->dept->id,
            'printer_target' => 'ชั้น 2 โต๊ะประชาสัมพันธ์',
            'reference_no' => 'REQ-GUEST-01',
            'action_date' => now()->format('Y-m-d'),
            'notes' => 'เบิกใช้งานด่วน ไม่ต้องล็อกอิน',
        ]);

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');

        // Stock decreased from 5 to 3
        $this->assertEquals(3, $ink->fresh()->current_stock);

        // Transaction recorded
        $this->assertDatabaseHas('ink_transactions', [
            'ink_id' => $ink->id,
            'type' => 'out',
            'quantity' => 2,
            'person_name' => 'นายสมชาย พนักงานทั่วไป',
            'stock_before' => 5,
            'stock_after' => 3,
        ]);
    }

    public function test_cannot_disburse_more_than_available_stock(): void
    {
        $ink = Ink::create([
            'code' => 'INK-LOW-01',
            'name' => 'HP 85A Limited',
            'brand' => 'HP',
            'model' => '85A',
            'color' => 'Black',
            'color_code' => '#111827',
            'type' => 'Toner Cartridge',
            'unit' => 'ตลับ',
            'current_stock' => 2,
            'min_stock' => 1,
        ]);

        $response = $this->post(route('transactions.stock_out.store'), [
            'ink_id' => $ink->id,
            'quantity' => 5,
            'person_name' => 'วิชัย ขอเบิกเกิน',
            'department_id' => $this->dept->id,
            'action_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(2, $ink->fresh()->current_stock);
    }

    public function test_guest_cannot_access_admin_stock_in_or_management(): void
    {
        // Guest cannot access Stock In
        $resStockIn = $this->get(route('transactions.stock_in'));
        $resStockIn->assertRedirect(route('login'));
        $resStockIn->assertSessionHas('error');

        // Guest cannot access Create Ink
        $resInk = $this->get(route('inks.create'));
        $resInk->assertRedirect(route('login'));

        // Guest cannot access Users Management
        $resUsers = $this->get(route('users.index'));
        $resUsers->assertRedirect(route('login'));

        // Guest cannot access Departments Management
        $resDepts = $this->get(route('departments.index'));
        $resDepts->assertRedirect(route('login'));
    }

    public function test_admin_can_login_and_access_admin_functions(): void
    {
        $loginRes = $this->post(route('login.store'), [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $loginRes->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->admin);

        // Admin can access Stock In
        $stockInRes = $this->actingAs($this->admin)->get(route('transactions.stock_in'));
        $stockInRes->assertStatus(200);

        // Admin can access Create Ink
        $inkRes = $this->actingAs($this->admin)->get(route('inks.create'));
        $inkRes->assertStatus(200);

        // Admin can access Users
        $usersRes = $this->actingAs($this->admin)->get(route('users.index'));
        $usersRes->assertStatus(200);
    }

    public function test_admin_can_receive_stock_in(): void
    {
        $ink = Ink::create([
            'code' => 'INK-IN-01',
            'name' => 'Canon Test Ink',
            'brand' => 'Canon',
            'model' => 'GI-790',
            'color' => 'Cyan',
            'color_code' => '#0284c7',
            'type' => 'Ink Bottle',
            'unit' => 'ขวด',
            'current_stock' => 5,
            'min_stock' => 2,
        ]);

        $response = $this->actingAs($this->admin)->post(route('transactions.stock_in.store'), [
            'ink_id' => $ink->id,
            'quantity' => 10,
            'person_name' => 'แอดมินผู้รับเข้า',
            'reference_no' => 'PO-TEST-001',
            'action_date' => now()->format('Y-m-d'),
            'notes' => 'รับเข้าสต็อกโดยแอดมิน',
        ]);

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');

        $this->assertEquals(15, $ink->fresh()->current_stock);
    }
}
