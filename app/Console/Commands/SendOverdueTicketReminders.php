<?php

namespace App\Console\Commands;

use App\Mail\TicketOverdueReminder;
use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendOverdueTicketReminders extends Command
{
    protected $signature = 'tickets:send-overdue-reminders
                            {--days=14 : Number of days before a ticket is considered overdue}
                            {--repeat=7 : Re-notify assignees every N days after first reminder}
                            {--dry-run : Show which tickets would be notified without sending emails}';

    protected $description = 'Send email reminders to assigned PIC for complaints unresolved after 14 days';

    public function handle(): int
    {
        $days       = (int) $this->option('days');
        $repeatDays = (int) $this->option('repeat');
        $dryRun     = $this->option('dry-run');
        $cutoff     = now()->subDays($days);

        $tickets = Ticket::with('assignees')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->where('created_at', '<=', $cutoff)
            ->whereHas('assignees')
            ->where(function ($query) use ($repeatDays) {
                $query->whereNull('overdue_notified_at')
                      ->orWhere('overdue_notified_at', '<=', now()->subDays($repeatDays));
            })
            ->get();

        if ($tickets->isEmpty()) {
            $this->info('No overdue tickets found. Nothing to send.');
            return self::SUCCESS;
        }

        $this->info("Found {$tickets->count()} overdue ticket(s)." . ($dryRun ? ' [DRY RUN]' : ''));

        $sent = 0;

        foreach ($tickets as $ticket) {
            $daysPending = $ticket->created_at->diffInDays(now());

            foreach ($ticket->assignees as $assignee) {
                if (!$assignee->email) {
                    continue;
                }

                if ($dryRun) {
                    $this->line("  [DRY RUN] Would notify {$assignee->name} <{$assignee->email}> for ticket {$ticket->ticket_number} ({$daysPending} days old)");
                    continue;
                }

                Mail::to($assignee->email)->send(new TicketOverdueReminder($ticket, $assignee));

                $this->line("  ✓ Sent reminder to {$assignee->name} <{$assignee->email}> for [{$ticket->ticket_number}] ({$daysPending} days old)");
                $sent++;
            }

            if (!$dryRun) {
                $ticket->update(['overdue_notified_at' => now()]);
            }
        }

        if (!$dryRun) {
            $this->newLine();
            $this->info("Done. {$sent} reminder email(s) sent.");
        }

        return self::SUCCESS;
    }
}
