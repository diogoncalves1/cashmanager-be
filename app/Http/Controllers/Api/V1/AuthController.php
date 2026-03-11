<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\User\Entities\User;
use Modules\User\Events\EmailVerified;
use Modules\User\Http\Requests\UserRequest;
use Modules\User\Http\Resources\UserResource;
use Modules\User\Repositories\UserRepository;

class AuthController extends ApiController
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(UserRequest $request)
    {
        try {
            $this->userRepository->store($request);

            return $this->ok();
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    public function login(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages(['email' => ['Credenciais invalidas.']]);
            }

            if (! $user->hasVerifiedEmail()) {
                throw new \Exception('Valide o seu email antes de efetuar login.', 403);
            }

            $token = $user->createToken('web-token')->plainTextToken;

            return response()->json(['user' => new UserResource($user), 'token' => $token], 201);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com successo']);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return $this->ok(new UserResource($user));
    }

    public function verifyEmail(Request $request)
    {
        try {
            $user = $request->user();
            if ($user->hasVerifiedEmail()) {
                return $this->ok();
            }

            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
                event(new EmailVerified($user));
            }

            return $this->ok();
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }
}
