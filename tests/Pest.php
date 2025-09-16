<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(TestCase::class)->in('Feature', 'Unit');
uses(DatabaseTransactions::class)->in('Feature', 'Unit');

