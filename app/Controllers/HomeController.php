<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;

final class HomeController extends Controller
{
  public function index(Request $request): void
  {
    $this->redirect('/ordenes');
  }
}
