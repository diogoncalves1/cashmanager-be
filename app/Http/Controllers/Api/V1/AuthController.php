<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Auth\AuthChangePasswordRequest;
use App\Http\Requests\Auth\AuthResetChangePasswordRequest;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\User\Entities\User;
use Modules\User\Events\EmailVerified;
use Modules\User\Events\ResetPassword;
use Modules\User\Http\Resources\UserResource;
use Modules\User\Repositories\UserRepository;

class AuthController extends ApiController
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(Request $request)
    {
        try {
            if (! $request->validate(['email' => 'required|email|unique:users|max:191'])) {
                throw new \Exception('Erro ao efetuar login');
            }
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
    public function resetPassword(Request $request)
    {
        try {
            $user = $this->userRepository->getUserByEmail($request->get('email'));

            event(new ResetPassword($user));

            return $this->ok();
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    public function resetChangePassword(AuthResetChangePasswordRequest $request)
    {
        try {
            $this->userRepository->updatePassword($request);

            return $this->ok();
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    public function changePassword(AuthChangePasswordRequest $request)
    {
        try {
            if (! $this->userRepository->checkPassword($request)) {
                throw new \Exception('Palavra-Passe atual inválida.', 403);
            }

            $this->userRepository->updatePassword($request);

            return $this->ok();
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }
}
