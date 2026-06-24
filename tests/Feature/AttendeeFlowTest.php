<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\Company;
use App\Models\Attendee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AttendeeFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Http::fake();
    }

    public function test_admin_can_manage_departments()
    {
        $admin = User::factory()->create();

        // 1. Post to store department
        $response = $this->actingAs($admin)
            ->post(route('admin.departments.store'), [
                'name' => 'IT Department',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('departments', [
            'name' => 'IT Department',
        ]);

        $department = Department::first();

        // 2. Delete department
        $response = $this->actingAs($admin)
            ->delete(route('admin.departments.destroy', $department->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('departments', [
            'name' => 'IT Department',
        ]);
    }

    public function test_admin_can_manage_companies()
    {
        $admin = User::factory()->create();

        // 1. Post to store company
        $response = $this->actingAs($admin)
            ->post(route('admin.companies.store'), [
                'name' => 'IT Corp',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('companies', [
            'name' => 'IT Corp',
        ]);

        $company = Company::first();

        // 2. Delete company
        $response = $this->actingAs($admin)
            ->delete(route('admin.companies.destroy', $company->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('companies', [
            'name' => 'IT Corp',
        ]);
    }

    public function test_admin_can_invite_attendee()
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)
            ->post(route('admin.invite'), [
                'email' => 'attendee@example.com',
                'invite_phone' => '081234567890',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendees', [
            'email' => 'attendee@example.com',
            'invite_phone' => '6281234567890',
            'status' => 'invited',
        ]);

        // Cek email dikirim
        Mail::assertSent(\App\Mail\InvitationEmail::class);
    }

    public function test_attendee_can_complete_registration_with_vendor()
    {
        $admin = User::factory()->create();
        $department = Department::create(['name' => 'Finance']);

        $attendee = Attendee::create([
            'email' => 'attendee@example.com',
            'invite_phone' => '6281234567890',
            'invitation_token' => 'test-token-123',
            'status' => 'invited',
        ]);

        // 1. Show registration form
        $response = $this->get(route('register.form', 'test-token-123'));
        $response->assertStatus(200);

        // 2. Complete registration as Vendor
        $response = $this->post(route('register.complete'), [
            'token' => 'test-token-123',
            'name' => 'John Doe',
            'phone' => '081234567890',
            'company_type' => 'Vendor',
        ]);

        $response->assertRedirect();

        $attendee->refresh();
        $this->assertEquals('John Doe', $attendee->name);
        $this->assertEquals('6281234567890', $attendee->phone);
        $this->assertNull($attendee->department_id);
        $this->assertEquals('Vendor', $attendee->company);
        $this->assertEquals('registered', $attendee->status);

        // Cek email tiket dikirim
        Mail::assertSent(\App\Mail\TicketEmail::class);
    }

    public function test_attendee_can_complete_registration_with_others()
    {
        $admin = User::factory()->create();

        $attendee = Attendee::create([
            'email' => 'attendee_other@example.com',
            'invite_phone' => '6281234567890',
            'invitation_token' => 'test-token-789',
            'status' => 'invited',
        ]);

        // Complete registration as Others with custom company
        $response = $this->post(route('register.complete'), [
            'token' => 'test-token-789',
            'name' => 'Alex Doe',
            'phone' => '081234567890',
            'company_type' => 'Others',
            'company_other' => 'Pemerintah Provinsi Bali',
        ]);

        $response->assertRedirect();

        $attendee->refresh();
        $this->assertEquals('Alex Doe', $attendee->name);
        $this->assertEquals('6281234567890', $attendee->phone);
        $this->assertNull($attendee->company_id);
        $this->assertEquals('Pemerintah Provinsi Bali', $attendee->company);
        $this->assertEquals('registered', $attendee->status);
    }

    public function test_attendee_can_complete_registration_with_balisuperhost()
    {
        $admin = User::factory()->create();
        $department = Department::create(['name' => 'IT']);

        $attendee = Attendee::create([
            'email' => 'attendee2@example.com',
            'invite_phone' => '6281234567890',
            'invitation_token' => 'test-token-456',
            'status' => 'invited',
        ]);

        // 1. Trying to complete registration without department (should fail validation)
        $response = $this->post(route('register.complete'), [
            'token' => 'test-token-456',
            'name' => 'Jane Doe',
            'phone' => '081234567890',
            'company_type' => 'Balisuperhost',
        ]);
        $response->assertSessionHasErrors(['department_id']);

        // 2. Complete registration with department
        $response = $this->post(route('register.complete'), [
            'token' => 'test-token-456',
            'name' => 'Jane Doe',
            'phone' => '081234567890',
            'company_type' => 'Balisuperhost',
            'department_id' => $department->id,
        ]);

        $response->assertRedirect();

        $attendee->refresh();
        $this->assertEquals('Jane Doe', $attendee->name);
        $this->assertEquals('6281234567890', $attendee->phone);
        $this->assertEquals($department->id, $attendee->department_id);
        $this->assertEquals('Balisuperhost', $attendee->company);
        $this->assertEquals('registered', $attendee->status);
    }

    public function test_public_attendee_can_self_register_as_balisuperhost()
    {
        $department = Department::create(['name' => 'IT']);

        $response = $this->post(route('register.complete'), [
            'email' => 'self_balisuperhost@example.com',
            'name' => 'Self Balisuperhost',
            'phone' => '081234567891',
            'company_type' => 'Balisuperhost',
            'department_id' => $department->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendees', [
            'email' => 'self_balisuperhost@example.com',
            'name' => 'Self Balisuperhost',
            'phone' => '6281234567891',
            'company' => 'Balisuperhost',
            'department_id' => $department->id,
            'status' => 'registered',
        ]);
        
        $attendee = Attendee::where('email', 'self_balisuperhost@example.com')->first();
        $this->assertNotNull($attendee->qr_token);
        $this->assertEquals(20, strlen($attendee->qr_token));
    }

    public function test_public_attendee_can_self_register_as_others()
    {
        $response = $this->post(route('register.complete'), [
            'email' => 'self_other@example.com',
            'name' => 'Self Other',
            'phone' => '081234567892',
            'company_type' => 'Others',
            'company_other' => 'Digital Agency Inc',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendees', [
            'email' => 'self_other@example.com',
            'name' => 'Self Other',
            'phone' => '6281234567892',
            'company' => 'Digital Agency Inc',
            'company_id' => null,
            'department_id' => null,
            'status' => 'registered',
        ]);
    }

    public function test_public_self_registration_fails_if_already_registered()
    {
        Attendee::create([
            'email' => 'registered@example.com',
            'name' => 'Already Registered',
            'phone' => '6281234567890',
            'status' => 'registered',
        ]);

        $response = $this->post(route('register.complete'), [
            'email' => 'registered@example.com',
            'name' => 'New Name',
            'phone' => '081234567890',
            'company_type' => 'Vendor',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseMissing('attendees', [
            'email' => 'registered@example.com',
            'name' => 'New Name',
        ]);
    }

    public function test_public_self_registration_updates_invited_record()
    {
        Attendee::create([
            'email' => 'invited_only@example.com',
            'invite_phone' => '6281234567890',
            'status' => 'invited',
        ]);

        $response = $this->post(route('register.complete'), [
            'email' => 'invited_only@example.com',
            'name' => 'Invited Guy',
            'phone' => '081234567890',
            'company_type' => 'Vendor',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendees', [
            'email' => 'invited_only@example.com',
            'name' => 'Invited Guy',
            'status' => 'registered',
        ]);
    }
}
