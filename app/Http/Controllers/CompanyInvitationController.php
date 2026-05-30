<?php

namespace App\Http\Controllers;

use App\Models\CompanyInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Phase A — invitation acceptance endpoint.
 *
 * Security boundary: the URL token is the only credential a recipient
 * possesses. We hash it before any DB lookup so a leak of the URL
 * never grants read access to the underlying row in another tenant.
 *
 * Email-match enforcement: an authenticated user can only accept an
 * invitation that was issued to their own login email. If a
 * mismatch happens, we redirect them back home with a flash so they
 * understand why nothing happened; we never silently swap accounts.
 *
 * Guest flow: an unauthenticated visitor lands on the same URL, sees
 * a friendly status page, and is routed to register/login with the
 * intended URL preserved by Laravel's session-driven redirect.
 */
class CompanyInvitationController extends Controller
{
    public function accept(Request $request, string $token)
    {
        $invitation = CompanyInvitation::query()
            ->with('company:id,name')
            ->where('token_hash', CompanyInvitation::hashToken($token))
            ->first();

        if (! $invitation) {
            return $this->showStatus('not_found', null, 404);
        }

        if ($invitation->isAccepted()) {
            return $this->showStatus('already_accepted', $invitation);
        }

        if ($invitation->isRevoked()) {
            return $this->showStatus('revoked', $invitation, 410);
        }

        if ($invitation->isExpired()) {
            return $this->showStatus('expired', $invitation, 410);
        }

        // Guest: park the token in the session so the next time they
        // hit /invitations/{token}/accept after auth we resolve again.
        if (! Auth::check()) {
            $request->session()->put('amr7.pending_invitation_token', $token);

            return redirect()->guest(route('register'));
        }

        $user = Auth::user();

        // Email-match guard. Compare case-insensitively. If we ever
        // change rules later (e.g. allow same-domain accept), this is
        // the single chokepoint to update.
        if (strcasecmp((string) $user->email, (string) $invitation->email) !== 0) {
            Log::info('company_invitation.email_mismatch', [
                'invitation_id' => $invitation->id,
                'user_id'       => $user->id,
            ]);

            return redirect()
                ->route('dashboard')
                ->withErrors(['invitation' => 'هذه الدعوة موجهة لبريد آخر. سجّل دخولاً بالحساب الصحيح.']);
        }

        try {
            DB::transaction(function () use ($user, $invitation) {
                $alreadyMember = $invitation->company
                    ->users()
                    ->where('user_id', $user->id)
                    ->exists();

                if (! $alreadyMember) {
                    $invitation->company->users()->attach($user->id, [
                        'role'      => in_array($invitation->role, ['admin', 'employee'], true)
                            ? $invitation->role
                            : 'employee',
                        'is_active' => false,
                    ]);
                }

                $invitation->update(['accepted_at' => now()]);
            });
        } catch (\Throwable $e) {
            report($e);
            return $this->showStatus('attach_failed', $invitation, 500);
        }

        // Surface the just-joined company so the dashboard greets the
        // user with the right context.
        $request->session()->put('active_company_id', (int) $invitation->company_id);

        return redirect()
            ->route('dashboard', ['section' => 'home'])
            ->with('status', 'تم قبول الدعوة بنجاح.');
    }

    /**
     * Renders a friendly status page for the recipient. Kept as a
     * single helper so all not-found / expired / revoked paths share
     * the same look and never leak invitation details to strangers.
     */
    private function showStatus(string $key, ?CompanyInvitation $invitation, int $httpStatus = 200)
    {
        $companyName = $invitation?->company?->name;
        $payload = [
            'status_key'   => $key,
            'company_name' => $companyName,
        ];

        // Reuse a generic errors view if one ships; otherwise fall back
        // to a minimal inline response so this controller never crashes
        // for missing views.
        if (view()->exists('errors.invitation-status')) {
            return response()->view('errors.invitation-status', $payload, $httpStatus);
        }

        $message = match ($key) {
            'not_found'         => 'رابط الدعوة غير صالح أو غير موجود.',
            'already_accepted'  => 'هذه الدعوة مقبولة مسبقًا.',
            'revoked'           => 'تم إلغاء هذه الدعوة من قِبَل المنشأة.',
            'expired'           => 'انتهت صلاحية هذه الدعوة.',
            'attach_failed'     => 'تعذر معالجة الدعوة. حاول مرة أخرى لاحقًا.',
            default             => 'حدث خطأ غير متوقع.',
        };

        return response()->json([
            'status'  => $key,
            'message' => $message,
            'company' => $companyName,
        ], $httpStatus);
    }
}
