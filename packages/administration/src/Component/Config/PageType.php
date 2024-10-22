<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

enum PageType: string
{
    case LIST = 'list';
    case DETAIL = 'detail';
    case CREATE = 'create';
    case EDIT = 'edit';
    case DELETE = 'delete';
}
