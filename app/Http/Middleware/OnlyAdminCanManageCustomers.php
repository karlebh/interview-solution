<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use App\Models\Customer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OnlyAdminCanManageCustomers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $customerId = $request->route('customer');

        $customer =  Customer::find($customerId);

        if (! $customer) {
            return response()->json(["message" => "Customer with id {$customerId} does not exist"], 404);
        }

        if ($user->role === RoleEnum::ADMIN->value || $customer->user_id === $user->id) {
            return $next($request);
        }

        return response()->json(['message' => 'You are not authourized to perform this action'], 403);
    }
}
