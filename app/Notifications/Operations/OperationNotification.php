<?php

namespace App\Notifications\Operations;

use App\Enums\NotificationType;
use App\Notifications\Channels\FcmChannel;
use App\Notifications\Channels\InAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Base class for every operational (business) notification.
 *
 * A subclass only has to describe the message — title, body, type and the
 * record it refers to — and this base fans it out across all channels:
 *   - mail    : an email built from the title/body.
 *   - in_app  : a row in the App\Models\Notification table (read by the app).
 *   - fcm     : a Firebase push (kept COMMENTED OUT for now, see channels()).
 */
abstract class OperationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** The notification title / email subject. */
    abstract protected function title(object $notifiable): string;

    /** The notification body / email line. */
    abstract protected function body(object $notifiable): string;

    /** Severity used for the in-app record. Override to change. */
    protected function type(): NotificationType
    {
        return NotificationType::INFO;
    }

    /** The kind of record this notification points at (e.g. 'order'). */
    protected function referenceType(): ?string
    {
        return null;
    }

    /** The id of the record this notification points at. */
    protected function referenceId(): ?int
    {
        return null;
    }

    /**
     * Extra key/value data delivered with the FCM push (used by the app to
     * deep-link, etc.). Override to add fields.
     *
     * @return array<string, mixed>
     */
    protected function data(object $notifiable): array
    {
        return array_filter([
            'type' => $this->type()->value,
            'reference_type' => $this->referenceType(),
            'reference_id' => $this->referenceId(),
        ], fn ($v) => $v !== null);
    }

    /**
     * Friendly channel labels this notification is delivered on. Firebase is
     * intentionally left commented out until the frontend wires it up — just
     * uncomment the 'fcm' line (and register a device token) to enable push.
     *
     * @return array<int, string>
     */
    public function sentVia(): array
    {
        return [
            'mail',
            'in_app',
            // 'fcm', // TODO: uncomment when Firebase Cloud Messaging is wired up on the frontend.
        ];
    }

    /**
     * Maps the friendly labels above to the concrete channel implementations.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return array_map(fn (string $channel): string => match ($channel) {
            'in_app' => InAppChannel::class,
            'fcm' => FcmChannel::class,
            default => $channel,
        }, $this->sentVia());
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title($notifiable))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line($this->body($notifiable));
    }

    /**
     * Payload persisted by the InAppChannel.
     *
     * @return array<string, mixed>
     */
    public function toInApp(object $notifiable): array
    {
        return [
            'title' => $this->title($notifiable),
            'body' => $this->body($notifiable),
            'type' => $this->type()->value,
            'reference_type' => $this->referenceType(),
            'reference_id' => $this->referenceId(),
        ];
    }

    /**
     * Payload delivered by the FcmChannel.
     *
     * @return array<string, mixed>
     */
    public function toFcm(object $notifiable): array
    {
        return [
            'title' => $this->title($notifiable),
            'body' => $this->body($notifiable),
            'data' => $this->data($notifiable),
        ];
    }
}