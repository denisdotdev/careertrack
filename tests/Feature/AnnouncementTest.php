<?php

use App\Models\Announcement;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create();
    $this->admin = User::factory()->create();
    $this->manager = User::factory()->create();
    
    // Add users to company with different roles
    $this->company->addUser($this->admin, 'admin');
    $this->company->addUser($this->manager, 'manager');
    $this->company->addUser($this->user, 'member');
});

describe('Announcement Model', function () {
    it('can create an announcement', function () {
        $announcement = Announcement::factory()->create([
            'company_id' => $this->company->id,
        ]);

        expect($announcement)->toBeInstanceOf(Announcement::class);
        expect($announcement->title)->toBeString();
        expect($announcement->content)->toBeString();
        expect($announcement->company_id)->toBe($this->company->id);
    });

    it('has required fillable attributes', function () {
        $announcement = new Announcement();
        
        foreach (['title', 'content', 'company_id', 'status', 'priority', 'published_at'] as $field) {
            expect($announcement->getFillable())->toContain($field);
        }
    });

    it('belongs to a company', function () {
        $announcement = Announcement::factory()->create([
            'company_id' => $this->company->id,
        ]);

        expect($announcement->company)->toBeInstanceOf(Company::class);
        expect($announcement->company->id)->toBe($this->company->id);
    });

    it('can have different statuses', function () {
        $draft = Announcement::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'draft',
        ]);

        $published = Announcement::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'published',
        ]);

        $archived = Announcement::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'archived',
        ]);

        expect($draft->status)->toBe('draft');
        expect($published->status)->toBe('published');
        expect($archived->status)->toBe('archived');
    });

    it('can have different priorities', function () {
        $low = Announcement::factory()->create([
            'company_id' => $this->company->id,
            'priority' => 'low',
        ]);

        $medium = Announcement::factory()->create([
            'company_id' => $this->company->id,
            'priority' => 'medium',
        ]);

        $high = Announcement::factory()->create([
            'company_id' => $this->company->id,
            'priority' => 'high',
        ]);

        expect($low->priority)->toBe('low');
        expect($medium->priority)->toBe('medium');
        expect($high->priority)->toBe('high');
    });

    it('can have a published_at timestamp', function () {
        $publishedAt = now();
        
        $announcement = Announcement::factory()->create([
            'company_id' => $this->company->id,
            'published_at' => $publishedAt,
        ]);

        expect($announcement->published_at)->toBeInstanceOf(\Carbon\Carbon::class);
        expect($announcement->published_at->toDateTimeString())->toBe($publishedAt->toDateTimeString());
    });

    it('can be published', function () {
        $announcement = Announcement::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'draft',
            'published_at' => null,
        ]);

        $announcement->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        expect($announcement->fresh()->status)->toBe('published');
        expect($announcement->fresh()->published_at)->not->toBeNull();
    });

    it('can be archived', function () {
        $announcement = Announcement::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'published',
        ]);

        $announcement->update(['status' => 'archived']);

        expect($announcement->fresh()->status)->toBe('archived');
    });
});

describe('Announcement Factory', function () {
    it('creates valid announcement data', function () {
        $announcement = Announcement::factory()->create([
            'company_id' => $this->company->id,
        ]);

        expect($announcement->title)->not->toBeEmpty();
        expect($announcement->content)->not->toBeEmpty();
        expect($announcement->company_id)->toBe($this->company->id);
        expect($announcement->status)->toBeIn(['draft', 'published', 'archived']);
        expect($announcement->priority)->toBeIn(['low', 'medium', 'high']);
    });

    it('creates announcement with company when company_id not provided', function () {
        $announcement = Announcement::factory()->create();

        expect($announcement->company_id)->not->toBeNull();
        expect($announcement->company)->toBeInstanceOf(Company::class);
    });
});

describe('Announcement Relationships', function () {
    it('can access company through relationship', function () {
        $announcement = Announcement::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $company = $announcement->company;

        expect($company)->toBeInstanceOf(Company::class);
        expect($company->id)->toBe($this->company->id);
        expect($company->name)->toBe($this->company->name);
    });

    it('can access announcements through company relationship', function () {
        $announcement1 = Announcement::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $announcement2 = Announcement::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $companyAnnouncements = $this->company->announcements;

        expect($companyAnnouncements)->toHaveCount(2);
        expect($companyAnnouncements->pluck('id')->toArray())->toContain($announcement1->id);
        expect($companyAnnouncements->pluck('id')->toArray())->toContain($announcement2->id);
    });
});

describe('Announcement Scopes and Queries', function () {
    it('can filter by status', function () {
        Announcement::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'draft',
        ]);

        Announcement::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'published',
        ]);

        $draftAnnouncements = Announcement::where('status', 'draft')->get();
        $publishedAnnouncements = Announcement::where('status', 'published')->get();

        expect($draftAnnouncements)->toHaveCount(1);
        expect($publishedAnnouncements)->toHaveCount(1);
    });

    it('can filter by priority', function () {
        Announcement::factory()->create([
            'company_id' => $this->company->id,
            'priority' => 'high',
        ]);

        Announcement::factory()->create([
            'company_id' => $this->company->id,
            'priority' => 'low',
        ]);

        $highPriorityAnnouncements = Announcement::where('priority', 'high')->get();
        $lowPriorityAnnouncements = Announcement::where('priority', 'low')->get();

        expect($highPriorityAnnouncements)->toHaveCount(1);
        expect($lowPriorityAnnouncements)->toHaveCount(1);
    });

    it('can filter by company', function () {
        $otherCompany = Company::factory()->create();

        Announcement::factory()->create([
            'company_id' => $this->company->id,
        ]);

        Announcement::factory()->create([
            'company_id' => $otherCompany->id,
        ]);

        $companyAnnouncements = Announcement::where('company_id', $this->company->id)->get();
        $otherCompanyAnnouncements = Announcement::where('company_id', $otherCompany->id)->get();

        expect($companyAnnouncements)->toHaveCount(1);
        expect($otherCompanyAnnouncements)->toHaveCount(1);
    });

    it('can order by created_at', function () {
        $oldAnnouncement = Announcement::factory()->create([
            'company_id' => $this->company->id,
            'created_at' => now()->subDay(),
        ]);

        $newAnnouncement = Announcement::factory()->create([
            'company_id' => $this->company->id,
            'created_at' => now(),
        ]);

        $orderedAnnouncements = Announcement::orderBy('created_at', 'desc')->get();

        expect($orderedAnnouncements->first()->id)->toBe($newAnnouncement->id);
        expect($orderedAnnouncements->last()->id)->toBe($oldAnnouncement->id);
    });
});

describe('Announcement Validation', function () {
    it('requires title', function () {
        expect(function () {
            Announcement::create([
                'content' => 'Test content',
                'company_id' => $this->company->id,
            ]);
        })->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('requires content', function () {
        expect(function () {
            Announcement::create([
                'title' => 'Test title',
                'company_id' => $this->company->id,
            ]);
        })->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('requires company_id', function () {
        expect(function () {
            Announcement::create([
                'title' => 'Test title',
                'content' => 'Test content',
            ]);
        })->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('validates status enum values', function () {
        expect(function () {
            Announcement::factory()->create([
                'company_id' => $this->company->id,
                'status' => 'invalid_status',
            ]);
        })->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('validates priority enum values', function () {
        expect(function () {
            Announcement::factory()->create([
                'company_id' => $this->company->id,
                'priority' => 'invalid_priority',
            ]);
        })->toThrow(\Illuminate\Database\QueryException::class);
    });
});

describe('Announcement Database Constraints', function () {
    it('enforces foreign key constraint for company_id', function () {
        expect(function () {
            Announcement::factory()->create([
                'company_id' => 99999, // Non-existent company ID
            ]);
        })->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('cascades delete when company is deleted', function () {
        $announcement = Announcement::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $announcementId = $announcement->id;
        
        $this->company->delete();

        expect(Announcement::find($announcementId))->toBeNull();
    });
});

describe('Announcement with Role-Based Access', function () {
    it('can be created by admin users', function () {
        $this->actingAs($this->admin);

        $announcementData = [
            'title' => 'Admin Announcement',
            'content' => 'This is an admin announcement',
            'company_id' => $this->company->id,
            'status' => 'published',
            'priority' => 'high',
        ];

        $announcement = Announcement::create($announcementData);

        expect($announcement->title)->toBe('Admin Announcement');
        expect($announcement->company_id)->toBe($this->company->id);
    });

    it('can be created by manager users', function () {
        $this->actingAs($this->manager);

        $announcementData = [
            'title' => 'Manager Announcement',
            'content' => 'This is a manager announcement',
            'company_id' => $this->company->id,
            'status' => 'draft',
            'priority' => 'medium',
        ];

        $announcement = Announcement::create($announcementData);

        expect($announcement->title)->toBe('Manager Announcement');
        expect($announcement->company_id)->toBe($this->company->id);
    });

    it('can be created by regular members', function () {
        $this->actingAs($this->user);

        $announcementData = [
            'title' => 'Member Announcement',
            'content' => 'This is a member announcement',
            'company_id' => $this->company->id,
            'status' => 'draft',
            'priority' => 'low',
        ];

        $announcement = Announcement::create($announcementData);

        expect($announcement->title)->toBe('Member Announcement');
        expect($announcement->company_id)->toBe($this->company->id);
    });
});
