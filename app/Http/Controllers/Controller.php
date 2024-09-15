<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(title: 'Cashbee Coding Exam Api Documentation', version: '0.1')]
#[OA\Tag(name: 'Auth', description: 'Operations related to authentication')]
#[OA\Tag(name: 'Posts', description: 'Operations related to posts')]
#[OA\Tag(name: 'Users', description: 'Operations related to users')]
abstract class Controller
{
    //
}
