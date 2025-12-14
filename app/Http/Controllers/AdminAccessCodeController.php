<?php

namespace App\Http\Controllers;

use App\Helpers\SettingHelper;
use App\Http\Middleware\AdminAccessCodeMiddleware;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class AdminAccessCodeController extends Controller
{
    public function show(): Response
    {
        return response()->view('admin.access-code');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'access_code' => ['required', 'string'],
        ]);

        $expectedCode = SettingHelper::get('admin_access_code', config('app.admin_access_code'));

        if (! $expectedCode) {
            return Redirect::back()->withErrors([
                'access_code' => __('Kode akses belum dikonfigurasi.'),
            ]);
        }

        if (! $this->codeMatches($validated['access_code'], $expectedCode)) {
            return Redirect::back()
                ->withErrors(['access_code' => __('Kode akses tidak valid.')])
                ->withInput();
        }

        $request->session()->put(AdminAccessCodeMiddleware::SESSION_KEY, now()->timestamp);

        return redirect()->intended(url('/admin'));
    }

    protected function codeMatches(string $input, string $expected): bool
    {
        $info = password_get_info($expected);

        if (($info['algo'] ?? 0) !== 0) {
            return password_verify($input, $expected);
        }

        return hash_equals((string) $expected, (string) $input);
    }
}
