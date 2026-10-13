<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class SsoController extends Controller
{
    /**
     * Redirect to Keycloak SSO
     */
    public function redirect()
    {
        if (!config('services.keycloak.client_id')) {
            return redirect()->route('login')->with('error', 'SSO tidak dikonfigurasi.');
        }

        try {
            return Socialite::driver('keycloak')->redirect();
        } catch (\Exception $e) {
            Log::error('SSO redirect failed', ['error' => $e->getMessage()]);
            return redirect()->route('login')->with('error', 'SSO tidak tersedia. Gunakan email/password.');
        }
    }

    /**
     * Handle callback from Keycloak
     */
    public function callback()
    {
        try {
            // Get user data from Keycloak
            $keycloakUser = Socialite::driver('keycloak')->user();

            // Extract data (NIP from username field in Keycloak)
            $nip = $keycloakUser->getNickname(); // nickname = username field
            $email = $keycloakUser->getEmail();
            $name = $keycloakUser->getName();

            // Extract NIK and phone from Keycloak user data
            $rawUser = $keycloakUser->getRaw();
            $nik = $rawUser['nik'] ?? null;
            $phone = $rawUser['phone'] ?? $rawUser['mobile'] ?? $rawUser['phone_number'] ?? null;

            // Validate required fields
            if (empty($nip) || empty($email)) {
                Log::error('SSO Login Failed: Missing NIP or Email', [
                    'keycloak_user' => $keycloakUser->getRaw()
                ]);
                return redirect()->route('login')->with('error', 'Data SSO tidak lengkap. NIP atau email tidak ditemukan.');
            }

            $user = $this->resolveSsoUser($nip, $email, $name, $nik, $phone);

            if ($user->wasRecentlyCreated) {
                // Assign default role: User
                $defaultRole = Role::where('name', 'User')->first();
                if ($defaultRole) {
                    $user->roles()->attach($defaultRole->id);
                }
            }

            // Log the user in
            Auth::login($user, true); // true = remember me

            // Regenerate session for security
            request()->session()->regenerate();

            // Redirect based on role (same logic as AuthenticatedSessionController)
            if ($user->hasRole('Admin')) {
                return redirect('/admin');
            } elseif ($user->hasRole('Operator-Vidcon')) {
                return redirect('/op/tik/borrow');
            } else {
                return redirect('/dashboard');
            }

        } catch (\Exception $e) {
            Log::error('SSO Login Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('login')->with('error', 'Login SSO gagal. Silakan coba lagi atau gunakan email/password.');
        }
    }
    public function loginWithJwt(Request $request)
  {
      $token = $request->input('token')
          ?? $request->input('access_token')
          ?? $request->bearerToken();

      if (! $token) {
          return $this->fail($request, 'Token tidak ditemukan.', 400);
      }

      $publicKey = config('sso.public_key');
      if (! $publicKey) {
          return $this->fail($request, 'SSO public key belum dikonfigurasi.', 500);
      }

      $publicKey = str_replace("\\n", "\n", trim($publicKey));
      JWT::$leeway = (int) config('sso.leeway', 60);

      try {
          $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));
      } catch (\Throwable $e) {
          Log::warning('SSO token decode failed', ['error' => $e->getMessage()]);
          return $this->fail($request, 'Token tidak valid.', 401);
      }

      if (! $this->validateIssuer($decoded)) {
          return $this->fail($request, 'Issuer tidak sesuai.', 401);
      }

      if (! $this->validateAudience($decoded)) {
          return $this->fail($request, 'Audience tidak sesuai.', 401);
      }

      if (! $this->validateRealm($decoded->user->realm ?? null)) {
          return $this->fail($request, 'Realm tidak diizinkan.', 403);
      }

      $userClaim = $decoded->user ?? null;
      $nip = $userClaim->nip ?? $userClaim->username ?? null;
      $email = $userClaim->email ?? null;
      $name = $userClaim->full_name ?? $userClaim->username ?? $nip;
      $nik = $userClaim->nik ?? null;
      $phone = $userClaim->phone ?? $userClaim->mobile ?? $userClaim->phone_number ?? null;

      if (! $nip || ! $email) {
          return $this->fail($request, 'Data token tidak lengkap (nip/email).', 401);
      }

      $user = $this->resolveSsoUser($nip, $email, $name, $nik, $phone);

      // Mapping role dari token -> role lokal
      $roleCode = $decoded->role->code ?? null;
      $roleMap = [
          'admin' => 'Admin',
          'operator-vidcon' => 'Operator-Vidcon',
          'public' => 'User-Individual',
      ];

      if ($roleCode && isset($roleMap[$roleCode])) {
          $role = Role::where('name', $roleMap[$roleCode])->first();
          if ($role) {
              $user->roles()->syncWithoutDetaching([$role->id]);
          }
      }

      // If no role was assigned from token, assign default User role
      if ($user->roles()->count() === 0) {
          $defaultRole = Role::where('name', 'User')->first();
          if ($defaultRole) {
              $user->roles()->attach($defaultRole->id);
          }
      }

      Auth::login($user, true);
      $request->session()->regenerate();

      if ($user->hasRole('Admin')) {
          return redirect('/admin');
      } elseif ($user->hasRole('Operator-Vidcon')) {
          return redirect('/op/tik/borrow');
      }

      return redirect('/dashboard');
  }

  /**
   * Cari akun lokal untuk identitas SSO, atau buat baru bila belum ada.
   *
   * NIP adalah identitas utama ASN: bila NIP sudah dipakai sebuah akun, SSO
   * masuk ke akun itu walaupun email di SSO berbeda. Email akun yang sudah ada
   * tidak ditimpa — email adalah kredensial login lokal dan kolomnya unik.
   */
  protected function resolveSsoUser(string $nip, string $email, ?string $name, ?string $nik, ?string $phone): User
  {
      $nip = preg_replace('/\s+/', '', $nip);
      $nikHash = User::hashNik($nik);

      // Urutan prioritas: NIP, lalu NIK, lalu email. Bila NIP terlanjur ganda,
      // pilih akun lama (bukan buatan SSO) supaya riwayat layanan tetap menyatu.
      $user = User::where('nip', $nip)->orderBy('is_sso_user')->orderBy('id')->first();
      $matchedBy = 'nip';

      if (! $user && $nikHash) {
          $user = User::where('nik_hash', $nikHash)->orderBy('is_sso_user')->orderBy('id')->first();
          $matchedBy = 'nik';
      }

      if (! $user) {
          $user = User::where('email', $email)->first();
          $matchedBy = 'email';
      }

      if (! $user) {
          $user = User::create([
              'nip' => $nip,
              'nik' => $nik,
              'phone' => $phone ?? '-', // Use phone from SSO if available, otherwise default
              'email' => $email,
              'name' => $name,
              'password' => bcrypt(Str::random(32)), // Random password for SSO users
              'is_verified' => true, // Auto-verify SSO users
              'is_sso_user' => true, // Mark as SSO user
              'verified_at' => now(),
              'verified_by' => 'SSO System',
          ]);

          Log::info('New user created via SSO', ['nip' => $nip, 'email' => $email]);

          return $user;
      }

      // Aman menimpa NIP: pencarian NIP di atas memastikan belum ada akun lain yang memakainya.
      $updateData = ['nip' => $nip];

      // Nama akun yang sudah ada tidak ditimpa: nama di SSO bisa tidak lengkap,
      // sedangkan nama lokal sudah dipakai di dokumen layanan.
      if ($name && blank($user->name)) {
          $updateData['name'] = $name;
      }

      if ($nik) {
          $updateData['nik'] = $nik;
      }

      if ($phone) {
          $updateData['phone'] = $phone;
      }

      $user->update($updateData);

      Log::info('User logged in via SSO', [
          'nip' => $nip,
          'user_id' => $user->id,
          'matched_by' => $matchedBy,
          'email_differs' => strcasecmp($user->email, $email) !== 0,
      ]);

      return $user;
  }

  protected function validateIssuer(object $decoded): bool
  {
      $issuer = config('sso.issuer');
      if (! $issuer) {
          return true;
      }
      return isset($decoded->iss) && $decoded->iss === $issuer;
  }

  protected function validateAudience(object $decoded): bool
  {
      $audience = config('sso.audience');
      if (! $audience) {
          return true;
      }

      $audClaim = $decoded->aud ?? null;
      if (is_array($audClaim)) {
          return in_array($audience, $audClaim, true);
      }

      return $audClaim === $audience;
  }

  protected function validateRealm(?string $realm): bool
  {
      if (! $realm) {
          return false;
      }

      $allowed = array_filter(array_map('trim', explode(',', env('SSO_ALLOWED_REALMS', 'asn-kaltara'))));
      if (! $allowed) {
          return true;
      }

      return in_array($realm, $allowed, true);
  }

  protected function fail(Request $request, string $message, int $status = 400)
  {
      if ($request->expectsJson()) {
          return response()->json(['message' => $message], $status);
      }

      return redirect()->route('login')->with('error', $message);
  }
}
