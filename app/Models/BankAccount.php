<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_name',
        'account_name',
        'account_number',
        'iban',
        'is_active',
        'logo',
    ];

    /**
     * تحويل البيانات تلقائياً
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes (نطاقات الاستعلام)
    |--------------------------------------------------------------------------
    */

    /**
     * جلب الحسابات النشطة فقط
     * الاستخدام: BankAccount::active()->get();
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators (المعالجات)
    |--------------------------------------------------------------------------
    */

    /**
     * جلب رابط اللوجو الكامل
     * الاستخدام: $bank->logo_url
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        // لوجو افتراضي للبنك إذا لم توجد صورة
        return asset('images/default-bank.png');
    }

    /**
     * عرض الآيبان بشكل مقروء (مقسم كل 4 أرقام)
     * الاستخدام: $bank->formatted_iban
     */
    public function getFormattedIbanAttribute(): string
    {
        $iban = $this->iban;
        if (! $iban) return '';

        $cleanIban = str_replace(' ', '', $iban);

        return trim(chunk_split($cleanIban, 4, ' '));
    }

    // ── Encrypted fields ────────────────────────────────────────────────────
    // iban + account_number are encrypted at rest. Decryption is lazy and
    // falls back to the raw value when the column still holds legacy plaintext,
    // so the model coexists with un-migrated rows until the
    // bank-accounts:encrypt-existing command finishes the backfill.

    public function setIbanAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['iban'] = null;
            return;
        }

        $clean = strtoupper(str_replace(' ', '', $value));
        $this->attributes['iban'] = Crypt::encryptString($clean);
    }

    public function getIbanAttribute(?string $value): ?string
    {
        return $this->decryptOrPassthrough($value);
    }

    public function setAccountNumberAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['account_number'] = null;
            return;
        }

        $this->attributes['account_number'] = Crypt::encryptString($value);
    }

    public function getAccountNumberAttribute(?string $value): ?string
    {
        return $this->decryptOrPassthrough($value);
    }

    protected function decryptOrPassthrough(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            // Legacy plaintext row (pre-encryption). The Artisan
            // bank-accounts:encrypt-existing command rewrites these in place.
            return $value;
        }
    }
}