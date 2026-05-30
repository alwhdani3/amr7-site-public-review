<?php

namespace App\Console\Commands;

use App\Models\BankAccount;
use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotent backfill: encrypts any row whose iban or account_number is still
 * stored as plaintext. A value is considered already encrypted if
 * Crypt::decryptString() returns without throwing — those rows are skipped.
 *
 * Safe to re-run. Designed to be invoked once after the widen-columns
 * migration is deployed. Use --dry-run to preview without writing.
 */
class EncryptExistingBankAccounts extends Command
{
    protected $signature = 'bank-accounts:encrypt-existing
                            {--dry-run : Show what would change without writing}';

    protected $description = 'Encrypts plaintext iban and account_number columns on existing bank_accounts rows. Idempotent.';

    public function handle(): int
    {
        if (! Schema::hasTable('bank_accounts')) {
            $this->warn('bank_accounts table is missing — nothing to do.');
            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');

        $touched = 0;
        $alreadyEncrypted = 0;
        $skipped = 0;

        // Pull raw values without invoking the model casts/mutators so we can
        // see the database state as-is.
        DB::table('bank_accounts')->orderBy('id')->chunkById(200, function ($rows) use (&$touched, &$alreadyEncrypted, &$skipped, $dryRun) {
            foreach ($rows as $row) {
                $update = [];

                foreach (['iban', 'account_number'] as $column) {
                    $raw = $row->{$column} ?? null;
                    if ($raw === null || $raw === '') {
                        continue;
                    }

                    if ($this->isAlreadyEncrypted($raw)) {
                        $alreadyEncrypted++;
                        continue;
                    }

                    $clean = $column === 'iban'
                        ? strtoupper(str_replace(' ', '', $raw))
                        : $raw;

                    $update[$column] = Crypt::encryptString($clean);
                    $touched++;
                }

                if (empty($update)) {
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->line(sprintf('  [dry-run] would encrypt #%d (%s)', $row->id, implode(', ', array_keys($update))));
                    continue;
                }

                DB::table('bank_accounts')->where('id', $row->id)->update($update);
            }
        });

        $this->info(sprintf(
            '%s touched=%d, already_encrypted=%d, skipped_empty=%d',
            $dryRun ? '[dry-run]' : 'done.',
            $touched,
            $alreadyEncrypted,
            $skipped
        ));

        return self::SUCCESS;
    }

    protected function isAlreadyEncrypted(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        try {
            Crypt::decryptString($value);
            return true;
        } catch (DecryptException) {
            return false;
        }
    }
}
