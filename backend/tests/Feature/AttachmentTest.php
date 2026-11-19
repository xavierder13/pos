<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AttachmentTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_upload_list_download_delete(): void
    {
        $this->actingAsAdmin();
        $user = User::factory()->create();

        $id = $this->post("/api/attachments/user/{$user->id}", ['file' => UploadedFile::fake()->create('contract.pdf', 120, 'application/pdf')], ['Accept' => 'application/json'])
            ->assertCreated()->assertJsonPath('data.name', 'contract.pdf')->json('data.id');

        $this->getJson("/api/attachments/user/{$user->id}")->assertOk()->assertJsonCount(1, 'data');
        $path = $user->attachments()->first()->path;
        Storage::disk('local')->assertExists($path);

        $this->get("/api/attachments/{$id}/download")->assertOk()->assertDownload('contract.pdf');

        $this->deleteJson("/api/attachments/{$id}")->assertOk();
        Storage::disk('local')->assertMissing($path);
        $this->getJson('/api/audit-trail?record=User&record_id='.$user->id)->assertJsonFragment(['field' => 'attachments']);
    }

    public function test_type_size_and_permission_rules(): void
    {
        $this->actingAsAdmin();
        $user = User::factory()->create();

        $this->post("/api/attachments/user/{$user->id}", ['file' => UploadedFile::fake()->create('virus.exe', 10)], ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJsonValidationErrors('file');
        $this->post("/api/attachments/user/{$user->id}", ['file' => UploadedFile::fake()->create('huge.pdf', 20000, 'application/pdf')], ['Accept' => 'application/json'])
            ->assertStatus(422);
        $this->getJson('/api/attachments/nothing/1')->assertNotFound();

        $this->actingAsUserWith(['user-list']);
        $this->getJson("/api/attachments/user/{$user->id}")->assertOk();
        $this->post("/api/attachments/user/{$user->id}", ['file' => UploadedFile::fake()->create('a.pdf', 5, 'application/pdf')], ['Accept' => 'application/json'])
            ->assertStatus(403);
    }

    public function test_deleting_the_record_deletes_its_files(): void
    {
        $this->actingAsAdmin();
        $user = User::factory()->create();
        $this->post("/api/attachments/user/{$user->id}", ['file' => UploadedFile::fake()->create('cv.pdf', 5, 'application/pdf')], ['Accept' => 'application/json']);
        $path = $user->attachments()->first()->path;

        $this->deleteJson("/api/users/{$user->id}")->assertOk();

        Storage::disk('local')->assertMissing($path);
        $this->assertDatabaseCount('attachments', 0);
    }
}
