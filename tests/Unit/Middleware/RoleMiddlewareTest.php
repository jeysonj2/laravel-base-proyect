<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\RoleMiddleware;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected RoleMiddleware $middleware;

    public function setUp(): void
    {
        parent::setUpWithAuth();
        
        $this->middleware = new RoleMiddleware();
    }

    #[Test]
    public function it_allows_access_to_users_with_required_role()
    {
        $this->actingAs($this->admin);
        
        $request = Request::create('/api/roles', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . $this->adminToken);
        
        $next = function ($request) {
            return new Response('OK');
        };
        
        $response = $this->middleware->handle($request, $next, 'admin');
        
        $this->assertEquals('OK', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function it_denies_access_to_users_without_required_role()
    {
        $this->actingAs($this->regularUser);
        
        $request = Request::create('/api/roles', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . $this->userToken);
        
        $next = function ($request) {
            return new Response('OK');
        };
        
        $this->expectException(AccessDeniedHttpException::class);
        
        $this->middleware->handle($request, $next, 'admin');
    }

    #[Test]
    public function it_allows_access_when_multiple_roles_are_specified_and_user_has_one_of_them()
    {
        $this->actingAs($this->regularUser);
        
        $request = Request::create('/api/route-with-multiple-roles', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . $this->userToken);
        
        $next = function ($request) {
            return new Response('OK');
        };
        
        $response = $this->middleware->handle($request, $next, 'admin,user');
        
        $this->assertEquals('OK', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function it_denies_access_when_multiple_roles_are_specified_and_user_has_none_of_them()
    {
        // Create a user with a different role
        $guestRole = Role::create(['name' => 'guest']);
        $guestUser = User::factory()->create([
            'name' => 'Guest',
            'last_name' => 'User',
            'email' => 'guest@example.com',
            'role_id' => $guestRole->id,
        ]);
        
        $this->actingAs($guestUser);
        
        $request = Request::create('/api/route-with-multiple-roles', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . JWTAuth::fromUser($guestUser));
        
        $next = function ($request) {
            return new Response('OK');
        };
        
        $this->expectException(AccessDeniedHttpException::class);
        
        $this->middleware->handle($request, $next, 'admin,user');
    }
}
