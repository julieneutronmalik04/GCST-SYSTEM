<?php
// Admin deployment setting: 'Rejected' or 'Waiting List'.
return [
    'conflicting_pending_status' => getenv('GCST_RESERVATION_CONFLICT_POLICY') ?: 'Rejected',
];
