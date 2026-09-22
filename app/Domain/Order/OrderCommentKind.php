<?php

namespace App\Domain\Order;

enum OrderCommentKind: string
{
    case Regular = 'regular';
    case ApprovalRequest = 'approval_request';
    case ApprovalResult = 'approval_result';
}
