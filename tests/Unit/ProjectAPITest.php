<?php

namespace Modules\Project\Tests\Unit;


use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CriticalEdition\Models\CriticalEdition;
use Modules\Project\Models\Project;
use Modules\Project\Models\ProjectRole;
use Tests\TestCase;

class ProjectAPITest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        ProjectRole::factory()->count(1)->create();

        // Disable auth:api middleware globally for all tests
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\Authenticate::class);

        // Disable EnsureClientIsAllowed middleware globally for all tests
        $this->withoutMiddleware(\App\Http\Middleware\EnsureClientIsAllowed::class);

    }

    public function testProjectWithRelatedUsersStore(): void
    {
        $project = Project::factory()->make();
        $user = User::factory()->count(2)->create();

        $data = [
            'type' => 'projects',
            'attributes' => [
                'name' => $project->name,
                'description' => $project->description,
                'project_type' => $project->project_type,
            ],
            'relationships' => [
                'users' => [
                    'data' => $user->map(fn(User $user) => [
                        'type' => 'users',
                        'id' => (string) $user->getRouteKey(),
                    ])->all(),
                ],
            ],
        ];

        $response = $this
            ->jsonApi()
            ->expects('projects')
            ->withData($data)
            ->post('/api/v1/projects');

        $id = $response
            ->assertCreatedWithServerId('http://localhost/api/v1/projects', $data)
            ->id();

        $this->assertDatabaseHas('projects', [
            'id' => $id,
            'name' => $project->name,
            'description' => $project->description,
            'project_type' => $project->project_type,
        ]);
    }

    public function testUserIndex(): void
    {
        $project = Project::factory()->create();
        $users = User::factory()->count(2)->create();
        $role = ProjectRole::factory()->create(); // Create a specific role
        $project->users()->attach($users, ['role_id' => $role->id]); // Use the created role's ID

        $expected = $users->map(fn(User $user) => [
            'type' => 'users',
            'id' => (string) $user->getRouteKey(),
            'attributes' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ])->all();

        $response = $this
            ->jsonApi()
            ->expects('users')
            ->get("/api/v1/projects/{$project->getRouteKey()}/users");

        $response->assertFetchedMany($expected);
    }

    public function testProjectWithRelatedCriticalEditionStore(): void
    {
        $project = Project::factory()->make();
        $criticalEdition = CriticalEdition::factory()->create();

        $data = [
            'type' => 'projects',
            'attributes' => [
                'name' => $project->name,
                'description' => $project->description,
                'project_type' => $project->project_type,
            ],
            'relationships' => [
                'scope' => [
                    'data' => [
                        'type' => 'critical-editions',
                        'id' => (string) $criticalEdition->getRouteKey(),
                    ]
                ]
            ],
        ];

        $response = $this
            ->jsonApi()
            ->expects('projects')
            ->withData($data)
            ->post('/api/v1/projects');

        $id = $response
            ->assertCreatedWithServerId('http://localhost/api/v1/projects', $data)
            ->id();

        $this->assertDatabaseHas('projects', [
            'id' => $id,
            'name' => $project->name,
            'description' => $project->description,
            'project_type' => $project->project_type,
            'projectable_id' => $criticalEdition->getKey(),
            'projectable_type' => CriticalEdition::class,
        ]);

        $this->assertDatabaseHas('critical_editions', [
            'id' => $criticalEdition->getKey()
        ]);
    }
}
