<div class="alert alert-warning">
    <div class="d-flex align-items-center gap-4">
        <div class="fs-1">
            <i class="fas fa-exclamation-triangle"></i>
        </div>

        <div class="flex-grow-1 text-center">
            <div>
                {{ $slot }}
            </div>

            <div class="mt-2">
                <a href="{{ route('organizations.billing.get_select_plan', $organization) }}" class="btn btn-primary">
                    Upgrade Plan
                </a>
            </div>
        </div>
    </div>
</div>
