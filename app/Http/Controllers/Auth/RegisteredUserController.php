<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function checkField(Request $request): JsonResponse
    {
        $field = $request->query('field');
        $value = trim($request->query('value', ''));

        if (!in_array($field, ['phone', 'email'], true) || $value === '') {
            return response()->json(['status' => 'invalid']);
        }

        $taken = User::where($field, $value)->exists();

        return response()->json(['status' => $taken ? 'taken' : 'available']);
    }

    public function checkNik(Request $request): JsonResponse
    {
        $nik = preg_replace('/\D/', '', $request->query('nik', ''));

        if (strlen($nik) !== 16) {
            return response()->json(['status' => 'invalid']);
        }

        $hash = User::hashNik($nik);
        $taken = $hash && User::where('nik_hash', $hash)->exists();

        return response()->json(['status' => $taken ? 'taken' : 'available']);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik' => [
                'required',
                'digits:16',
                // unique:users,nik tidak bekerja karena kolom NIK di-encrypt dengan random IV.
                // Gunakan nik_hash (SHA-256 deterministik) untuk cek duplikat.
                function (string $_attribute, mixed $value, \Closure $fail) {
                    $hash = User::hashNik($value);
                    if ($hash && User::where('nik_hash', $hash)->exists()) {
                        $fail('NIK sudah terdaftar.');
                    }
                },
            ],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'g-recaptcha-response' => ['required', 'recaptcha'],
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits'   => 'NIK harus tepat 16 digit angka.',
            'phone.unique' => 'Nomor HP sudah terdaftar.',
            'email.unique' => 'Email sudah terdaftar.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'nik' => $request->nik,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),

        ]);

        // Assign default role: User-Individual
        $userIndividualRole = \App\Models\Role::where('name', 'User-Individual')->first();
        if ($userIndividualRole) {
            $user->roles()->attach($userIndividualRole->id);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('user.dashboard', absolute: false));
    }
}
