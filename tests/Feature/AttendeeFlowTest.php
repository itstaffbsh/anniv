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

    public function test_attendee_can_complete_registration_with_non_balisuperhost()
    {
        $admin = User::factory()->create();
        $company = Company::create(['name' => 'Other Corp']);
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
        $response->assertSee('Other Corp');

        // 2. Complete registration without department (since it's non-Balisuperhost)
        $response = $this->post(route('register.complete'), [
            'token' => 'test-token-123',
            'name' => 'John Doe',
            'phone' => '081234567890',
            'company_id' => $company->id,
        ]);

        $response->assertRedirect();

        $attendee->refresh();
        $this->assertEquals('John Doe', $attendee->name);
        $this->assertEquals('6281234567890', $attendee->phone);
        $this->assertNull($attendee->department_id);
        $this->assertEquals($company->id, $attendee->company_id);
        $this->assertEquals('registered', $attendee->status);

        // Cek email tiket dikirim
        Mail::assertSent(\App\Mail\TicketEmail::class);
    }

    public function test_attendee_can_complete_registration_with_balisuperhost()
    {
        $admin = User::factory()->create();
        $company = Company::create(['name' => 'Balisuperhost']);
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
            'company_id' => $company->id,
        ]);
        $response->assertSessionHasErrors(['department_id']);

        // 2. Complete registration with department
        $response = $this->post(route('register.complete'), [
            'token' => 'test-token-456',
            'name' => 'Jane Doe',
            'phone' => '081234567890',
            'company_id' => $company->id,
            'department_id' => $department->id,
        ]);

        $response->assertRedirect();

        $attendee->refresh();
        $this->assertEquals('Jane Doe', $attendee->name);
        $this->assertEquals('6281234567890', $attendee->phone);
        $this->assertEquals($department->id, $attendee->department_id);
        $this->assertEquals($company->id, $attendee->company_id);
        $this->assertEquals('registered', $attendee->status);
    }
}
