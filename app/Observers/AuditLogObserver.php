<?php


namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogObserver
{
    public function created(Model $model): void
    {
        $this->log(
            model: $model,
            action: 'created',
            oldValues: null,
            newValues: $model->getAttributes()
        );
    }

    public function updated(Model $model): void
    {
        $this->log(
            model: $model,
            action: 'updated',
            oldValues: $model->getOriginal(),
            newValues: $model->getChanges()
        );
    }

    public function deleted(Model $model): void
    {
        $this->log(
            model: $model,
            action: 'deleted',
            oldValues: $model->getAttributes(),
            newValues: null
        );
    }

    private function log(
        Model  $model,
        string $action,
        ?array $oldValues,
        ?array $newValues
    ): void
    {
        /*
         * لا نسجل AuditLog نفسه
         * حتى لا ندخل في حلقة لا نهائية.
         */
        if ($model instanceof AuditLog) {
            return;
        }

        /*
         * إذا لم يوجد مستخدم مسجل دخول،
         * لا نستطيع ربط العملية بـ user_id.
         */
        if (!Auth::check()) {
            return;
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'record_id' => $model->getKey(),
            'table_name' => $model->getTable(),
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
