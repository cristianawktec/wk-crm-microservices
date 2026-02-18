<?php

namespace App\Console\Commands;

use App\Mail\LoginAuditMail;
use App\Models\LoginAudit;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class TestLoginAuditEmail extends Command
{
    protected $signature = 'email:test-login-audit {--email=admin@consultoriawk.com}';

    protected $description = 'Test login audit email sending';

    public function handle()
    {
        $this->info('╔════════════════════════════════════════════╗');
        $this->info('║  Testing Login Audit Email Configuration  ║');
        $this->info('╚════════════════════════════════════════════╝');
        
        // 1. Check mail configuration
        $this->newLine();
        $this->info('📧 Mail Configuration:');
        $this->line('   MAIL_MAILER: ' . config('mail.default'));
        $this->line('   MAIL_HOST: ' . config('mail.mailers.smtp.host'));
        $this->line('   MAIL_PORT: ' . config('mail.mailers.smtp.port'));
        $this->line('   MAIL_USERNAME: ' . config('mail.mailers.smtp.username'));
        $this->line('   MAIL_ENCRYPTION: ' . config('mail.mailers.smtp.encryption'));
        $this->line('   MAIL_FROM_ADDRESS: ' . config('mail.from.address'));
        $this->line('   MAIL_AUDIT_RECIPIENT: ' . config('mail.audit_recipient'));
        
        // 2. Verify recipients
        $testEmail = $this->option('email');
        $auditRecipient = config('mail.audit_recipient', 'admin@consultoriawk.com');
        
        $this->newLine();
        $this->info('📮 Recipients:');
        $this->line('   Test Email: ' . $testEmail);
        $this->line('   Audit Recipient (config): ' . $auditRecipient);
        
        // 3. Find a login audit record
        $this->newLine();
        $this->info('🔍 Looking for login audit record...');
        $auditRecord = LoginAudit::latest()->first();
        
        if (!$auditRecord) {
            $this->error('❌ No login audit records found. Create one by logging in first.');
            return 1;
        }
        
        $auditRecord->loadMissing('user:id,name,email');
        $this->line('   Found: ' . $auditRecord->user->email . ' (' . $auditRecord->user->name . ')');
        $this->line('   Logged in at: ' . $auditRecord->logged_in_at);
        
        // 4. Prepare and send email
        $this->newLine();
        $this->info('📤 Attempting to send email...');
        
        try {
            $mailable = new LoginAuditMail(
                collect([$auditRecord]),
                $auditRecipient,
                $auditRecord->user->email
            );
            
            Mail::to($testEmail)->send($mailable);
            
            $this->newLine();
            $this->info('✅ Email sent successfully!');
            $this->line('   To: ' . $testEmail);
            $this->line('   Subject: Login Audit Report');
            return 0;
            
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Email sending failed!');
            $this->line('   Error: ' . $e->getMessage());
            $this->line('   Class: ' . get_class($e));
            
            if (method_exists($e, 'getDebugMessage')) {
                $this->line('   Debug: ' . $e->getDebugMessage());
            }
            
            return 1;
        }
    }
}
