<?php
class Permission {
    public int $user_id;
    public int $can_create = 0;
    public int $can_edit   = 0;
    public int $can_delete = 0;
    public ?string $updated_at = null;
}