<?php

/**
 * Cron Job: Send In-Progress Ticket Reminders
 * ─────────────────────────────────────────────────────────────────────────────
 * Sends email reminders to assigned PIC for tickets that have been In Progress
 * for 7+ days, then every 2 days thereafter until resolved or closed.
 *
 * Schedule (Linux crontab):
 *   0 8 * * * php /path/to/aduan/cron/send-inprogress-reminders.php >> /path/to/aduan/storage/logs/inprogress-reminders.log 2>&1
 *
 * Schedule (Windows Task Scheduler):
 *   Program : C:\xampp\php\php.exe
 *   Arguments: C:\xampp\htdocs\aduan\cron\send-inprogress-reminders.php
 */

define('LARAVEL_START', microtime(true));

// ── Bootstrap Laravel ────────────────────────────────────────────────────────
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// ── Dependencies ─────────────────────────────────────────────────────────────
use App\Mail\TicketInProgressReminder;
use App\Models\Ticket;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// ── Config ───────────────────────────────────────────────────────────────────
$startDay   = 7;  // first reminder on day 7 of In Progress
$repeatEvery = 2;  // then every 2 days

// ── Logging helper ───────────────────────────────────────────────────────────
$log = function (string $message) {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    echo $line . PHP_EOL;
    Log::channel('single')->info('[InProgressReminder] ' . $message);
};

$log('Starting in-progress ticket reminder job.');

// ── Query: tickets that need a reminder ──────────────────────────────────────
$tickets = Ticket::with('assignees')
    ->where('status', 'in_progress')
    ->whereNotNull('assigned_at')
    ->where('assigned_at', '<=', now()->subDays($startDay))   // 7+ days in progress
    ->whereHas('assignees')
    ->where(function ($query) use ($repeatEvery) {
        $query->whereNull('inprogress_notified_at')            // never notified yet
              ->orWhere('inprogress_notified_at', '<=', now()->subDays($repeatEvery)); // or 2+ days since last
    })
    ->get();

if ($tickets->isEmpty()) {
    $log('No tickets require a reminder. Exiting.');
    exit(0);
}

$log("Found {$tickets->count()} ticket(s) requiring a reminder.");

$sent = 0;

foreach ($tickets as $ticket) {
    $daysInProgress = (int) $ticket->assigned_at->diffInDays(now());

    foreach ($ticket->assignees as $assignee) {
        if (empty($assignee->email)) {
            $log("  ⚠ Skipped {$assignee->name} – no email address.");
            continue;
        }

        try {
            Mail::to($assignee->email)->send(new TicketInProgressReminder($ticket, $assignee));
            $log("  ✓ Sent to {$assignee->name} <{$assignee->email}> for [{$ticket->ticket_number}] ({$daysInProgress} days in progress).");
            $sent++;
        } catch (\Exception $e) {
            $log("  ✗ Failed to send to {$assignee->name} <{$assignee->email}>: " . $e->getMessage());
        }
    }

    // Update timestamp so next reminder sends 2 days later
    $ticket->update(['inprogress_notified_at' => now()]);
}

$log("Done. {$sent} reminder email(s) sent.");
exit(0);
