<?php
class Permission {
    public int $user_id;
    public int $can_view = 1;
    public int $can_create = 0;
    public int $can_edit   = 0;
    public int $can_delete = 0;
    public int $can_discharge = 0;
    public int $can_close_management = 0;
    public int $can_generate_pdf = 0;
    public ?string $updated_at = null;
}
